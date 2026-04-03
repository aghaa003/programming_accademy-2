<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    /** GET /api/admin/users */
    public function index(Request $request)
    {
        // M4: Paginate admin user list; L5: phone excluded (available via show/{id})
        $limit  = min((int) $request->query('limit', 20), 100);
        $offset = max((int) $request->query('offset', 0), 0);

        $query = User::with('roles')
            ->select('id', 'firstName', 'lastName', 'username', 'email', 'country', 'experience', 'joinDate');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            // Escape SQL LIKE wildcards to prevent wildcard injection (N9 pattern)
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $search);
            $like = "%{$escaped}%";
            $query->where(function ($q) use ($like) {
                $q->where('firstName', 'LIKE', $like)
                  ->orWhere('lastName', 'LIKE', $like)
                  ->orWhere('username', 'LIKE', $like)
                  ->orWhere('email', 'LIKE', $like);
            });
        }

        $total = $query->count();

        $users = $query->orderBy('joinDate', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($u) {
                $u->roles = $u->roles->pluck('name');

                return $u;
            });

        return response()->json(['success' => true, 'users' => $users, 'total' => $total]);
    }

    /** GET /api/admin/users/{id} */
    public function show($id)
    {
        $user = User::with('roles')->find($id);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود'], 404);
        }

        // Pluck role names (same as index()), so frontend gets ["student"] / ["admin"]
        $user->roles = $user->roles->pluck('name');

        // Enrolled courses with progress
        $courses = DB::table('user_course_progress as ucp')
            ->join('courses as c', 'c.id', '=', 'ucp.course_id')
            ->where('ucp.user_id', $id)
            ->select('c.id', 'c.title', 'ucp.percentage_completed', 'ucp.last_accessed')
            ->orderByDesc('ucp.last_accessed')
            ->get();

        // Challenges attempted + latest submitted code (one row per challenge, no duplicates)
        $latestAttempt = DB::table('challenge_attempts')
            ->selectRaw('user_id, challenge_id, MAX(id) as max_id')
            ->groupBy('user_id', 'challenge_id');

        $challenges = DB::table('user_challenges as uc')
            ->join('challenges as ch', 'ch.id', '=', 'uc.challenge_id')
            ->leftJoinSub($latestAttempt, 'lat', function ($join) {
                $join->on('lat.user_id', '=', 'uc.user_id')
                     ->on('lat.challenge_id', '=', 'uc.challenge_id');
            })
            ->leftJoin('challenge_attempts as ca', 'ca.id', '=', 'lat.max_id')
            ->where('uc.user_id', $id)
            ->select('ch.id', 'ch.title', 'uc.completed', 'uc.best_score', 'uc.attempts', 'uc.last_attempted', 'ca.code as submitted_code')
            ->orderByDesc('uc.last_attempted')
            ->get();

        // Assignments submitted
        $assignments = DB::table('user_assignments as ua')
            ->join('assignments as a', 'a.id', '=', 'ua.assignment_id')
            ->where('ua.user_id', $id)
            ->select('ua.id as submission_id', 'a.id', DB::raw('SUBSTR(a.question, 1, 60) as question'), 'ua.solution', 'ua.score', 'ua.status', 'ua.is_completed', 'ua.submitted_at')
            ->orderByDesc('ua.submitted_at')
            ->get();

        return response()->json([
            'success'     => true,
            'user'        => $user,
            'courses'     => $courses,
            'challenges'  => $challenges,
            'assignments' => $assignments,
        ]);
    }

    /** POST /api/admin/users/{id}/toggle-admin */
    public function toggleAdmin($id)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود'], 404);
        }

        // M2: admin state is determined by role membership (role_id=2), not a DB column
        $isCurrentlyAdmin = DB::table('user_roles')->where('user_id', $id)->where('role_id', 2)->exists();
        $newAdminState = ! $isCurrentlyAdmin;

        // Prevent the last admin from being demoted (would lock out all admin access)
        if (! $newAdminState) {
            $adminCount = DB::table('user_roles')->where('role_id', 2)->count();
            if ($adminCount <= 1) {
                return response()->json(['success' => false, 'message' => 'لا يمكن إزالة صلاحيات آخر مدير في النظام.'], 400);
            }
        }

        DB::transaction(function () use ($id, $newAdminState) {
            if ($newAdminState) {
                DB::table('user_roles')->insertOrIgnore(['user_id' => $id, 'role_id' => 2]);
            } else {
                DB::table('user_roles')->where('user_id', $id)->where('role_id', 2)->delete();
            }
        });

        AuditLogger::log(request(), 'toggle_admin', 'User', (int) $id, ['new_state' => $newAdminState]);

        return response()->json(['success' => true, 'is_admin' => $newAdminState]);
    }

    /** DELETE /api/admin/users/{id} */
    public function destroy($id, \Illuminate\Http\Request $request)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود'], 404);
        }

        // Prevent admin from deleting their own account via the admin panel
        $currentUserId = auth()->id();
        if ((int) $id === (int) $currentUserId) {
            return response()->json(['success' => false, 'message' => 'لا يمكنك حذف حسابك الخاص من لوحة الإدارة. استخدم صفحة الملف الشخصي.'], 400);
        }

        // Collect avatar path before transaction; delete file only AFTER transaction succeeds
        $avatarFile = null;
        if (! empty($user->avatar_path)) {
            $avatarFile = public_path(ltrim($user->avatar_path, '/'));
        }

        // Delete all user data atomically so a partial failure leaves a consistent state
        DB::transaction(function () use ($id, $user) {
            DB::table('user_assignments')->where('user_id', $id)->delete();
            DB::table('user_challenges')->where('user_id', $id)->delete();
            DB::table('challenge_attempts')->where('user_id', $id)->delete();
            DB::table('user_course_progress')->where('user_id', $id)->delete();
            DB::table('user_lesson_progress')->where('user_id', $id)->delete();
            DB::table('platform_bookmarks')->where('user_id', $id)->delete();
            DB::table('platform_ratings')->where('user_id', $id)->delete();
            DB::table('academy_reviews')->where('user_id', $id)->delete();
            DB::table('password_resets')->where('email', $user->email)->delete();
            DB::table('user_roles')->where('user_id', $id)->delete();
            DB::table('user_preferences')->where('user_id', $id)->delete();
            $user->delete();
        });

        // Delete avatar file only after DB transaction succeeded
        if ($avatarFile && is_file($avatarFile)) {
            @unlink($avatarFile);
        }

        AuditLogger::log($request, 'delete_user', 'User', (int) $id, ['username' => $user->username, 'email' => $user->email]);

        return response()->json(['success' => true, 'message' => 'تم حذف المستخدم بنجاح']);
    }
}

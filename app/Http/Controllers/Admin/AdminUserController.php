<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminUserController extends Controller
{
    /** GET /api/admin/users */
    public function index()
    {
        $users = User::with('roles')
            ->select('id', 'firstName', 'lastName', 'username', 'email', 'phone', 'country', 'experience', 'joinDate', 'is_admin')
            ->orderBy('joinDate', 'desc')
            ->limit(500)
            ->get()
            ->map(function ($u) {
                $u->roles = $u->roles->pluck('name');

                return $u;
            });

        return response()->json(['success' => true, 'users' => $users]);
    }

    /** GET /api/admin/users/{id} */
    public function show($id)
    {
        $user = User::with('roles')->find($id);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود'], 404);
        }

        return response()->json(['success' => true, 'user' => $user]);
    }

    /** POST /api/admin/users/{id}/toggle-admin */
    public function toggleAdmin($id)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود'], 404);
        }

        $newAdminState = ! $user->is_admin;

        // Prevent the last admin from demoting themselves (would lock out all admin access)
        if (! $newAdminState) {
            $adminCount = User::where('is_admin', true)->count();
            if ($adminCount <= 1) {
                return response()->json(['success' => false, 'message' => 'لا يمكن إزالة صلاحيات آخر مدير في النظام.'], 400);
            }
        }

        // Wrap both writes in a transaction so users.is_admin and user_roles never go out of sync
        DB::transaction(function () use ($user, $id, $newAdminState) {
            $user->is_admin = $newAdminState;
            $user->save();

            if ($newAdminState) {
                DB::table('user_roles')->insertOrIgnore(['user_id' => $id, 'role_id' => 2]);
            } else {
                DB::table('user_roles')->where('user_id', $id)->where('role_id', 2)->delete();
            }
        });

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
        $currentUserId = $request->session()->get('user_id');
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

        return response()->json(['success' => true, 'message' => 'تم حذف المستخدم بنجاح']);
    }
}

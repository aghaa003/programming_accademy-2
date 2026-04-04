<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminChallengeRequest;
use App\Models\Challenge;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminChallengeController extends Controller
{
    public function index(Request $request)
    {
        // M4: Paginate admin challenge list
        $limit  = min((int) $request->query('limit', 20), 100);
        $offset = max((int) $request->query('offset', 0), 0);
        $total  = Challenge::count();

        $challenges = Challenge::orderBy('id', 'desc')->skip($offset)->take($limit)->get();

        return response()->json(['success' => true, 'challenges' => $challenges, 'total' => $total]);
    }

    public function store(AdminChallengeRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        $challenge = Challenge::create($data);

        AuditLogger::log($request, 'create_challenge', 'Challenge', $challenge->id, ['title' => $challenge->title]);

        return response()->json(['success' => true, 'challenge' => $challenge], 201);
    }

    public function show($id)
    {
        $challenge = Challenge::find($id);
        if (! $challenge) {
            return response()->json(['success' => false, 'message' => 'التحدي غير موجود'], 404);
        }

        return response()->json(['success' => true, 'challenge' => $challenge]);
    }

    public function update(AdminChallengeRequest $request, $id)
    {
        $challenge = Challenge::find($id);
        if (! $challenge) {
            return response()->json(['success' => false, 'message' => 'التحدي غير موجود'], 404);
        }

        $challenge->fill($request->validated());
        $challenge->save();

        AuditLogger::log($request, 'update_challenge', 'Challenge', $challenge->id, ['title' => $challenge->title]);

        return response()->json(['success' => true, 'challenge' => $challenge]);
    }

    public function destroy($id)
    {
        $challenge = Challenge::find($id);
        if (! $challenge) {
            return response()->json(['success' => false, 'message' => 'التحدي غير موجود'], 404);
        }
        // Delete related rows atomically to avoid partial deletion on failure
        DB::transaction(function () use ($id, $challenge) {
            DB::table('challenge_attempts')->where('challenge_id', $id)->delete();
            DB::table('user_challenges')->where('challenge_id', $id)->delete();
            $challenge->delete();
        });

        AuditLogger::log(request(), 'delete_challenge', 'Challenge', (int) $id, ['title' => $challenge->title]);

        return response()->json(['success' => true, 'message' => 'تم حذف التحدي بنجاح']);
    }

    /** POST /api/admin/challenges/{challengeId}/grade-user/{userId} */
    public function gradeUser(Request $request, $challengeId, $userId)
    {
        $score = (int) $request->input('score', 0);
        if ($score < 0 || $score > 100) {
            return response()->json(['success' => false, 'message' => 'الدرجة يجب أن تكون بين 0 و 100.'], 422);
        }

        if (! DB::table('challenges')->where('id', $challengeId)->exists()) {
            return response()->json(['success' => false, 'message' => 'التحدي غير موجود.'], 404);
        }
        if (! DB::table('users')->where('id', $userId)->whereNull('deleted_at')->exists()) {
            return response()->json(['success' => false, 'message' => 'المستخدم غير موجود.'], 404);
        }

        $completed = $score >= 70 ? 1 : 0;

        DB::transaction(function () use ($userId, $challengeId, $score, $completed) {
            // Sync the summary row (create if this is the first manual grade ever).
            // NOTE: attempts is intentionally NOT incremented — an admin grade is not
            // a user attempt. attempts only increments when the user submits code.
            DB::table('user_challenges')->updateOrInsert(
                ['user_id' => $userId, 'challenge_id' => $challengeId],
                [
                    'best_score'     => $score,
                    'completed'      => $completed,
                    'last_attempted' => now(),
                ]
            );

            // Keep challenge_attempts in sync with the admin decision.
            // Only update an existing row — don't create a phantom attempt if
            // the user never actually submitted code.
            DB::table('challenge_attempts')
                ->where('user_id', $userId)
                ->where('challenge_id', $challengeId)
                ->update([
                    'completed'    => $completed,
                    'completed_at' => $completed ? now() : null,
                ]);
        });

        AuditLogger::log($request, 'grade_challenge', 'Challenge', (int) $challengeId, [
            'user_id' => (int) $userId,
            'score'   => $score,
        ]);

        return response()->json(['success' => true, 'message' => 'تم تصحيح التحدي بنجاح']);
    }
}

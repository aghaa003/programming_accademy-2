<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChallengeController extends Controller
{
    /** GET /api/challenges */
    public function index(Request $request)
    {
        $userId = auth()->id();

        $userCompletionSubquery = DB::table('user_challenges')
            ->select('challenge_id', DB::raw('MAX(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as user_completed'))
            ->where('user_id', $userId)
            ->groupBy('challenge_id');

        $challenges = DB::table('challenges as c')
            ->select(
                'c.*',
                DB::raw('COALESCE(SUM(uc.attempts), 0) as total_attempts'),
                DB::raw('COALESCE(COUNT(CASE WHEN uc.completed = 1 THEN 1 END), 0) as total_completions'),
                DB::raw('COALESCE(ucu.user_completed, 0) as user_completed')
            )
            ->leftJoin('user_challenges as uc', 'c.id', '=', 'uc.challenge_id')
            ->leftJoinSub($userCompletionSubquery, 'ucu', function ($join) {
                $join->on('c.id', '=', 'ucu.challenge_id');
            })
            ->where('c.is_active', 1)
            ->groupBy('c.id', 'ucu.user_completed')
            ->orderBy('c.created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'challenges' => $challenges]);
    }

    /** GET /api/challenge-stats */
    public function stats(Request $request)
    {
        $totalChallenges = DB::table('challenges')->where('is_active', 1)->count();
        $activeUsers = DB::table('user_challenges')->distinct()->count('user_id');
        $totalCompletions = DB::table('user_challenges')->where('completed', 1)->count();
        $totalAttempts = (int) DB::table('user_challenges')->sum('attempts');

        if ($totalAttempts > 0) {
            $avgRating = round(min(($totalCompletions / $totalAttempts) * 5, 5.0), 1);
        } else {
            $avgRating = 0.0;
        }

        return response()->json([
            'success' => true,
            'stats' => [
                'total_challenges' => $totalChallenges,
                'active_users' => $activeUsers ?: DB::table('users')->count(),
                'total_completions' => $totalCompletions,
                'average_rating' => $avgRating,
            ],
        ]);
    }

    /** GET /api/user-challenge-progress */
    public function userProgress(Request $request)
    {
        $userId = auth()->id();
        if (! $userId) {
            return response()->json(['success' => false, 'showLogin' => true]);
        }

        $attemptedChallenges = DB::table('user_challenges')->where('user_id', $userId)->count();
        $completedCount = DB::table('user_challenges')->where('user_id', $userId)->where('completed', 1)->count();
        $totalPoints = DB::table('user_challenges')->where('user_id', $userId)->sum('best_score');
        $successRate = $attemptedChallenges > 0 ? round(($completedCount * 100.0) / $attemptedChallenges, 1) : 0;

        // Per-category breakdown
        $categories = DB::table('challenges as c')
            ->leftJoin('user_challenges as uc', function ($j) use ($userId) {
                $j->on('c.id', '=', 'uc.challenge_id')->where('uc.user_id', '=', $userId);
            })
            ->select(
                'c.category',
                DB::raw('COUNT(DISTINCT c.id) as total_in_category'),
                DB::raw('COUNT(CASE WHEN uc.completed = 1 THEN 1 END) as completed_in_category')
            )
            ->where('c.is_active', 1)
            ->groupBy('c.category')
            ->orderBy('c.category')
            ->get()
            ->map(function ($row) {
                $map = [
                    'algorithms' => 'الخوارزميات',
                    'data-structures' => 'هياكل البيانات',
                    'web' => 'تطوير الويب',
                    'database' => 'قواعد البيانات',
                ];
                $pct = $row->total_in_category > 0
                    ? round(($row->completed_in_category / $row->total_in_category) * 100, 1)
                    : 0;

                return [
                    'category' => $row->category,
                    'category_name' => $map[$row->category] ?? $row->category,
                    'total_in_category' => (int) $row->total_in_category,
                    'completed_in_category' => (int) $row->completed_in_category,
                    'progress_percentage' => $pct,
                ];
            });

        return response()->json([
            'success' => true,
            'stats' => [
                'completed_challenges' => $completedCount,
                'total_points' => (int) $totalPoints,
                'attempted_challenges' => $attemptedChallenges,
                'success_rate' => $successRate,
            ],
            'categories' => $categories,
        ]);
    }

    /** POST /api/challenges/submit */
    public function submit(Request $request)
    {
        $userId = auth()->id();
        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $challengeId = $request->input('challenge_id');
        $code = $request->input('code', '');
        // C4: completion is NEVER trusted from the client — only AiController
        // sets completed=true after AI verification. This endpoint only saves attempts.
        $completed = false;

        $challenge = Challenge::find($challengeId);
        if (! $challenge) {
            return response()->json(['success' => false, 'message' => 'Challenge not found'], 404);
        }

        // Upsert user_challenges — insert on first attempt, then use atomic increments
        DB::table('user_challenges')->insertOrIgnore([
            'user_id' => $userId,
            'challenge_id' => $challengeId,
            'attempts' => 0,
            'completed' => 0,
        ]);

        $updateData = [
            'attempts' => DB::raw('attempts + 1'),
            'last_attempted' => now(),
        ];

        if ($completed) {
            $updateData['completed'] = 1;
            // Use DB-level GREATEST to avoid race condition on concurrent submissions
            $updateData['best_score'] = DB::raw('GREATEST(COALESCE(best_score, 0), '.(int) $challenge->points.')');
        }

        DB::table('user_challenges')
            ->where('user_id', $userId)
            ->where('challenge_id', $challengeId)
            ->update($updateData);

        // Read best_score directly — avoids null crash if row wasn't inserted for any reason
        $bestScore = (int) (DB::table('user_challenges')
            ->where('user_id', $userId)
            ->where('challenge_id', $challengeId)
            ->value('best_score') ?? 0);

        // Record attempt (always runs regardless of user_challenges state)
        ChallengeAttempt::updateOrCreate(
            ['user_id' => $userId, 'challenge_id' => $challengeId],
            ['code' => $code, 'completed' => $completed, 'completed_at' => $completed ? now() : null]
        );

        return response()->json([
            'success' => true,
            'message' => $completed ? 'تهانينا! تم حل التحدي بنجاح.' : 'تم حفظ المحاولة.',
            'completed' => $completed,
            'best_score' => $bestScore,
        ]);
    }

    /** GET /api/leaderboard */
    public function leaderboard(Request $request)
    {
        $leaderboard = DB::table('user_challenges as uc')
            ->join('users as u', 'uc.user_id', '=', 'u.id')
            ->select(
                'u.id',
                'u.username',
                'u.firstName',
                'u.lastName',
                DB::raw('SUM(uc.best_score) as total_points'),
                DB::raw('COUNT(CASE WHEN uc.completed = 1 THEN 1 END) as completed_challenges')
            )
            ->groupBy('u.id', 'u.username', 'u.firstName', 'u.lastName')
            ->orderBy('total_points', 'desc')
            ->limit(50)
            ->get();

        return response()->json(['success' => true, 'leaderboard' => $leaderboard]);
    }
}

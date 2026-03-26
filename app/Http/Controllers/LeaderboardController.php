<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    /** GET /api/leaderboard */
    public function index(Request $request)
    {
        $rows = DB::table('users as u')
            ->leftJoin('user_challenges as uc', function ($join) {
                $join->on('u.id', '=', 'uc.user_id')->where('uc.completed', '=', 1);
            })
            ->select(
                'u.id',
                'u.username',
                'u.firstName',
                'u.lastName',
                DB::raw('COALESCE(SUM(uc.best_score), 0) as total_points'),
                DB::raw('COUNT(CASE WHEN uc.completed = 1 THEN 1 END) as completed_challenges')
            )
            ->groupBy('u.id', 'u.username', 'u.firstName', 'u.lastName')
            ->havingRaw('total_points > 0')
            ->orderByDesc('total_points')
            ->orderByDesc('completed_challenges')
            ->limit(20)
            ->get();

        $leaderboard = $rows->values()->map(function ($user, $index) {
            $name = '';
            if (!empty($user->firstName) && !empty($user->lastName)) {
                $name = $user->firstName . ' ' . $user->lastName;
            } elseif (!empty($user->firstName)) {
                $name = $user->firstName;
            } elseif (!empty($user->username)) {
                $name = $user->username;
            } else {
                $name = 'مستخدم مجهول';
            }
            return [
                'rank'                  => $index + 1,
                'name'                  => $name,
                'points'                => (int) $user->total_points,
                'avatar_letter'         => mb_substr($name, 0, 1, 'UTF-8'),
                'completed_challenges'  => (int) $user->completed_challenges,
            ];
        });

        return response()->json([
            'success'            => true,
            'leaderboard'        => $leaderboard,
            'total_participants' => count($leaderboard),
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Repository;
use App\Models\RepoLike;
use Illuminate\Http\Request;

class RepoLikeController extends Controller
{
    public function toggle($repoId)
    {
        $repository = Repository::findOrFail($repoId);
        $userId = auth()->id();

        $like = RepoLike::where('repository_id', $repoId)
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            $like->delete();
            $repository->decrement('likes');

            return response()->json([
                'liked' => false,
                'likes' => $repository->likes,
            ]);
        }

        RepoLike::create([
            'repository_id' => $repoId,
            'user_id' => $userId,
        ]);
        $repository->increment('likes');

        return response()->json([
            'liked' => true,
            'likes' => $repository->likes,
        ], 201);
    }
}

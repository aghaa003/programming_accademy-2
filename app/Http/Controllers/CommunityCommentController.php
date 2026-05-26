<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use Illuminate\Http\Request;

class CommunityCommentController extends Controller
{
    public function store(Request $request, $postId)
    {
        $post = CommunityPost::findOrFail($postId);

        $validated = $request->validate([
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:community_post_comments,id',
        ]);

        $validated['post_id'] = $postId;
        $validated['user_id'] = auth()->id();

        $comment = CommunityPostComment::create($validated);

        $post->increment('comments_count');

        return response()->json($comment->load('user'), 201);
    }

    public function update(Request $request, $postId, $commentId)
    {
        $post = CommunityPost::findOrFail($postId);
        $comment = CommunityPostComment::where('id', $commentId)
            ->where('post_id', $postId)
            ->firstOrFail();

        // Check ownership
        if ($comment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $comment->update($validated);

        return response()->json($comment->load('user'));
    }

    public function destroy($postId, $commentId)
    {
        $post = CommunityPost::findOrFail($postId);
        $comment = CommunityPostComment::where('id', $commentId)
            ->where('post_id', $postId)
            ->firstOrFail();

        // Check ownership
        if ($comment->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->delete();
        $post->decrement('comments_count');

        return response()->json(['message' => 'Comment deleted successfully']);
    }
}

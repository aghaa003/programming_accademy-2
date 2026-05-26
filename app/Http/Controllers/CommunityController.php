<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Models\CommunityPostLike;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    public function index(Request $request)
    {
        $query = CommunityPost::query();

        if ($request->has('tag')) {
            $query->whereJsonContains('tags', $request->tag);
        }

        $posts = $query
            ->with('user')
            ->latest()
            ->paginate(15);

        return response()->json($posts->items());
    }

    public function show($id)
    {
        $post = CommunityPost::with(['user', 'comments.user', 'likes.user'])
            ->findOrFail($id);

        return response()->json($post);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'tags' => 'array|max:10',
            'tags.*' => 'string|max:50',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['likes_count'] = 0;
        $validated['comments_count'] = 0;

        $post = CommunityPost::create($validated);

        return response()->json($post->load('user'), 201);
    }

    public function update(Request $request, $id)
    {
        $post = CommunityPost::findOrFail($id);

        // Check ownership
        if ($post->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'string|max:255',
            'body' => 'string',
            'tags' => 'array|max:10',
            'tags.*' => 'string|max:50',
        ]);

        $post->update($validated);

        return response()->json($post);
    }

    public function destroy($id)
    {
        $post = CommunityPost::findOrFail($id);

        // Check ownership
        if ($post->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }

    public function like($id)
    {
        $post = CommunityPost::findOrFail($id);
        $userId = auth()->id();

        $like = CommunityPostLike::where('post_id', $id)
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            $like->delete();
            $post->decrement('likes_count');

            return response()->json([
                'liked' => false,
                'likesCount' => $post->likes_count,
            ]);
        }

        CommunityPostLike::create([
            'post_id' => $id,
            'user_id' => $userId,
        ]);
        $post->increment('likes_count');

        return response()->json([
            'liked' => true,
            'likesCount' => $post->likes_count,
        ], 201);
    }
}

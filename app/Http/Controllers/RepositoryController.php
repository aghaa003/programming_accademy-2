<?php

namespace App\Http\Controllers;

use App\Models\Repository;
use App\Models\RepoLike;
use Illuminate\Http\Request;

class RepositoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Repository::query();

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('public')) {
            $query->where('is_public', filter_var($request->public, FILTER_VALIDATE_BOOLEAN));
        }

        $repositories = $query
            ->where('is_draft', false)
            ->latest()
            ->paginate(15);

        return response()->json($repositories->items());
    }

    public function show($id)
    {
        $repository = Repository::findOrFail($id);

        return response()->json($repository);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'technologies' => 'array',
            'repo_url' => 'nullable|url',
            'live_demo_url' => 'nullable|url',
            'file_url' => 'nullable|url',
            'code_files_urls' => 'array',
            'pdf_files_urls' => 'array',
            'cover_image_url' => 'nullable|url',
            'is_public' => 'boolean',
            'is_draft' => 'boolean',
            'source_project' => 'nullable|string',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['likes'] = 0;

        $repository = Repository::create($validated);

        return response()->json($repository, 201);
    }

    public function update(Request $request, $id)
    {
        $repository = Repository::findOrFail($id);

        // Check ownership
        if ($repository->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'string',
            'technologies' => 'array',
            'repo_url' => 'nullable|url',
            'live_demo_url' => 'nullable|url',
            'file_url' => 'nullable|url',
            'code_files_urls' => 'array',
            'pdf_files_urls' => 'array',
            'cover_image_url' => 'nullable|url',
            'is_public' => 'boolean',
            'is_draft' => 'boolean',
            'source_project' => 'nullable|string',
        ]);

        $repository->update($validated);

        return response()->json($repository);
    }

    public function destroy($id)
    {
        $repository = Repository::findOrFail($id);

        // Check ownership
        if ($repository->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $repository->delete();

        return response()->json(['message' => 'Repository deleted successfully']);
    }

    public function like($id)
    {
        $repository = Repository::findOrFail($id);
        $userId = auth()->id();

        $like = RepoLike::where('repository_id', $id)
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
            'repository_id' => $id,
            'user_id' => $userId,
        ]);
        $repository->increment('likes');

        return response()->json([
            'liked' => true,
            'likes' => $repository->likes,
        ], 201);
    }
}

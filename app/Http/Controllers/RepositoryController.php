<?php

namespace App\Http\Controllers;

use App\Models\RepoLike;
use App\Models\Repository;
use Illuminate\Http\Request;

class RepositoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Repository::with('user'); // Load user relationship

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('public')) {
            $query->where('is_public', filter_var($request->public, FILTER_VALIDATE_BOOLEAN));
        }

        $repositories = $query
            ->where('is_draft', false)
            ->latest()
            ->limit(15) // Use limit instead of paginate for simpler data structure
            ->get();

        // Transform the data to match frontend expectations
        $transformed = $repositories->map(function ($repo) {
            return [
                'id' => $repo->id,
                'title' => $repo->title,
                'description' => $repo->description,
                'technologies' => $repo->technologies ?? [],
                'coverImageUrl' => $repo->cover_image_url,
                'ownerName' => $repo->user ? ($repo->user->name ?? $repo->user->username ?? 'مجهول') : 'مجهول',
            ];
        });

        return response()->json($transformed);
    }

    public function show($id)
    {
        $repository = Repository::findOrFail($id);

        return response()->json($repository);
    }

    /** POST /api/repositories */
    public function store(Request $request)
    {// Handle both frontend and backend field names
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'technologies' => 'array',
            'repo_url' => 'nullable|url',
            'live_demo_url' => 'nullable|url',
            'code_files_urls' => 'array',
            'pdf_files_urls' => 'array',
            'cover_image_url' => 'nullable|url',
            'is_public' => 'boolean',
            // Alternative field names from frontend
            'repoUrl' => 'nullable|url',
            'liveDemoUrl' => 'nullable|url',
            'codeFilesUrls' => 'array',
            'pdfFilesUrls' => 'array',
            'coverImageUrl' => 'nullable|url',
        ]);

        // Map frontend field names to database fields
        $data = [
            'title' => $validated['title'],
            'description' => $validated['description'],
            'technologies' => $validated['technologies'] ?? $request->get('technologies', []),
            'repo_url' => $validated['repo_url'] ?? $validated['repoUrl'] ?? null,
            'live_demo_url' => $validated['live_demo_url'] ?? $validated['liveDemoUrl'] ?? null,
            'code_files_urls' => $validated['code_files_urls'] ?? $validated['codeFilesUrls'] ?? [],
            'pdf_files_urls' => $validated['pdf_files_urls'] ?? $validated['pdfFilesUrls'] ?? [],
            'cover_image_url' => $validated['cover_image_url'] ?? $validated['coverImageUrl'] ?? null,
            'is_public' => $validated['is_public'] ?? $request->get('isPublic', true),
            'user_id' => auth()->id(),
            'likes' => 0,
        ];

        $repository = Repository::create($data);

        // Return the created repository with full data
        $repoWithUser = Repository::with('user')->find($repository->id);

        return response()->json([
            'id' => $repoWithUser->id,
            'title' => $repoWithUser->title,
            'description' => $repoWithUser->description,
            'technologies' => $repoWithUser->technologies ?? [],
            'coverImageUrl' => $repoWithUser->cover_image_url,
            'repoUrl' => $repoWithUser->repo_url,
            'liveDemoUrl' => $repoWithUser->live_demo_url,
            'codeFilesUrls' => $repoWithUser->code_files_urls ?? [],
            'pdfFilesUrls' => $repoWithUser->pdf_files_urls ?? [],
            'isPublic' => $repoWithUser->is_public,
            'likes' => $repoWithUser->likes,
            'createdAt' => $repoWithUser->created_at,
            'ownerName' => $repoWithUser->user ? ($repoWithUser->user->name ?? $repoWithUser->user->username ?? 'مجهول') : 'مجهول',
        ], 201);
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

    public function featured()
    {
        $repositories = Repository::with('user')
            ->where('is_draft', false)
            ->where('is_public', true)
            ->latest()
            ->limit(6)
            ->get();

        $transformed = $repositories->map(function ($repo) {
            return [
                'id' => $repo->id,
                'title' => $repo->title,
                'description' => $repo->description,
                'technologies' => $repo->technologies ?? [],
                'coverImageUrl' => $repo->cover_image_url,
                'ownerName' => $repo->user ? ($repo->user->name ?? $repo->user->username ?? 'مجهول') : 'مجهول',
            ];
        });

        return response()->json($transformed);
    }

    // Add this new method for user-specific repositories (for profile page)
    public function userRepositories($userId)
    {
        $repositories = Repository::with('user')
            ->where('user_id', $userId)
            ->where('is_draft', false)
            ->latest()
            ->get();

        // Transform the data with full details for profile page
        $transformed = $repositories->map(function ($repo) {
            return [
                'id' => $repo->id,
                'title' => $repo->title,
                'description' => $repo->description,
                'technologies' => $repo->technologies ?? [],
                'coverImageUrl' => $repo->cover_image_url,
                'repoUrl' => $repo->repo_url,
                'liveDemoUrl' => $repo->live_demo_url,
                'codeFilesUrls' => $repo->code_files_urls ?? [],
                'pdfFilesUrls' => $repo->pdf_files_urls ?? [],
                'isPublic' => $repo->is_public,
                'likes' => $repo->likes,
                'createdAt' => $repo->created_at,
                'ownerName' => $repo->user ? ($repo->user->name ?? $repo->user->username ?? 'مجهول') : 'مجهول',
            ];
        });

        return response()->json(['repositories' => $transformed]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class UploadController extends Controller
{
    public function __construct()
    {
        // Allow these methods to bypass CSRF for API calls
        $this->middleware('auth:sanctum')->except(['store', 'storeMultiple']);
    }

    // Single file upload
    public function store(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:10240', // 10MB max
            ]);

            $file = $request->file('file');
            if (! $file) {
                return response()->json(['error' => 'No file provided'], 400);
            }

            $path = $file->store('uploads', 'public');

            return response()->json([
                'file' => [
                    'url' => Storage::url($path),
                    'path' => $path,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed: '.$e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Upload failed: '.$e->getMessage()], 500);
        }
    }

    // Multiple files upload
    public function storeMultiple(Request $request)
    {
        try {
            $request->validate([
                'files' => 'required|array',
                'files.*' => 'file|max:10240',
            ]);

            $urls = [];
            foreach ($request->file('files') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('uploads', 'public');
                    $urls[] = Storage::url($path);
                }
            }

            return response()->json(['urls' => $urls]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed: '.$e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Upload failed: '.$e->getMessage()], 500);
        }
    }

    public function destroy($uploadId)
    {
        $upload = Upload::where('id', $uploadId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        Storage::disk('public')->delete($upload->stored_path);
        $upload->delete();

        return response()->json(['message' => 'Upload deleted successfully']);
    }

    public function getUserUploads(Request $request)
    {
        $uploads = Upload::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return response()->json($uploads->items());
    }
}

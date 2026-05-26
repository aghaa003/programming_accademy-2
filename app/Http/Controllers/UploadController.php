<?php

namespace App\Http\Controllers;

use App\Models\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:52428800', // 50MB max
        ]);

        $file = $validated['file'];
        $path = $file->store('uploads', 'public');
        $url = Storage::url($path);

        $upload = Upload::create([
            'user_id' => auth()->id(),
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'stored_path' => $path,
            'url' => $url,
        ]);

        return response()->json($upload, 201);
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

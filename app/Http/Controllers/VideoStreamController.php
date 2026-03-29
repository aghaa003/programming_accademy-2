<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VideoStreamController extends Controller
{
    /** GET /api/stream-video?lesson_id=X */
    public function stream(Request $request)
    {
        $userId = auth()->id();
        if (! $userId) {
            return response('Unauthorized', 401);
        }

        $lessonId = $request->query('lesson_id');
        if (! $lessonId) {
            return response('Missing lesson_id', 400);
        }

        $lesson = Lesson::select('video_path', 'video_mime_type', 'title')->find($lessonId);
        if (! $lesson) {
            return response('Video not found', 404);
        }

        // Build full path - video_path stores relative path like videos/Category/courseId/file.mp4
        $relativePath = str_replace('\\', '/', $lesson->video_path);

        $videoPath = realpath(storage_path('app/'.$relativePath));
        $basePath = realpath(storage_path('app/videos'));

        // Security: ensure resolved path is within storage/app/videos
        if (! $videoPath || ! $basePath || ! str_starts_with($videoPath, $basePath)) {
            return response('Access denied', 403);
        }

        if (! file_exists($videoPath)) {
            return response('Video file not found', 404);
        }

        $fileSize = filesize($videoPath);
        // Whitelist MIME type — never use raw DB value directly in response headers (header injection)
        $allowedMimes = ['video/mp4', 'video/webm', 'video/ogg', 'video/x-msvideo', 'video/quicktime'];
        $mimeType = in_array($lesson->video_mime_type, $allowedMimes, true)
            ? $lesson->video_mime_type
            : 'video/mp4';

        $start = 0;
        $end = $fileSize - 1;
        $length = $fileSize;
        $status = 200;

        $headers = [
            'Content-Type' => $mimeType,
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'Content-Disposition' => 'inline; filename="'.basename($videoPath).'"',
            'X-Content-Type-Options' => 'nosniff',
        ];

        // Handle Range header
        $rangeHeader = $request->header('Range');
        if ($rangeHeader && preg_match('/bytes=(\d+)-(\d*)/', $rangeHeader, $matches)) {
            $start = (int) $matches[1];
            $end = $matches[2] !== '' ? (int) $matches[2] : $end;

            if ($start > $end || $start >= $fileSize) {
                return response('Range Not Satisfiable', 416)
                    ->header('Content-Range', "bytes */$fileSize");
            }

            $length = $end - $start + 1;
            $status = 206;
            $headers['Content-Range'] = "bytes $start-$end/$fileSize";
        }

        $headers['Content-Length'] = $length;

        return new StreamedResponse(function () use ($videoPath, $start, $length) {
            $fp = fopen($videoPath, 'rb');
            if (! $fp) {
                return;
            }

            fseek($fp, $start);
            $bufferSize = 8192;
            $bytesRemaining = $length;

            while (! feof($fp) && $bytesRemaining > 0 && connection_status() === 0) {
                $read = min($bufferSize, $bytesRemaining);
                echo fread($fp, $read);
                flush();
                $bytesRemaining -= $read;
            }

            fclose($fp);
        }, $status, $headers);
    }
}

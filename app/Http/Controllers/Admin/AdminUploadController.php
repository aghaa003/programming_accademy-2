<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUploadController extends Controller
{
    /** POST /api/admin/upload */
    public function upload(Request $request)
    {
        DB::beginTransaction();
        try {
            $courseId = $request->input('course_id');
            $courseTitle = '';
            $courseCategory = '';
            $courseLogoPath = null;

            // -------------------------------------------------------
            // Step 1: Create new course OR fetch existing
            // -------------------------------------------------------
            if ($courseId === 'new') {
                $newTitle = $request->input('new_course_title');
                $newCategory = $request->input('new_course_category');
                $newDescription = $request->input('new_course_description');
                $newPoints = $request->input('new_course_main_points');
                $newLevel = $request->input('level');

                if (empty($newTitle) || empty($newCategory)) {
                    return response()->json(['success' => false, 'message' => 'New course title and category are required.'], 400);
                }

                // Handle optional logo upload
                if ($request->hasFile('course_logo')) {
                    $logoFile = $request->file('course_logo');
                    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

                    if (! in_array($logoFile->getMimeType(), $allowed)) {
                        return response()->json(['success' => false, 'message' => 'Invalid logo file type. Only JPEG, PNG, GIF, WebP allowed.'], 400);
                    }
                    if ($logoFile->getSize() > 5 * 1024 * 1024) {
                        return response()->json(['success' => false, 'message' => 'Logo file exceeds 5MB limit.'], 400);
                    }

                    $logoMimeToExt = ['image/jpeg' => 'jpg', 'image/jpg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                    $logoExt = $logoMimeToExt[$logoFile->getMimeType()] ?? 'jpg';
                    $logoFilename = 'logo_'.uniqid().'.'.$logoExt;

                    $logosDir = public_path('uploads/logos');
                    if (! is_dir($logosDir)) {
                        mkdir($logosDir, 0755, true);
                    }

                    $logoFile->move($logosDir, $logoFilename);
                    $courseLogoPath = 'uploads/logos/'.$logoFilename;
                }

                $course = Course::create([
                    'title' => $newTitle,
                    'description' => $newDescription,
                    'main_points' => $newPoints,
                    'category' => $newCategory,
                    'logo_path' => $courseLogoPath,
                    'level' => $newLevel,
                ]);

                $courseId = $course->id;
                $courseTitle = $newTitle;
                $courseCategory = $newCategory;

            } else {
                if (empty($courseId)) {
                    return response()->json(['success' => false, 'message' => 'Please select a course or choose to create a new one.'], 400);
                }
                $course = Course::find($courseId);
                if (! $course) {
                    return response()->json(['success' => false, 'message' => 'Selected course does not exist.'], 404);
                }
                $courseTitle = $course->title;
                $courseCategory = $course->category;
            }

            // -------------------------------------------------------
            // Step 2: Process uploaded video files
            // -------------------------------------------------------
            $videos = $request->file('videos');

            if (! $videos || empty($videos)) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "تم حفظ الكورس '{$courseTitle}' بدون دروس.",
                    'course_id' => $courseId,
                ]);
            }

            $titles = $request->input('titles', []);
            if (count($videos) !== count($titles)) {
                DB::rollBack();

                return response()->json(['success' => false, 'message' => 'عدد الفيديوهات لا يتطابق مع عدد العناوين.'], 400);
            }

            $safeCategory = $this->sanitizeFolderName($courseCategory ?: 'Other');
            $courseUploadDir = storage_path('app/videos').DIRECTORY_SEPARATOR.$safeCategory.DIRECTORY_SEPARATOR.$courseId;

            if (! is_dir($courseUploadDir)) {
                if (! mkdir($courseUploadDir, 0755, true)) {
                    return response()->json(['success' => false, 'message' => 'Failed to create upload directory.'], 500);
                }
            }

            $baseSortOrder = (int) DB::table('lessons')->where('course_id', $courseId)->max('sort_order');

            $allowedVideoMimes = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo', 'video/mpeg'];
            $videoMimeToExt = ['video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/quicktime' => 'mov', 'video/x-msvideo' => 'avi', 'video/mpeg' => 'mpeg'];

            $movedFiles = [];

            for ($i = 0; $i < count($videos); $i++) {
                $video = $videos[$i];
                $videoMime = $video->getMimeType();

                if (! in_array($videoMime, $allowedVideoMimes)) {
                    // Roll back DB and delete any files already moved in previous iterations
                    DB::rollBack();
                    foreach ($movedFiles as $f) {
                        if (file_exists($f)) {
                            @unlink($f);
                        }
                    }
                    return response()->json(['success' => false, 'message' => 'نوع الملف غير مدعوم. يُسمح بـ MP4, WebM, MOV, AVI فقط.'], 400);
                }

                $ext = $videoMimeToExt[$videoMime] ?? 'mp4';
                $uniqueFilename = uniqid('lesson_', true).'.'.$ext;

                $video->move($courseUploadDir, $uniqueFilename);
                $movedFiles[] = $courseUploadDir.DIRECTORY_SEPARATOR.$uniqueFilename;

                $relativePath = 'videos/'.$safeCategory.'/'.$courseId.'/'.$uniqueFilename;

                Lesson::create([
                    'course_id' => $courseId,
                    'title' => $titles[$i],
                    'description' => $request->input("descriptions.{$i}"),
                    'video_path' => $relativePath,
                    'video_mime_type' => $videoMime,
                    'resources_code' => $request->input("codes.{$i}"),
                    'sort_order' => $baseSortOrder + $i + 1,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم رفع '.count($videos).' درس بنجاح للكورس \''.$courseTitle.'\'.',
                'course_id' => $courseId,
                'lesson_count' => count($videos),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($movedFiles ?? [] as $movedFile) {
                if (file_exists($movedFile)) {
                    @unlink($movedFile);
                }
            }
            // Clean up orphaned logo file if course creation was rolled back
            if (! empty($courseLogoPath)) {
                $logoFullPath = public_path($courseLogoPath);
                if (is_file($logoFullPath)) {
                    @unlink($logoFullPath);
                }
            }
            \Log::error('AdminUploadController error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'فشل رفع الملفات، يرجى المحاولة مجدداً.'], 500);
        }
    }

    private function sanitizeFolderName(string $name): string
    {
        $name = preg_replace('/[^\w\-\s]/u', '', $name);
        $name = trim(str_replace(' ', '_', $name));

        return empty($name) ? 'default_folder' : mb_substr($name, 0, 100, 'UTF-8');
    }
}

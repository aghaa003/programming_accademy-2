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
            $courseId      = $request->input('course_id');
            $courseTitle   = '';
            $courseCategory = '';
            $courseLogoPath = null;

            // -------------------------------------------------------
            // Step 1: Create new course OR fetch existing
            // -------------------------------------------------------
            if ($courseId === 'new') {
                $newTitle       = $request->input('new_course_title');
                $newCategory    = $request->input('new_course_category');
                $newDescription = $request->input('new_course_description');
                $newPoints      = $request->input('new_course_main_points');
                $newLevel       = $request->input('level');

                if (empty($newTitle) || empty($newCategory)) {
                    return response()->json(['success' => false, 'message' => 'New course title and category are required.'], 400);
                }

                // Handle optional logo upload
                if ($request->hasFile('course_logo')) {
                    $logoFile = $request->file('course_logo');
                    $allowed  = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

                    if (!in_array($logoFile->getMimeType(), $allowed)) {
                        return response()->json(['success' => false, 'message' => 'Invalid logo file type. Only JPEG, PNG, GIF, WebP allowed.'], 400);
                    }
                    if ($logoFile->getSize() > 5 * 1024 * 1024) {
                        return response()->json(['success' => false, 'message' => 'Logo file exceeds 5MB limit.'], 400);
                    }

                    $safeName    = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME));
                    $safeName    = trim(preg_replace('/_+/', '_', $safeName), '_') ?: 'course_logo';
                    $logoFilename = 'logo_' . uniqid() . '_' . $safeName . '.' . strtolower($logoFile->getClientOriginalExtension());

                    $logosDir = public_path('uploads/logos');
                    if (!is_dir($logosDir)) {
                        mkdir($logosDir, 0755, true);
                    }

                    $logoFile->move($logosDir, $logoFilename);
                    $courseLogoPath = 'uploads/logos/' . $logoFilename;
                }

                $course = Course::create([
                    'title'       => $newTitle,
                    'description' => $newDescription,
                    'main_points' => $newPoints,
                    'category'    => $newCategory,
                    'logo_path'   => $courseLogoPath,
                    'level'       => $newLevel,
                ]);

                $courseId       = $course->id;
                $courseTitle    = $newTitle;
                $courseCategory = $newCategory;

            } else {
                if (empty($courseId)) {
                    return response()->json(['success' => false, 'message' => 'Please select a course or choose to create a new one.'], 400);
                }
                $course = Course::find($courseId);
                if (!$course) {
                    return response()->json(['success' => false, 'message' => 'Selected course does not exist.'], 404);
                }
                $courseTitle    = $course->title;
                $courseCategory = $course->category;
            }

            // -------------------------------------------------------
            // Step 2: Process uploaded video files
            // -------------------------------------------------------
            $videos = $request->file('videos');

            if (!$videos || empty($videos)) {
                DB::commit();
                return response()->json([
                    'success'   => true,
                    'message'   => "تم حفظ الكورس '{$courseTitle}' بدون دروس.",
                    'course_id' => $courseId,
                ]);
            }

            $titles = $request->input('titles', []);
            if (count($videos) !== count($titles)) {
                return response()->json(['success' => false, 'message' => 'عدد الفيديوهات لا يتطابق مع عدد العناوين.'], 400);
            }

            $safeCategory     = $this->sanitizeFolderName($courseCategory ?: 'Other');
            $courseUploadDir  = storage_path('app/videos') . DIRECTORY_SEPARATOR . $safeCategory . DIRECTORY_SEPARATOR . $courseId;

            if (!is_dir($courseUploadDir)) {
                if (!mkdir($courseUploadDir, 0755, true)) {
                    return response()->json(['success' => false, 'message' => 'Failed to create upload directory.'], 500);
                }
            }

            $baseSortOrder = (int) DB::table('lessons')->where('course_id', $courseId)->max('sort_order');

            for ($i = 0; $i < count($videos); $i++) {
                $video          = $videos[$i];
                $ext            = strtolower($video->getClientOriginalExtension());
                $uniqueFilename = uniqid('lesson_', true) . '.' . $ext;

                $video->move($courseUploadDir, $uniqueFilename);

                $relativePath = 'videos' . DIRECTORY_SEPARATOR . $safeCategory . DIRECTORY_SEPARATOR . $courseId . DIRECTORY_SEPARATOR . $uniqueFilename;

                Lesson::create([
                    'course_id'      => $courseId,
                    'title'          => $titles[$i],
                    'description'    => $request->input("descriptions.{$i}"),
                    'video_path'      => $relativePath,
                    'video_mime_type' => $video->getClientMimeType(),
                    'resources_code' => $request->input("codes.{$i}"),
                    'sort_order'     => $baseSortOrder + $i + 1,
                ]);
            }

            DB::commit();

            return response()->json([
                'success'      => true,
                'message'      => 'تم رفع ' . count($videos) . ' درس بنجاح للكورس \'' . $courseTitle . '\'.',
                'course_id'    => $courseId,
                'lesson_count' => count($videos),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function sanitizeFolderName(string $name): string
    {
        $name = preg_replace('/[^\w\-\s\.]/u', '', $name);
        $name = trim(str_replace(' ', '_', $name));
        return empty($name) ? 'default_folder' : mb_substr($name, 0, 100, 'UTF-8');
    }
}

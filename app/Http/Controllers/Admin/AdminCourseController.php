<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCourseController extends Controller
{
    /** GET /api/admin/courses */
    public function index()
    {
        $courses = Course::withCount('lessons')->orderBy('id', 'desc')->get();

        return response()->json(['success' => true, 'courses' => $courses]);
    }

    /** GET /api/admin/courses/{id} */
    public function show($id)
    {
        $course = Course::find($id);
        if (! $course) {
            return response()->json(['success' => false, 'message' => 'الدورة غير موجودة'], 404);
        }

        return response()->json(['success' => true, 'course' => $course]);
    }

    /** POST /api/admin/courses */
    public function store(Request $request)
    {
        if (empty(trim($request->input('title', '')))) {
            return response()->json(['success' => false, 'message' => 'عنوان الكورس مطلوب.'], 400);
        }
        if (empty(trim($request->input('category', '')))) {
            return response()->json(['success' => false, 'message' => 'تصنيف الكورس مطلوب.'], 400);
        }

        $course = Course::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'main_points' => $request->input('main_points'),
            'category' => $request->input('category'),
            'level' => $request->input('level', 'Beginner'),
            'is_active' => $request->input('is_active', 1),
        ]);

        return response()->json(['success' => true, 'message' => 'تم إنشاء الكورس بنجاح', 'course' => $course], 201);
    }

    /** PUT /api/admin/courses/{id} */
    public function update(Request $request, $id)
    {
        $course = Course::find($id);
        if (! $course) {
            return response()->json(['success' => false, 'message' => 'الدورة غير موجودة'], 404);
        }

        $course->fill($request->only(['title', 'description', 'main_points', 'category', 'level', 'is_active']));
        $course->save();

        return response()->json(['success' => true, 'message' => 'تم تحديث الكورس بنجاح', 'course' => $course]);
    }

    /** DELETE /api/admin/courses/{id} */
    public function destroy($id)
    {
        $course = Course::find($id);
        if (! $course) {
            return response()->json(['success' => false, 'message' => 'الدورة غير موجودة'], 404);
        }

        // Remove user_assignments first (RESTRICT FK blocks cascade delete of assignments)
        $assignmentIds = DB::table('assignments')->where('course_id', $id)->pluck('id');
        if ($assignmentIds->isNotEmpty()) {
            DB::table('user_assignments')->whereIn('assignment_id', $assignmentIds)->delete();
        }

        // Remove user progress records for lessons and course
        $lessonIds = DB::table('lessons')->where('course_id', $id)->pluck('id');
        if ($lessonIds->isNotEmpty()) {
            DB::table('user_lesson_progress')->whereIn('lesson_id', $lessonIds)->delete();
        }
        DB::table('user_course_progress')->where('course_id', $id)->delete();

        // Delete lesson video files from storage
        $lessons = Lesson::where('course_id', $id)->select('video_path')->get();
        foreach ($lessons as $lesson) {
            if ($lesson->video_path) {
                $relativePath = str_replace('\\', '/', $lesson->video_path);
                $videoPath = storage_path('app/'.$relativePath);
                if (is_file($videoPath)) {
                    @unlink($videoPath);
                }
            }
        }

        // Delete course logo file
        if ($course->logo_path) {
            $logoPath = public_path(ltrim($course->logo_path, '/'));
            if (is_file($logoPath)) {
                @unlink($logoPath);
            }
        }

        $course->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف الكورس بنجاح']);
    }

    /** GET /api/admin/courses/{courseId}/lessons */
    public function lessons($courseId)
    {
        $lessons = Lesson::where('course_id', $courseId)
            ->select('id', 'title', 'description', 'sort_order', 'resources_code', 'video_path', 'video_mime_type', 'views', 'created_at', 'updated_at')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function ($l) {
                $arr = $l->toArray();
                $arr['video_url'] = url('/api/stream-video?lesson_id='.$l->id);

                return $arr;
            });

        return response()->json(['success' => true, 'lessons' => $lessons]);
    }

    /** POST /api/admin/lessons - upload lesson video */
    public function storeLesson(Request $request)
    {
        $courseId = $request->input('course_id');
        $title = $request->input('title');
        $description = $request->input('description', '');
        $sortOrder = (int) $request->input('sort_order', 0);
        $resources = $request->input('resources_code', '');

        if (! $request->hasFile('video')) {
            return response()->json(['success' => false, 'message' => 'No video file provided'], 400);
        }

        $file = $request->file('video');
        $mimeType = $file->getMimeType();

        $allowedVideoMimes = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo', 'video/mpeg'];
        if (!in_array($mimeType, $allowedVideoMimes)) {
            return response()->json(['success' => false, 'message' => 'نوع الملف غير مدعوم. يُسمح بـ MP4, WebM, MOV, AVI فقط.'], 400);
        }
        $videoMimeToExt = ['video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/quicktime' => 'mov', 'video/x-msvideo' => 'avi', 'video/mpeg' => 'mpeg'];
        $ext = $videoMimeToExt[$mimeType] ?? 'mp4';

        $course = Course::find($courseId);
        $catDir = $course ? $course->category : 'misc';
        $dest = storage_path('app/videos/'.$catDir.'/'.$courseId);
        if (! is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $filename = 'lesson_'.uniqid().'.'.$ext;
        $file->move($dest, $filename);

        $relativePath = 'videos/'.$catDir.'/'.$courseId.'/'.$filename;

        $lesson = Lesson::create([
            'course_id' => $courseId,
            'title' => $title,
            'description' => $description,
            'sort_order' => $sortOrder,
            'video_path' => $relativePath,
            'video_mime_type' => $mimeType,
            'resources_code' => $resources,
        ]);

        return response()->json(['success' => true, 'message' => 'تم رفع الدرس بنجاح', 'lesson' => $lesson], 201);
    }

    /** PUT /api/admin/lessons/{id} */
    public function updateLesson(Request $request, $id)
    {
        $lesson = Lesson::find($id);
        if (! $lesson) {
            return response()->json(['success' => false, 'message' => 'الدرس غير موجود'], 404);
        }

        $lesson->fill($request->only(['title', 'description', 'sort_order', 'resources_code']));

        // Handle optional video replacement
        if ($request->hasFile('video') || $request->hasFile('lesson_video')) {
            $file = $request->file('video') ?: $request->file('lesson_video');
            $mimeType = $file->getMimeType();

            $allowedVideoMimes = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo', 'video/mpeg'];
            if (!in_array($mimeType, $allowedVideoMimes)) {
                return response()->json(['success' => false, 'message' => 'نوع الملف غير مدعوم. يُسمح بـ MP4, WebM, MOV, AVI فقط.'], 400);
            }
            $videoMimeToExt = ['video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/quicktime' => 'mov', 'video/x-msvideo' => 'avi', 'video/mpeg' => 'mpeg'];
            $ext = $videoMimeToExt[$mimeType] ?? 'mp4';

            $course = Course::find($lesson->course_id);
            $catDir = $course ? $course->category : 'misc';
            $dest = storage_path('app/videos/'.$catDir.'/'.$lesson->course_id);
            if (! is_dir($dest)) {
                mkdir($dest, 0755, true);
            }
            $filename = 'lesson_'.uniqid().'.'.$ext;
            $file->move($dest, $filename);

            // Delete old video only after the new file is saved successfully
            if ($lesson->video_path) {
                $oldPath = storage_path('app/'.str_replace('\\', '/', $lesson->video_path));
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $lesson->video_path = 'videos/'.$catDir.'/'.$lesson->course_id.'/'.$filename;
            $lesson->video_mime_type = $mimeType;
        }

        $lesson->save();

        return response()->json(['success' => true, 'message' => 'تم تحديث الدرس بنجاح']);
    }

    /** DELETE /api/admin/lessons/{id} */
    public function destroyLesson($id)
    {
        $lesson = Lesson::find($id);
        if (! $lesson) {
            return response()->json(['success' => false, 'message' => 'الدرس غير موجود'], 404);
        }

        // Delete video file from storage if it's a file-based lesson
        if ($lesson->video_path) {
            $relativePath = str_replace('\\', '/', $lesson->video_path);
            $videoPath = storage_path('app/'.$relativePath);
            if (is_file($videoPath)) {
                @unlink($videoPath);
            }
        }

        $lesson->delete();

        return response()->json(['success' => true, 'message' => 'تم حذف الدرس بنجاح']);
    }

    /** POST /api/admin/lessons/{id}/reorder */
    public function reorderLesson(Request $request, $id)
    {
        $lesson = Lesson::find($id);
        if (! $lesson) {
            return response()->json(['success' => false, 'message' => 'الدرس غير موجود'], 404);
        }

        $direction = $request->input('direction', 'up');
        $currentOrder = $lesson->sort_order ?? 0;

        if ($direction === 'up') {
            $swap = Lesson::where('course_id', $lesson->course_id)
                ->where('sort_order', '<', $currentOrder)
                ->orderByDesc('sort_order')
                ->first();
        } else {
            $swap = Lesson::where('course_id', $lesson->course_id)
                ->where('sort_order', '>', $currentOrder)
                ->orderBy('sort_order')
                ->first();
        }

        if ($swap) {
            $swapOrder = $swap->sort_order;
            $swap->sort_order = $currentOrder;
            $lesson->sort_order = $swapOrder;
            $swap->save();
            $lesson->save();
        }

        return response()->json(['success' => true, 'message' => 'تم إعادة ترتيب الدرس']);
    }

    /** POST /api/admin/courses/{id}/upload-logo */
    public function uploadLogo(Request $request, $id)
    {
        $course = Course::find($id);
        if (! $course) {
            return response()->json(['success' => false, 'message' => 'الدورة غير موجودة'], 404);
        }

        if (! $request->hasFile('logo')) {
            return response()->json(['success' => false, 'message' => 'No file provided'], 400);
        }

        $file = $request->file('logo');
        $dest = public_path('uploads/logos');
        if (! is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $filename = 'logo_'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->move($dest, $filename);

        $course->logo_path = 'uploads/logos/'.$filename;
        $course->save();

        return response()->json(['success' => true, 'logo_path' => $course->logo_path]);
    }
}

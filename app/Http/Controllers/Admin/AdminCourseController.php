<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminCourseRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminCourseController extends Controller
{
    /** GET /api/admin/courses */
    public function index(Request $request)
    {
        // M4: Paginate admin course list
        $limit = min((int) $request->query('limit', 20), 100);
        $offset = max((int) $request->query('offset', 0), 0);
        $total = Course::count();

        $courses = Course::withCount('lessons')->orderBy('id', 'desc')->skip($offset)->take($limit)->get();

        return response()->json(['success' => true, 'courses' => $courses, 'total' => $total]);
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
    public function store(AdminCourseRequest $request)
    {
        $course = Course::create(array_merge(
            $request->validated(),
            ['level' => $request->input('level', 'Beginner'), 'is_active' => $request->input('is_active', 1)]
        ));

        AuditLogger::log($request, 'create_course', 'Course', $course->id, ['title' => $course->title]);

        return response()->json(['success' => true, 'message' => 'تم إنشاء الكورس بنجاح', 'course' => $course], 201);
    }

    /** PUT /api/admin/courses/{id} */
    public function update(AdminCourseRequest $request, $id)
    {
        $course = Course::find($id);
        if (! $course) {
            return response()->json(['success' => false, 'message' => 'الدورة غير موجودة'], 404);
        }

        $course->fill($request->validated());
        $course->save();

        AuditLogger::log($request, 'update_course', 'Course', $course->id, ['title' => $course->title]);

        return response()->json(['success' => true, 'message' => 'تم تحديث الكورس بنجاح', 'course' => $course]);
    }

    /** DELETE /api/admin/courses/{id} */
    public function destroy($id)
    {
        $course = Course::find($id);
        if (! $course) {
            return response()->json(['success' => false, 'message' => 'الدورة غير موجودة'], 404);
        }

        // Collect file paths before the transaction so we can delete them after commit
        $lessons = Lesson::where('course_id', $id)->select('video_path')->get();
        $videoPaths = $lessons->filter(fn ($l) => $l->video_path)
            ->map(fn ($l) => storage_path('app/'.str_replace('\\', '/', $l->video_path)))
            ->all();
        $logoPath = $course->logo_path ? public_path(ltrim($course->logo_path, '/')) : null;
        $courseTitle = $course->title;

        try {
            DB::transaction(function () use ($id) {
                // Delete child records manually to avoid FK RESTRICT violations on assignments
                $assignmentIds = DB::table('assignments')->where('course_id', $id)->pluck('id');
                if ($assignmentIds->isNotEmpty()) {
                    DB::table('user_assignments')->whereIn('assignment_id', $assignmentIds)->delete();
                }
                DB::table('assignments')->where('course_id', $id)->delete();

                // Delete lesson progress records before deleting lessons
                $lessonIds = DB::table('lessons')->where('course_id', $id)->pluck('id');
                if ($lessonIds->isNotEmpty()) {
                    DB::table('user_lesson_progress')->whereIn('lesson_id', $lessonIds)->delete();
                }
                DB::table('user_course_progress')->where('course_id', $id)->delete();
                DB::table('lessons')->where('course_id', $id)->delete();

                // Hard-delete the course row so the record is truly removed (not just soft-deleted).
                // Using DB::table avoids a second Course::find() call inside the transaction which
                // would return null if the model was already soft-deleted by a previous failed attempt.
                DB::table('courses')->where('id', $id)->delete();
            });
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'فشل في حذف الكورس: '.$e->getMessage()], 500);
        }

        // Delete files only after the DB transaction committed successfully
        foreach ($videoPaths as $videoPath) {
            if (is_file($videoPath)) {
                @unlink($videoPath);
            }
        }
        if ($logoPath && is_file($logoPath)) {
            @unlink($logoPath);
        }

        // Audit log is non-fatal — a logging failure must not hide a successful delete
        try {
            AuditLogger::log(request(), 'delete_course', 'Course', (int) $id, ['title' => $courseTitle]);
        } catch (\Throwable) {
            // Intentionally swallowed — transaction already committed
        }

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

        // N32: Validate title is not empty
        if (empty(trim($title ?? ''))) {
            return response()->json(['success' => false, 'message' => 'عنوان الدرس مطلوب.'], 400);
        }

        if (! $courseId || ! Course::where('id', $courseId)->where('is_active', 1)->exists()) {
            return response()->json(['success' => false, 'message' => 'الكورس غير موجود أو غير مفعّل.'], 422);
        }

        if (! $request->hasFile('video')) {
            return response()->json(['success' => false, 'message' => 'No video file provided'], 400);
        }

        $file = $request->file('video');
        $mimeType = $file->getMimeType();

        $allowedVideoMimes = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo', 'video/mpeg'];
        // Fallback: Windows/XAMPP finfo may return application/octet-stream for valid MP4/WebM files
        if (! in_array($mimeType, $allowedVideoMimes)) {
            $extToMime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime', 'avi' => 'video/x-msvideo', 'mpeg' => 'video/mpeg', 'mpg' => 'video/mpeg'];
            $clientExt = strtolower($file->getClientOriginalExtension());
            if (isset($extToMime[$clientExt])) {
                $mimeType = $extToMime[$clientExt];
            }
        }
        if (! in_array($mimeType, $allowedVideoMimes)) {
            return response()->json(['success' => false, 'message' => 'نوع الملف غير مدعوم. يُسمح بـ MP4, WebM, MOV, AVI فقط.'], 400);
        }
        $videoMimeToExt = ['video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/quicktime' => 'mov', 'video/x-msvideo' => 'avi', 'video/mpeg' => 'mpeg'];
        $ext = $videoMimeToExt[$mimeType] ?? 'mp4';

        $course = Course::find($courseId);
        $catDir = $course ? preg_replace('/[^\p{L}\p{N}_\- ]/u', '_', $course->category) : 'misc';
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

        AuditLogger::log($request, 'create_lesson', 'Lesson', $lesson->id, ['title' => $lesson->title, 'course_id' => $courseId]);

        return response()->json(['success' => true, 'message' => 'تم رفع الدرس بنجاح', 'lesson' => $lesson], 201);
    }

    /** PUT /api/admin/lessons/{id} */
    public function updateLesson(Request $request, $id)
    {
        $lesson = Lesson::find($id);
        if (! $lesson) {
            return response()->json(['success' => false, 'message' => 'الدرس غير موجود'], 404);
        }

        // N33: Validate title is not blanked on update
        if ($request->has('title') && empty(trim($request->input('title', '')))) {
            return response()->json(['success' => false, 'message' => 'عنوان الدرس مطلوب.'], 400);
        }

        $lesson->fill($request->only(['title', 'description', 'sort_order', 'resources_code']));

        // Handle optional video replacement
        if ($request->hasFile('video') || $request->hasFile('lesson_video')) {
            $file = $request->file('video') ?: $request->file('lesson_video');
            $mimeType = $file->getMimeType();

            $allowedVideoMimes = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo', 'video/mpeg'];
            // Fallback: Windows/XAMPP finfo may return application/octet-stream for valid MP4/WebM files
            if (! in_array($mimeType, $allowedVideoMimes)) {
                $extToMime = ['mp4' => 'video/mp4', 'webm' => 'video/webm', 'mov' => 'video/quicktime', 'avi' => 'video/x-msvideo', 'mpeg' => 'video/mpeg', 'mpg' => 'video/mpeg'];
                $clientExt = strtolower($file->getClientOriginalExtension());
                if (isset($extToMime[$clientExt])) {
                    $mimeType = $extToMime[$clientExt];
                }
            }
            if (! in_array($mimeType, $allowedVideoMimes)) {
                return response()->json(['success' => false, 'message' => 'نوع الملف غير مدعوم. يُسمح بـ MP4, WebM, MOV, AVI فقط.'], 400);
            }
            $videoMimeToExt = ['video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/quicktime' => 'mov', 'video/x-msvideo' => 'avi', 'video/mpeg' => 'mpeg'];
            $ext = $videoMimeToExt[$mimeType] ?? 'mp4';

            $course = Course::find($lesson->course_id);
            $catDir = $course ? preg_replace('/[^\p{L}\p{N}_\- ]/u', '_', $course->category) : 'misc';
            $dest = storage_path('app/videos/'.$catDir.'/'.$lesson->course_id);
            if (! is_dir($dest)) {
                mkdir($dest, 0755, true);
            }
            // N37: Collect old path before overwriting it, so we can delete after DB save
            $oldVideoPath = null;
            if ($lesson->video_path) {
                $candidate = storage_path('app/'.str_replace('\\', '/', $lesson->video_path));
                if (is_file($candidate)) {
                    $oldVideoPath = $candidate;
                }
            }

            $filename = 'lesson_'.uniqid().'.'.$ext;
            $file->move($dest, $filename);

            $lesson->video_path = 'videos/'.$catDir.'/'.$lesson->course_id.'/'.$filename;
            $lesson->video_mime_type = $mimeType;
        }

        $lesson->save();

        // Delete old video file only after DB write succeeded
        if (isset($oldVideoPath) && $oldVideoPath) {
            @unlink($oldVideoPath);
        }

        AuditLogger::log($request, 'update_lesson', 'Lesson', $lesson->id, ['title' => $lesson->title]);

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
        $videoPath = null;
        if ($lesson->video_path) {
            $relativePath = str_replace('\\', '/', $lesson->video_path);
            $videoPath = storage_path('app/'.$relativePath);
        }

        DB::transaction(function () use ($lesson) {
            // Clean orphaned user_lesson_progress rows for this lesson
            DB::table('user_lesson_progress')->where('lesson_id', $lesson->id)->delete();
            $lesson->delete();
        });

        // Delete video file only after DB transaction succeeded
        if ($videoPath && is_file($videoPath)) {
            @unlink($videoPath);
        }

        AuditLogger::log(request(), 'delete_lesson', 'Lesson', (int) $id, ['title' => $lesson->title, 'course_id' => $lesson->course_id]);

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
            DB::transaction(function () use ($swap, $lesson, $currentOrder) {
                $swapOrder = $swap->sort_order;
                $swap->sort_order = $currentOrder;
                $lesson->sort_order = $swapOrder;
                $swap->save();
                $lesson->save();
            });
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
        $mimeType = $file->getMimeType();
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        // Extension fallback: Windows/XAMPP finfo may return application/octet-stream
        // for valid image files. Use the client-supplied extension ONLY to select a MIME
        // from a hardcoded whitelist — we never trust the extension itself.
        if (! in_array($mimeType, $allowed)) {
            $extMimeMap = [
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
            ];
            $clientExt = strtolower($file->getClientOriginalExtension());
            if (isset($extMimeMap[$clientExt])) {
                $mimeType = $extMimeMap[$clientExt];
            }
        }

        if (! in_array($mimeType, $allowed)) {
            return response()->json(['success' => false, 'message' => 'نوع الملف غير مدعوم. يُسمح بـ JPEG, PNG, GIF, WebP فقط.'], 400);
        }

        // Derive extension from validated MIME — never trust the client-supplied filename
        $mimeToExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        $ext = $mimeToExt[$mimeType];

        $dest = public_path('uploads/logos');
        if (! is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $filename = 'logo_'.uniqid().'.'.$ext;
        $file->move($dest, $filename);

        // N36: Collect old logo path, save new path to DB first, then delete old file
        $oldLogo = null;
        if ($course->logo_path) {
            $oldLogo = public_path(ltrim(str_replace('\\', '/', $course->logo_path), '/'));
        }

        $course->logo_path = 'uploads/logos/'.$filename;
        $course->save();

        // Delete old logo file only after DB write succeeded
        if ($oldLogo && is_file($oldLogo)) {
            @unlink($oldLogo);
        }

        return response()->json(['success' => true, 'logo_path' => $course->logo_path]);
    }

    /** DELETE /api/admin/courses/{id}/logo */
    public function deleteLogo($id)
    {
        $course = Course::find($id);
        if (! $course) {
            return response()->json(['success' => false, 'message' => 'الدورة غير موجودة'], 404);
        }

        if (! $course->logo_path) {
            return response()->json(['success' => false, 'message' => 'لا يوجد شعار لهذه الدورة'], 404);
        }

        // Clear DB first; delete file after
        $oldPath = public_path(ltrim(str_replace('\\', '/', $course->logo_path), '/'));
        $course->logo_path = null;
        $course->save();

        if ($oldPath && is_file($oldPath)) {
            @unlink($oldPath);
        }

        AuditLogger::log(request(), 'delete_course_logo', 'Course', $course->id, []);

        return response()->json(['success' => true]);
    }
}

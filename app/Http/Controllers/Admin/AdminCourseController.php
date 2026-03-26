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
        if (!$course) {
            return response()->json(['success' => false, 'message' => 'الدورة غير موجودة'], 404);
        }
        return response()->json(['success' => true, 'course' => $course]);
    }

    /** POST /api/admin/courses */
    public function store(Request $request)
    {
        $course = Course::create([
            'title'       => $request->input('title'),
            'description' => $request->input('description'),
            'main_points' => $request->input('main_points'),
            'category'    => $request->input('category'),
            'level'       => $request->input('level', 'Beginner'),
            'is_active'   => $request->input('is_active', 1),
        ]);

        return response()->json(['success' => true, 'message' => 'تم إنشاء الكورس بنجاح', 'course' => $course], 201);
    }

    /** PUT /api/admin/courses/{id} */
    public function update(Request $request, $id)
    {
        $course = Course::find($id);
        if (!$course) {
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
        if (!$course) {
            return response()->json(['success' => false, 'message' => 'الدورة غير موجودة'], 404);
        }

        $course->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف الكورس بنجاح']);
    }

    /** GET /api/admin/courses/{courseId}/lessons */
    public function lessons($courseId)
    {
        $lessons = Lesson::where('course_id', $courseId)
            ->select('id', 'title', 'description', 'sort_order', 'resources_code', 'video_data', 'video_mime', 'views', 'created_at', 'updated_at')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function ($l) {
                $arr = $l->toArray();
                $arr['video_url'] = url('/api/stream-video?lesson_id=' . $l->id);
                return $arr;
            });

        return response()->json(['success' => true, 'lessons' => $lessons]);
    }

    /** POST /api/admin/lessons - upload lesson video */
    public function storeLesson(Request $request)
    {
        $courseId   = $request->input('course_id');
        $title      = $request->input('title');
        $description= $request->input('description', '');
        $sortOrder  = (int) $request->input('sort_order', 0);
        $resources  = $request->input('resources_code', '');

        if (!$request->hasFile('video')) {
            return response()->json(['success' => false, 'message' => 'No video file provided'], 400);
        }

        $file     = $request->file('video');
        $mimeType = $file->getMimeType();

        // Store file in original project videos dir for compatibility
        $course = Course::find($courseId);
        $catDir = $course ? $course->category : 'misc';
        $dest   = base_path('../programming-academy/videos/' . $catDir . '/' . $courseId);
        if (!is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $filename = 'lesson_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($dest, $filename);

        $relativePath = 'videos/' . $catDir . '/' . $courseId . '/' . $filename;

        $lesson = Lesson::create([
            'course_id'      => $courseId,
            'title'          => $title,
            'description'    => $description,
            'sort_order'     => $sortOrder,
            'video_data'     => $relativePath,
            'video_mime'     => $mimeType,
            'resources_code' => $resources,
        ]);

        return response()->json(['success' => true, 'message' => 'تم رفع الدرس بنجاح', 'lesson' => $lesson], 201);
    }

    /** PUT /api/admin/lessons/{id} */
    public function updateLesson(Request $request, $id)
    {
        $lesson = Lesson::find($id);
        if (!$lesson) {
            return response()->json(['success' => false, 'message' => 'الدرس غير موجود'], 404);
        }

        $lesson->fill($request->only(['title', 'description', 'sort_order', 'resources_code']));
        $lesson->save();

        return response()->json(['success' => true, 'message' => 'تم تحديث الدرس بنجاح']);
    }

    /** DELETE /api/admin/lessons/{id} */
    public function destroyLesson($id)
    {
        $lesson = Lesson::find($id);
        if (!$lesson) {
            return response()->json(['success' => false, 'message' => 'الدرس غير موجود'], 404);
        }

        $lesson->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف الدرس بنجاح']);
    }

    /** POST /api/admin/lessons/{id}/reorder */
    public function reorderLesson(Request $request, $id)
    {
        $lesson = Lesson::find($id);
        if (!$lesson) {
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
        if (!$course) {
            return response()->json(['success' => false, 'message' => 'الدورة غير موجودة'], 404);
        }

        if (!$request->hasFile('logo')) {
            return response()->json(['success' => false, 'message' => 'No file provided'], 400);
        }

        $file = $request->file('logo');
        $dest = base_path('../programming-academy/uploads/logos');
        if (!is_dir($dest)) mkdir($dest, 0755, true);

        $filename = 'logo_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move($dest, $filename);

        $course->logo_path = 'uploads/logos/' . $filename;
        $course->save();

        return response()->json(['success' => true, 'logo_path' => $course->logo_path]);
    }
}

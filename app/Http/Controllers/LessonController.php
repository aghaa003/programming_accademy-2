<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\UserCourseProgress;
use App\Models\UserLessonProgress;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    /** GET /api/lessons?course_id=X */
    public function index(Request $request)
    {
        $userId   = auth()->id();
        $courseId = $request->query('course_id');

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        if (!$courseId) {
            return response()->json(['success' => false, 'message' => 'Missing course_id'], 400);
        }

        $course = Course::where('id', $courseId)->where('is_active', 1)->first();
        if (!$course) {
            return response()->json(['success' => false, 'message' => 'Course not found'], 404);
        }

        $points = array_values(array_filter(array_map('trim', explode("\n", $course->main_points ?? ''))));
        $courseData = [
            'id'          => $course->id,
            'title'       => $course->title,
            'description' => $course->description,
            'category'    => $course->category,
            'level'       => $course->level,
            'logo_path'   => $course->logo_path,
            'main_points' => $points,
        ];

        $lessons = Lesson::where('course_id', $courseId)
            ->select('id', 'title', 'description', 'sort_order', 'resources_code', 'created_at', 'updated_at')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // Fetch user progress
        $lessonIds = $lessons->pluck('id');
        $progressMap = UserLessonProgress::where('user_id', $userId)
            ->whereIn('lesson_id', $lessonIds)
            ->get()
            ->keyBy('lesson_id');

        $completedCount = 0;
        $enriched = $lessons->map(function ($lesson) use ($progressMap, &$completedCount) {
            $prog = $progressMap->get($lesson->id);
            $completed    = $prog && !is_null($prog->completed_at);
            $lastPosition = $prog ? (int) $prog->last_position : 0;

            if ($completed) $completedCount++;

            $version = $lesson->updated_at ? $lesson->updated_at->timestamp : time();

            return [
                'id'            => $lesson->id,
                'title'         => $lesson->title,
                'description'   => $lesson->description,
                'sort_order'    => $lesson->sort_order,
                'resources_code'=> $lesson->resources_code,
                'created_at'    => $lesson->created_at,
                'updated_at'    => $lesson->updated_at,
                'completed'     => $completed,
                'last_position' => $lastPosition,
                'video_url'     => url('/api/stream-video?lesson_id=' . $lesson->id . '&v=' . $version),
            ];
        });

        $totalLessons = $lessons->count();
        $percentage   = $totalLessons > 0 ? round(($completedCount / $totalLessons) * 100) : 0;

        // Get course progress record for last_lesson_id and started_at
        $courseProgress = UserCourseProgress::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();

        return response()->json([
            'success'  => true,
            'course'   => $courseData,
            'lessons'  => $enriched,
            'progress' => [
                'completed_lessons' => $completedCount,
                'total_lessons'     => $totalLessons,
                'percentage'        => $percentage,
                'last_lesson_id'    => $courseProgress->last_lesson_id ?? null,
                'started_at'        => $courseProgress->started_at ?? null,
            ],
        ]);
    }
}

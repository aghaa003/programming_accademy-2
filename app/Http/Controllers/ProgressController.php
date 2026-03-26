<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\UserLessonProgress;
use App\Models\UserCourseProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    /** POST /api/progress */
    public function update(Request $request)
    {
        $userId   = $request->session()->get('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $lessonId = $request->input('lesson_id');
        $action   = $request->input('action');
        $position = (int) $request->input('position', 0);

        if (!$lessonId || !$action) {
            return response()->json(['success' => false, 'message' => 'Missing required parameters'], 400);
        }

        $lesson = Lesson::find($lessonId);
        if (!$lesson) {
            return response()->json(['success' => false, 'message' => 'Lesson not found'], 404);
        }

        $courseId = $lesson->course_id;
        $message  = '';

        DB::beginTransaction();
        try {
            if ($action === 'update_position') {
                DB::statement('
                    INSERT INTO user_lesson_progress (user_id, lesson_id, last_position, updated_at)
                    VALUES (?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE last_position = VALUES(last_position), updated_at = NOW()
                ', [$userId, $lessonId, $position]);

                // Only update course row if user has at least one completed lesson
                $completedCount = UserLessonProgress::where('user_id', $userId)
                    ->whereNotNull('completed_at')
                    ->whereHas('lesson', fn($q) => $q->where('course_id', $courseId))
                    ->count();

                if ($completedCount > 0) {
                    DB::table('user_course_progress')
                        ->where('user_id', $userId)
                        ->where('course_id', $courseId)
                        ->update(['last_lesson_id' => $lessonId, 'last_accessed' => now()]);
                }
                $message = 'Position saved';

            } elseif ($action === 'mark_complete') {
                DB::statement('
                    INSERT INTO user_lesson_progress (user_id, lesson_id, completed_at, last_position, updated_at)
                    VALUES (?, ?, NOW(), ?, NOW())
                    ON DUPLICATE KEY UPDATE completed_at = NOW(), last_position = VALUES(last_position), updated_at = NOW()
                ', [$userId, $lessonId, $position]);
                $message = 'Lesson marked as complete';

            } elseif ($action === 'mark_incomplete') {
                DB::table('user_lesson_progress')
                    ->where('user_id', $userId)
                    ->where('lesson_id', $lessonId)
                    ->whereNotNull('completed_at')
                    ->update(['completed_at' => null, 'updated_at' => now()]);
                $message = 'Lesson marked as incomplete';

            } else {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Invalid action'], 400);
            }

            // Recalculate course percentage
            $totalLessons = Lesson::where('course_id', $courseId)->count();
            $completedLessons = UserLessonProgress::where('user_id', $userId)
                ->whereNotNull('completed_at')
                ->whereHas('lesson', fn($q) => $q->where('course_id', $courseId))
                ->count();

            $percentage = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

            if ($completedLessons > 0) {
                // Determine last lesson id
                $lastLessonId = $lessonId;
                if ($action === 'mark_incomplete') {
                    $lastCompleted = UserLessonProgress::where('user_id', $userId)
                        ->whereNotNull('completed_at')
                        ->whereHas('lesson', fn($q) => $q->where('course_id', $courseId))
                        ->orderBy('completed_at', 'desc')
                        ->first();
                    $lastLessonId = $lastCompleted ? $lastCompleted->lesson_id : $lessonId;
                }

                DB::statement('
                    INSERT INTO user_course_progress (user_id, course_id, percentage_completed, last_lesson_id, last_accessed, started_at)
                    VALUES (?, ?, ?, ?, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE
                        percentage_completed = VALUES(percentage_completed),
                        last_lesson_id = VALUES(last_lesson_id),
                        last_accessed = NOW()
                ', [$userId, $courseId, $percentage, $lastLessonId]);
            } else {
                DB::table('user_course_progress')
                    ->where('user_id', $userId)
                    ->where('course_id', $courseId)
                    ->delete();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'progress' => [
                    'completed_lessons' => $completedLessons,
                    'total_lessons'     => $totalLessons,
                    'percentage'        => $percentage,
                ],
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Database error'], 500);
        }
    }

    /** GET /api/user-progress?user_id=X (admin or self) */
    public function userProgress(Request $request)
    {
        $sessionUserId = $request->session()->get('user_id');
        if (!$sessionUserId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $targetUserId = $request->query('user_id', $sessionUserId);
        $roles = $request->session()->get('roles', []);
        if ($targetUserId != $sessionUserId && !in_array('admin', $roles)) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $courseProgress = UserCourseProgress::with('course')
            ->where('user_id', $targetUserId)
            ->get();

        return response()->json(['success' => true, 'progress' => $courseProgress]);
    }
}

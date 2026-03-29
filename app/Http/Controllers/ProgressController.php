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
        $userId   = auth()->id();
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
                DB::table('user_lesson_progress')->upsert(
                    [['user_id' => $userId, 'lesson_id' => $lessonId, 'last_position' => $position, 'updated_at' => now()]],
                    ['user_id', 'lesson_id'],
                    ['last_position', 'updated_at']
                );

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
                DB::table('user_lesson_progress')->upsert(
                    [['user_id' => $userId, 'lesson_id' => $lessonId, 'completed_at' => now(), 'last_position' => $position, 'updated_at' => now()]],
                    ['user_id', 'lesson_id'],
                    ['completed_at', 'last_position', 'updated_at']
                );
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

                DB::table('user_course_progress')->upsert(
                    [['user_id' => $userId, 'course_id' => $courseId, 'percentage_completed' => $percentage, 'last_lesson_id' => $lastLessonId, 'last_accessed' => now(), 'started_at' => now()]],
                    ['user_id', 'course_id'],
                    ['percentage_completed', 'last_lesson_id', 'last_accessed']
                    // NOTE: started_at is intentionally NOT in the update columns — keeps original enrollment date on conflict
                );
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
        $sessionUserId = auth()->id();
        if (!$sessionUserId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $targetUserId = $request->query('user_id', $sessionUserId);
        $isAdmin = auth()->user()?->roles->contains('name', 'admin') ?? false;
        if ($targetUserId != $sessionUserId && ! $isAdmin) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $courseProgress = UserCourseProgress::with('course')
            ->where('user_id', $targetUserId)
            ->get();

        return response()->json(['success' => true, 'progress' => $courseProgress]);
    }
}

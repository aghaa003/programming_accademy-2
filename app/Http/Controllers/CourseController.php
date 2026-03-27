<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\UserCourseProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /** GET /api/courses?category=X */
    public function index(Request $request)
    {
        $category = $request->query('category');

        $query = Course::withCount('lessons')
            ->where('is_active', 1)
            ->orderBy('created_at', 'desc');

        if ($category) {
            $query->where('category', $category);
        }

        $courses = $query->get()->map(function ($course) {
            // Parse main_points into array
            $points = array_values(array_filter(array_map('trim', explode("\n", trim($course->main_points ?? '')))));

            // Normalize logo path
            $logoPath = null;
            if (! empty($course->logo_path)) {
                $lp = ltrim(str_replace('\\', '/', $course->logo_path), '/');
                if (file_exists(public_path($lp))) {
                    $logoPath = '/'.$lp;
                }
            }

            return [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'category' => $course->category,
                'main_points' => $points,
                'logo_path' => $logoPath,
                'created_at' => $course->created_at,
                'level' => $course->level,
                'lesson_count' => $course->lessons_count,
                'icon_class' => $this->iconClass($course->title),
                'color_class' => $this->colorClass($course->title),
            ];
        });

        return response()->json(['success' => true, 'courses' => $courses]);
    }

    /** GET /api/user-courses  - enrolled courses for logged in user */
    public function userCourses(Request $request)
    {
        $userId = $request->session()->get('user_id');
        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        // Match old PHP: INNER JOIN user_course_progress, LEFT JOIN lessons + user_lesson_progress
        // HAVING COUNT(ulp.lesson_id) > 0 — only show courses with at least one completed lesson
        $rows = DB::select('
            SELECT
                c.id,
                c.title,
                c.description,
                c.category,
                c.level,
                c.logo_path,
                c.main_points,
                COALESCE(ucp.percentage_completed, 0) as percentage_completed,
                ucp.last_lesson_id,
                ucp.started_at,
                ucp.last_accessed,
                COUNT(l.id) as total_lessons,
                COUNT(ulp.lesson_id) as completed_lessons
            FROM courses c
            INNER JOIN user_course_progress ucp ON c.id = ucp.course_id AND ucp.user_id = ?
            LEFT JOIN lessons l ON c.id = l.course_id
            LEFT JOIN user_lesson_progress ulp ON l.id = ulp.lesson_id AND ulp.user_id = ?
            GROUP BY c.id, c.title, c.description, c.category, c.level, c.logo_path, c.main_points,
                     ucp.percentage_completed, ucp.last_lesson_id, ucp.started_at, ucp.last_accessed
            HAVING COUNT(ulp.lesson_id) > 0
            ORDER BY ucp.last_accessed DESC, c.created_at DESC
        ', [$userId, $userId]);

        $courses = array_map(function ($row) {
            $row = (array) $row;

            // Parse main_points
            $points = array_values(array_filter(array_map('trim', explode("\n", $row['main_points'] ?? ''))));
            $row['main_points'] = $points;

            // Add icon and color classes
            $row['icon_class'] = $this->iconClass($row['title']);
            $row['color_class'] = $this->colorClass($row['title']);

            // Recalculate percentage if stored value is 0 but there are completed lessons
            if ($row['percentage_completed'] == 0 && $row['total_lessons'] > 0) {
                $row['percentage_completed'] = round(($row['completed_lessons'] / $row['total_lessons']) * 100);
            }
            $row['percentage_completed'] = (int) $row['percentage_completed'];
            $row['total_lessons'] = (int) $row['total_lessons'];
            $row['completed_lessons'] = (int) $row['completed_lessons'];

            // Normalize logo path
            if (! empty($row['logo_path'])) {
                $lp = ltrim(str_replace('\\', '/', $row['logo_path']), '/');
                $row['logo_path'] = file_exists(public_path($lp)) ? '/'.$lp : null;
            }

            // Get last lesson title
            $row['last_lesson_title'] = null;
            if ($row['last_lesson_id']) {
                $lesson = Lesson::find($row['last_lesson_id']);
                $row['last_lesson_title'] = $lesson->title ?? null;
            }

            return $row;
        }, $rows);

        return response()->json(['success' => true, 'courses' => $courses]);
    }

    /** DELETE /api/course-progress/{courseId} */
    public function deleteCourseProgress(Request $request, $courseId)
    {
        $userId = $request->session()->get('user_id');
        if (! $userId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        DB::transaction(function () use ($userId, $courseId) {
            // Delete lesson-level progress for all lessons in this course
            DB::table('user_lesson_progress')
                ->whereIn('lesson_id', function ($q) use ($courseId) {
                    $q->select('id')->from('lessons')->where('course_id', $courseId);
                })
                ->where('user_id', $userId)
                ->delete();

            // Delete course-level progress
            UserCourseProgress::where('user_id', $userId)->where('course_id', $courseId)->delete();
        });

        return response()->json(['success' => true, 'message' => 'تم حذف تقدم الكورس.']);
    }

    private function iconClass(string $title): string
    {
        $t = strtolower($title);
        if (str_contains($t, 'javascript') || str_contains($t, 'js')) {
            return 'fab fa-js-square';
        }
        if (str_contains($t, 'python')) {
            return 'fab fa-python';
        }
        if (str_contains($t, 'php')) {
            return 'fab fa-php';
        }
        if (str_contains($t, 'html')) {
            return 'fab fa-html5';
        }
        if (str_contains($t, 'css')) {
            return 'fab fa-css3-alt';
        }
        if (str_contains($t, 'react')) {
            return 'fab fa-react';
        }
        if (str_contains($t, 'c++')) {
            return 'fas fa-code';
        }
        if (str_contains($t, 'java')) {
            return 'fab fa-java';
        }
        if (str_contains($t, 'c#') || str_contains($t, 'csharp')) {
            return 'fas fa-code';
        }
        if (str_contains($t, 'sql')) {
            return 'fas fa-database';
        }

        return 'fas fa-code';
    }

    private function colorClass(string $title): string
    {
        $t = strtolower($title);
        if (str_contains($t, 'c++')) {
            return 'cpp-icon';
        }
        if (str_contains($t, 'python')) {
            return 'python-icon';
        }
        if (str_contains($t, 'php')) {
            return 'php-icon';
        }
        if (str_contains($t, 'javascript') || str_contains($t, 'js')) {
            return 'js-icon';
        }
        if (str_contains($t, 'html')) {
            return 'web-icon';
        }
        if (str_contains($t, 'css')) {
            return 'web-icon';
        }
        if (str_contains($t, 'react')) {
            return 'react-icon';
        }
        if (str_contains($t, 'java')) {
            return 'java-icon';
        }
        if (str_contains($t, 'c#') || str_contains($t, 'csharp')) {
            return 'csharp-icon';
        }
        if (str_contains($t, 'sql')) {
            return 'sql-icon';
        }

        return 'default-icon';
    }
}

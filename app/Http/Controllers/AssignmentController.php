<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\UserAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    /** GET /api/assignments?course=X */
    public function index(Request $request)
    {
        $userId   = $request->session()->get('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $courseId = $request->query('course');
        if (!$courseId) {
            return response()->json(['success' => false, 'message' => 'Course is required'], 400);
        }

        $assignments = DB::table('assignments as a')
            ->select(
                'a.id', 'a.question', 'a.difficulty',
                DB::raw('COALESCE(ua.is_completed, 0) as completed'),
                'ua.score', 'ua.status', 'ua.completed_at'
            )
            ->leftJoin('user_assignments as ua', fn($j) =>
                $j->on('ua.assignment_id', '=', 'a.id')->where('ua.user_id', '=', $userId)
            )
            ->where('a.course_id', $courseId)
            ->orderBy('a.assignment_order')
            ->get();

        return response()->json(['success' => true, 'assignments' => $assignments]);
    }

    /** GET /api/courses-with-assignments?category=X */
    public function coursesWithAssignments(Request $request)
    {
        $category = $request->query('category');
        if (!$category) {
            return response()->json(['success' => false, 'message' => 'Category is required'], 400);
        }

        $userId = $request->session()->get('user_id');

        $query = DB::table('courses as c')
            ->select(
                'c.id', 'c.title', 'c.description', 'c.category', 'c.logo_path',
                DB::raw('COUNT(DISTINCT a.id) as assignment_count')
            )
            ->join('assignments as a', 'c.id', '=', 'a.course_id')
            ->where('c.category', $category)
            ->where('c.is_active', 1)
            ->groupBy('c.id', 'c.title', 'c.description', 'c.category', 'c.logo_path')
            ->orderBy('c.title');

        $courses = $query->get();

        return response()->json(['success' => true, 'courses' => $courses]);
    }

    /** POST /api/assignments/submit */
    public function submit(Request $request)
    {
        $userId = $request->session()->get('user_id');
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $assignmentId = $request->input('assignment_id');
        $solution     = $request->input('solution');

        if (!$assignmentId || !$solution) {
            return response()->json(['success' => false, 'message' => 'Assignment ID and solution are required'], 400);
        }

        DB::table('user_assignments')
            ->updateOrInsert(
                ['user_id' => $userId, 'assignment_id' => $assignmentId],
                ['solution' => $solution, 'submitted_at' => now()]
            );

        // Simulate scoring
        $score     = rand(70, 100);
        $threshold = 70;
        $completed = $score >= $threshold;

        DB::table('user_assignments')
            ->where('user_id', $userId)
            ->where('assignment_id', $assignmentId)
            ->update([
                'score'        => $score,
                'status'       => 'graded',
                'is_completed' => $completed ? 1 : 0,
                'completed_at' => $completed ? now() : null,
            ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تقديم التكليف بنجاح',
            'score'   => $score,
        ]);
    }
}

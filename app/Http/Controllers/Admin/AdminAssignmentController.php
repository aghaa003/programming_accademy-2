<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\UserAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAssignmentController extends Controller
{
    /** GET /api/admin/assignments */
    public function index()
    {
        $assignments = DB::table('assignments as a')
            ->select(
                'a.*',
                'c.title as course_title',
                'c.category',
                DB::raw('COUNT(ua.id) as submission_count')
            )
            ->leftJoin('courses as c', 'a.course_id', '=', 'c.id')
            ->leftJoin('user_assignments as ua', 'a.id', '=', 'ua.assignment_id')
            ->groupBy('a.id', 'c.title', 'c.category')
            ->orderBy('a.id', 'desc')
            ->get();

        return response()->json(['success' => true, 'assignments' => $assignments]);
    }

    /** POST /api/admin/assignments */
    public function store(Request $request)
    {
        $assignment = Assignment::create([
            'course_id'        => $request->input('course_id'),
            'question'         => $request->input('question'),
            'assignment_order' => (int) $request->input('assignment_order', 1),
            'difficulty'       => (int) $request->input('difficulty', 1),
        ]);
        return response()->json(['success' => true, 'message' => 'تم إضافة التكليف بنجاح', 'assignment' => $assignment], 201);
    }

    /** GET /api/admin/assignments/{id} */
    public function show($id)
    {
        $assignment = Assignment::with('course')->find($id);
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'التكليف غير موجود'], 404);
        }
        return response()->json(['success' => true, 'assignment' => $assignment]);
    }

    /** PUT /api/admin/assignments/{id} */
    public function update(Request $request, $id)
    {
        $assignment = Assignment::find($id);
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'التكليف غير موجود'], 404);
        }
        $assignment->fill($request->only(['course_id', 'question', 'assignment_order', 'difficulty']));
        $assignment->save();
        return response()->json(['success' => true, 'message' => 'تم تحديث التكليف', 'assignment' => $assignment]);
    }

    /** DELETE /api/admin/assignments/{id} */
    public function destroy($id)
    {
        $assignment = Assignment::find($id);
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'التكليف غير موجود'], 404);
        }
        // Delete submissions first to avoid FK constraint violation
        DB::table('user_assignments')->where('assignment_id', $id)->delete();
        $assignment->delete();
        return response()->json(['success' => true, 'message' => 'تم حذف التكليف بنجاح']);
    }

    /** PATCH /api/admin/assignments/{id}/toggle */
    public function toggle($id)
    {
        $assignment = Assignment::find($id);
        if (!$assignment) {
            return response()->json(['success' => false, 'message' => 'التكليف غير موجود'], 404);
        }
        $assignment->is_active = !$assignment->is_active;
        $assignment->save();
        $status = $assignment->is_active ? 'مفعّل' : 'معطّل';
        return response()->json(['success' => true, 'is_active' => $assignment->is_active, 'message' => "تم {$status} التكليف"]);
    }

    /** GET /api/admin/assignments/{id}/submissions */
    public function submissions($id)
    {
        $submissions = DB::table('user_assignments as ua')
            ->join('users as u', 'ua.user_id', '=', 'u.id')
            ->select('ua.*', 'u.username', 'u.firstName', 'u.lastName', 'u.email')
            ->where('ua.assignment_id', $id)
            ->orderBy('ua.submitted_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'submissions' => $submissions]);
    }

    /** POST /api/admin/assignments/{submissionId}/grade */
    public function grade(Request $request, $submissionId)
    {
        $score = (int) $request->input('score');

        DB::table('user_assignments')
            ->where('id', $submissionId)
            ->update([
                'score'        => $score,
                'status'       => 'graded',
                'is_completed' => $score >= 70 ? 1 : 0,
                'completed_at' => $score >= 70 ? now() : null,
            ]);

        return response()->json(['success' => true, 'message' => 'تم تصحيح التكليف']);
    }
}

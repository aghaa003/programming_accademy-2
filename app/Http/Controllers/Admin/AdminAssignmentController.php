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
        $courseId  = (int) $request->input('course_id');
        $question  = trim($request->input('question', ''));
        $order     = max(1, (int) $request->input('assignment_order', 1));
        $difficulty = max(1, min(5, (int) $request->input('difficulty', 1)));

        if (!$courseId || !\DB::table('courses')->where('id', $courseId)->where('is_active', 1)->exists()) {
            return response()->json(['success' => false, 'message' => 'الكورس غير موجود أو غير مفعّل.'], 422);
        }
        if (empty($question)) {
            return response()->json(['success' => false, 'message' => 'نص السؤال مطلوب.'], 422);
        }

        $assignment = Assignment::create([
            'course_id'        => $courseId,
            'question'         => $question,
            'assignment_order' => $order,
            'difficulty'       => $difficulty,
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

        // Validate course_id if it is being changed
        if ($request->has('course_id')) {
            $courseId = (int) $request->input('course_id');
            if (!$courseId || !\DB::table('courses')->where('id', $courseId)->where('is_active', 1)->exists()) {
                return response()->json(['success' => false, 'message' => 'الكورس غير موجود أو غير مفعّل.'], 422);
            }
        }

        $data = $request->only(['course_id', 'question', 'assignment_order', 'difficulty']);
        if (isset($data['difficulty'])) {
            $data['difficulty'] = max(1, min(5, (int) $data['difficulty']));
        }
        if (isset($data['assignment_order'])) {
            $data['assignment_order'] = max(1, (int) $data['assignment_order']);
        }
        $assignment->fill($data);
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
        DB::transaction(function () use ($id, $assignment) {
            DB::table('user_assignments')->where('assignment_id', $id)->delete();
            $assignment->delete();
        });
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

        if ($score < 0 || $score > 100) {
            return response()->json(['success' => false, 'message' => 'الدرجة يجب أن تكون بين 0 و 100.'], 422);
        }

        $affected = DB::table('user_assignments')
            ->where('id', $submissionId)
            ->update([
                'score'        => $score,
                'status'       => 'graded',
                'is_completed' => $score >= 70 ? 1 : 0,
                'completed_at' => $score >= 70 ? now() : null,
            ]);

        if ($affected === 0) {
            return response()->json(['success' => false, 'message' => 'التسليم غير موجود.'], 404);
        }

        return response()->json(['success' => true, 'message' => 'تم تصحيح التكليف']);
    }
}

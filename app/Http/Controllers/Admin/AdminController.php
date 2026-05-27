<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /** GET /api/admin/stats - Overview stats for admin dashboard */
    public function stats()
    {
        $stats = [
            'total_users'       => DB::table('users')->count(),
            'total_courses'     => DB::table('courses')->where('is_active', 1)->count(),
            'total_lessons'     => DB::table('lessons')->count(),
            'total_challenges'  => DB::table('challenges')->where('is_active', 1)->count(),
            'total_assignments' => DB::table('assignments')->count(),
            'total_platforms'   => DB::table('platforms')->where('is_active', 1)->count(),
            'enrollments'       => DB::table('user_course_progress')->count(),
            'submissions'       => DB::table('user_assignments')->count(),
        ];

        return response()->json(['success' => true, 'stats' => $stats]);
    }

    /** GET /api/admin/audit-logs - Paginated admin audit log */
    public function auditLogs(Request $request)
    {
        $limit  = min((int) $request->query('limit', 50), 200);
        $offset = max((int) $request->query('offset', 0), 0);
        $total  = DB::table('admin_audit_logs')->count();

        $logs = DB::table('admin_audit_logs as al')
            ->leftJoin('users as u', 'u.id', '=', 'al.admin_id')
            ->select(
                'al.id', 'al.action', 'al.target_type', 'al.target_id',
                'al.payload', 'al.ip', 'al.created_at',
                DB::raw("CONCAT(COALESCE(u.firstName,''), ' ', COALESCE(u.lastName,'')) as admin_name"),
                'u.username as admin_username'
            )
            ->orderByDesc('al.created_at')
            ->skip($offset)->take($limit)
            ->get();

        return response()->json(['success' => true, 'logs' => $logs, 'total' => $total]);
    }

    /** GET /api/admin/engagements - User engagement metrics */
    public function engagements(Request $request)
    {
        $limit  = min((int) $request->query('limit', 50), 200);
        $offset = max((int) $request->query('offset', 0), 0);

        $engagements = DB::table('users as u')
            ->select(
                'u.id',
                'u.username',
                'u.email',
                DB::raw('COUNT(DISTINCT ucp.course_id) as courses_enrolled'),
                DB::raw('COUNT(DISTINCT ulp.lesson_id) as lessons_completed'),
                DB::raw('COUNT(DISTINCT ca.id) as challenges_attempted'),
                DB::raw('COUNT(DISTINCT ua.id) as assignments_completed'),
                'u.created_at'
            )
            ->leftJoin('user_course_progress as ucp', 'u.id', '=', 'ucp.user_id')
            ->leftJoin('user_lesson_progress as ulp', 'u.id', '=', 'ulp.user_id')
            ->leftJoin('user_challenges as ca', 'u.id', '=', 'ca.user_id')
            ->leftJoin('user_assignments as ua', 'u.id', '=', 'ua.user_id')
            ->groupBy('u.id')
            ->orderByDesc('u.created_at')
            ->skip($offset)->take($limit)
            ->get();

        $total = DB::table('users')->count();

        return response()->json(['success' => true, 'engagements' => $engagements, 'total' => $total]);
    }

    /** GET /api/admin/comments - All comments across platform */
    public function comments(Request $request)
    {
        $limit  = min((int) $request->query('limit', 50), 200);
        $offset = max((int) $request->query('offset', 0), 0);

        $comments = DB::table('lesson_comments as lc')
            ->select(
                'lc.id',
                'lc.lesson_id',
                'lc.user_id',
                'lc.comment_text as text',
                'u.username',
                'u.avatar_path',
                'lc.created_at',
                DB::raw("'lesson' as type")
            )
            ->join('users as u', 'u.id', '=', 'lc.user_id')
            ->orderByDesc('lc.created_at')
            ->skip($offset)->take($limit)
            ->get()
            ->map(function ($comment) {
                $comment->avatar_url = ! empty($comment->avatar_path) ? asset($comment->avatar_path) : null;
                unset($comment->avatar_path);
                return $comment;
            });

        $total = DB::table('lesson_comments')->count();

        return response()->json(['success' => true, 'comments' => $comments, 'total' => $total]);
    }

    /** GET /api/admin/reviews - All course reviews */
    public function reviews(Request $request)
    {
        $limit  = min((int) $request->query('limit', 50), 200);
        $offset = max((int) $request->query('offset', 0), 0);

        $reviews = DB::table('course_reviews as cr')
            ->select(
                'cr.id',
                'cr.course_id',
                'cr.user_id',
                'cr.rating',
                'cr.comment',
                'u.username',
                'u.avatar_path',
                'c.title as course_title',
                'cr.created_at'
            )
            ->join('users as u', 'u.id', '=', 'cr.user_id')
            ->join('courses as c', 'c.id', '=', 'cr.course_id')
            ->orderByDesc('cr.created_at')
            ->skip($offset)->take($limit)
            ->get()
            ->map(function ($review) {
                $review->avatar_url = ! empty($review->avatar_path) ? asset($review->avatar_path) : null;
                unset($review->avatar_path);
                return $review;
            });

        $total = DB::table('course_reviews')->count();

        return response()->json(['success' => true, 'reviews' => $reviews, 'total' => $total]);
    }

    /** POST /api/admin/reviews/{id}/approve */
    public function approveReview($reviewId, Request $request)
    {
        $updated = DB::table('course_reviews')
            ->where('id', $reviewId)
            ->update(['is_approved' => 1]);

        if (!$updated) {
            return response()->json(['error' => 'Review not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'تم الموافقة على التقييم']);
    }

    /** POST /api/admin/reviews/{id}/reject */
    public function rejectReview($reviewId, Request $request)
    {
        $deleted = DB::table('course_reviews')
            ->where('id', $reviewId)
            ->delete();

        if (!$deleted) {
            return response()->json(['error' => 'Review not found'], 404);
        }

        return response()->json(['success' => true, 'message' => 'تم رفض التقييم']);
    }
}

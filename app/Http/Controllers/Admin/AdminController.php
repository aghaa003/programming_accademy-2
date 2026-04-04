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
}

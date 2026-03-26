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
}

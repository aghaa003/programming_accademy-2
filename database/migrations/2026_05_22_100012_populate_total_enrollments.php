<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $counts = DB::table('user_course_progress')
            ->select('course_id', DB::raw('count(*) as total'))
            ->groupBy('course_id')
            ->get();

        foreach ($counts as $row) {
            DB::table('courses')
                ->where('id', $row->course_id)
                ->update(['total_enrollments' => $row->total]);
        }
    }

    public function down(): void
    {
        DB::table('courses')->update(['total_enrollments' => 0]);
    }
};

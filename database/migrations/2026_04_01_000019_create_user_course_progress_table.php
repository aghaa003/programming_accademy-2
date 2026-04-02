<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_course_progress', function (Blueprint $table) {
            $table->integer('user_id');
            $table->unsignedInteger('course_id');
            $table->integer('percentage_completed')->default(0)->comment('Overall course completion 0-100');
            $table->integer('last_lesson_id')->nullable()->comment('Last watched lesson for resume');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('last_accessed')->useCurrent()->useCurrentOnUpdate();

            $table->primary(['user_id', 'course_id']);
            $table->index('course_id');
            $table->index('last_lesson_id');
            $table->index('last_accessed', 'idx_last_accessed');
            $table->index(['user_id', 'percentage_completed'], 'idx_user_completion');
            $table->index(['user_id', 'course_id', 'last_accessed'], 'idx_active_courses');

            $table->foreign('user_id', 'user_course_progress_ibfk_1')
                  ->references('id')->on('users')->onDelete('cascade');
            $table->foreign('course_id', 'user_course_progress_ibfk_2')
                  ->references('id')->on('courses')->onDelete('cascade');
            $table->foreign('last_lesson_id', 'user_course_progress_ibfk_3')
                  ->references('id')->on('lessons')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_course_progress');
    }
};

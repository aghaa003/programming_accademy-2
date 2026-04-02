<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->unsignedInteger('course_id');
            $table->text('question');
            $table->integer('difficulty')->default(1);
            $table->integer('assignment_order')->default(1);
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['course_id', 'assignment_order'], 'idx_course_assignments');
            $table->index(['difficulty', 'course_id'], 'idx_assignment_difficulty');

            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};

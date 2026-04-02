<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->unsignedInteger('course_id');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('video_path', 500)->nullable();
            $table->string('video_mime_type', 100)->nullable();
            $table->text('resources_code')->nullable()->comment('Code snippets and resources for this lesson');
            $table->integer('views')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['course_id', 'sort_order'], 'idx_course_lessons');
            $table->index('views', 'idx_lesson_views');
            $table->index(['course_id', 'created_at'], 'idx_course_active');

            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};

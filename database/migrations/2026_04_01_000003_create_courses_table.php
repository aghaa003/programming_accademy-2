<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->text('main_points')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->string('level', 50)->default('Beginner');
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['category', 'is_active', 'created_at'], 'idx_category_active');
            $table->index(['level', 'is_active'], 'idx_level_active');
            // FULLTEXT idx_course_search (title, description) - managed manually
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_lesson_progress', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('lesson_id');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->integer('last_position')->default(0)->comment('Video position in seconds');

            $table->unique(['user_id', 'lesson_id'], 'user_lesson_unique');
            $table->index('lesson_id');
            $table->index('completed_at', 'idx_completed');
            $table->index(['user_id', 'completed_at'], 'idx_user_incomplete');
            $table->index(['user_id', 'lesson_id', 'last_position'], 'idx_user_lesson_position');
            $table->index('updated_at', 'idx_updated');

            $table->foreign('user_id', 'user_lesson_progress_ibfk_1')
                  ->references('id')->on('users')->onDelete('cascade');
            $table->foreign('lesson_id', 'user_lesson_progress_ibfk_2')
                  ->references('id')->on('lessons')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_lesson_progress');
    }
};

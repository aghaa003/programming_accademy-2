<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->integer('from_user_id')->nullable();
            $table->foreign('from_user_id')->references('id')->on('users')->nullOnDelete();
            $table->string('from_user_name')->default('');
            $table->enum('type', [
                'post_like', 'post_comment', 'comment_reply',
                'lesson_like', 'lesson_comment',
                'course_like', 'course_comment', 'course_rating',
            ]);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('entity_title')->default('');
            $table->text('message');
            $table->boolean('read')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'read']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment');
            $table->timestamps();

            $table->unique(['course_id', 'user_id'], 'unique_course_review');
            $table->index(['course_id', 'rating', 'created_at'], 'idx_course_reviews_rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_reviews');
    }
};

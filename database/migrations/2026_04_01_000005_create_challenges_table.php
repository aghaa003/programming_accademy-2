<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('title', 255);
            $table->text('description');
            $table->enum('category', ['algorithms', 'data-structures', 'web', 'database']);
            $table->enum('difficulty', ['easy', 'medium', 'hard']);
            $table->integer('points')->default(0);
            $table->text('starter_code')->nullable();
            $table->string('code_language', 50)->nullable();
            $table->json('test_cases')->nullable();
            $table->text('solution_template')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['category', 'difficulty', 'is_active'], 'idx_challenge_filter');
            $table->index('points', 'idx_challenge_points');
            // FULLTEXT idx_challenge_search (title, description) - managed manually
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};

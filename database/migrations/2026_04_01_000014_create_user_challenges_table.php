<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_challenges', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('user_id');
            $table->integer('challenge_id');
            $table->integer('attempts')->default(0);
            $table->tinyInteger('completed')->default(0);
            $table->integer('best_score')->default(0);
            $table->timestamp('last_attempted')->useCurrent();

            $table->unique(['user_id', 'challenge_id'], 'unique_user_challenge');
            $table->index(['user_id', 'completed', 'best_score'], 'idx_user_challenge_complete');
            $table->index(['challenge_id', 'last_attempted'], 'idx_challenge_recent');
            $table->index(['user_id', 'best_score'], 'idx_leaderboard');

            $table->foreign('user_id', 'user_challenges_user_id_foreign')
                  ->references('id')->on('users')->onDelete('cascade');
            $table->foreign('challenge_id', 'user_challenges_challenge_id_foreign')
                  ->references('id')->on('challenges')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_challenges');
    }
};

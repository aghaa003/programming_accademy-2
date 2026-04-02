<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_attempts', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('user_id');
            $table->integer('challenge_id');
            $table->text('code');
            $table->tinyInteger('completed')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'challenge_id'], 'unique_attempt');
            $table->index('challenge_id');

            $table->foreign('user_id', 'challenge_attempts_ibfk_1')
                  ->references('id')->on('users')->onDelete('cascade');
            $table->foreign('challenge_id', 'challenge_attempts_ibfk_2')
                  ->references('id')->on('challenges')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_attempts');
    }
};

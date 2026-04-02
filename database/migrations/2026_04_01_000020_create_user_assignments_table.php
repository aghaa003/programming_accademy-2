<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_assignments', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('user_id');
            $table->integer('assignment_id');
            $table->text('solution')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->integer('score')->nullable();
            $table->enum('status', ['submitted', 'graded'])->default('submitted');
            $table->tinyInteger('is_completed')->default(0);
            $table->timestamp('completed_at')->nullable();

            $table->unique(['user_id', 'assignment_id'], 'unique_user_assignment');
            $table->index(['user_id', 'status', 'is_completed'], 'idx_user_assignment_status');
            $table->index(['assignment_id', 'score'], 'idx_assignment_score');
            $table->index(['user_id', 'submitted_at'], 'idx_submission_date');

            $table->foreign('user_id', 'user_assignments_user_id_foreign')
                  ->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assignment_id', 'user_assignments_assignment_id_foreign')
                  ->references('id')->on('assignments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_assignments');
    }
};

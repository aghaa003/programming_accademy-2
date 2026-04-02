<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // user_challenges: queried by (user_id, completed) in leaderboard, progress,
        // and by challenge_id in challenge detail pages
        Schema::table('user_challenges', function (Blueprint $table) {
            $table->index(['user_id', 'completed'], 'idx_user_challenges_user_completed');
            $table->index('challenge_id', 'idx_user_challenges_challenge_id');
        });

        // user_lesson_progress: upserted on every video position save (up to 60/min per user)
        Schema::table('user_lesson_progress', function (Blueprint $table) {
            $table->index(['user_id', 'lesson_id'], 'idx_ulp_user_lesson');
        });

        // challenges: filtered by is_active on every public listing
        Schema::table('challenges', function (Blueprint $table) {
            $table->index('is_active', 'idx_challenges_is_active');
        });

        // academy_reviews: checked per-user before insert to prevent duplicates
        Schema::table('academy_reviews', function (Blueprint $table) {
            $table->index('user_id', 'idx_reviews_user_id');
        });

        // admin_audit_logs: queried by admin_id + time range in audit log views
        Schema::table('admin_audit_logs', function (Blueprint $table) {
            $table->index(['admin_id', 'created_at'], 'idx_audit_admin_created');
        });
    }

    public function down(): void
    {
        Schema::table('user_challenges', function (Blueprint $table) {
            $table->dropIndex('idx_user_challenges_user_completed');
            $table->dropIndex('idx_user_challenges_challenge_id');
        });

        Schema::table('user_lesson_progress', function (Blueprint $table) {
            $table->dropIndex('idx_ulp_user_lesson');
        });

        Schema::table('challenges', function (Blueprint $table) {
            $table->dropIndex('idx_challenges_is_active');
        });

        Schema::table('academy_reviews', function (Blueprint $table) {
            $table->dropIndex('idx_reviews_user_id');
        });

        Schema::table('admin_audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_admin_created');
        });
    }
};

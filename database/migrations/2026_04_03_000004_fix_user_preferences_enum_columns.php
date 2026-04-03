<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * The user_preferences table was created with ENUM columns that only accepted
 * Arabic display-label values (e.g. ENUM('مبتدئ','متوسط','متقدم')).
 * After the frontend quiz was updated to send canonical English values
 * ('beginner', 'intermediate', 'advanced', 'ar', 'en'), MySQL silently
 * stores empty string for any value not in the ENUM definition.
 *
 * Fix: change preferred_level and preferred_language to VARCHAR(50) so
 * both legacy Arabic values and the new English canonical values are stored.
 * The time_commitment column already stores arbitrary Arabic text correctly
 * (it is also an enum, but the frontend still sends matching Arabic strings).
 */
return new class extends Migration
{
    public function up(): void
    {
        // SQLite (used in tests) doesn't support ENUM; guard is only needed
        // for the migration itself — we still run on SQLite via column().
        Schema::table('user_preferences', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                // MODIFY COLUMN: change ENUM → VARCHAR, preserve nullable
                DB::statement("ALTER TABLE user_preferences
                    MODIFY COLUMN preferred_level VARCHAR(50) NULL DEFAULT NULL,
                    MODIFY COLUMN preferred_language VARCHAR(10) NULL DEFAULT NULL");
            } else {
                // SQLite: no-op (tests use text columns already)
            }
        });
    }

    public function down(): void
    {
        Schema::table('user_preferences', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE user_preferences
                    MODIFY COLUMN preferred_level ENUM('مبتدئ','متوسط','متقدم') NULL DEFAULT NULL,
                    MODIFY COLUMN preferred_language ENUM('العربية','الإنجليزية','لا يهم') NULL DEFAULT NULL");
            }
        });
    }
};

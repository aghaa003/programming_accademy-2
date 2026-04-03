<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users') || ! Schema::hasColumn('users', 'avatar_data')) {
            return;
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar_data', 'avatar_mime_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->binary('avatar_data')->nullable()->after('interest');
            $table->string('avatar_mime_type', 255)->nullable()->after('avatar_data');
        });
    }
};

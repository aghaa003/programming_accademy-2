<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip if table doesn't exist (fresh install) or old column is already renamed
        if (! Schema::hasTable('lessons') || ! Schema::hasColumn('lessons', 'video_data')) {
            return;
        }
        // Rename longblob video_data → varchar video_path
        DB::statement('ALTER TABLE lessons CHANGE video_data video_path VARCHAR(500) NULL');
        // Rename video_mime → video_mime_type
        DB::statement('ALTER TABLE lessons CHANGE video_mime video_mime_type VARCHAR(100) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE lessons CHANGE video_path video_data LONGBLOB NOT NULL');
        DB::statement('ALTER TABLE lessons CHANGE video_mime_type video_mime VARCHAR(255) NOT NULL');
    }
};

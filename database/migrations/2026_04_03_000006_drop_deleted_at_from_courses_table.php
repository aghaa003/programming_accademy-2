<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Courses are now hard-deleted (DB::table delete) so the soft-delete column
        // is no longer used. Drop it to keep the schema consistent with the model.
        // Users retain their deleted_at column (soft-delete is still valuable there).
        if (Schema::hasColumn('courses', 'deleted_at')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropColumn('deleted_at');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('courses', 'deleted_at')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }
};

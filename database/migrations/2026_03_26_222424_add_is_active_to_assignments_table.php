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
        if (! Schema::hasTable('assignments') || Schema::hasColumn('assignments', 'is_active')) {
            return;
        }
        Schema::table('assignments', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('assignment_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};

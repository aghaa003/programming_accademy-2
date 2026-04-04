<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'provider')) {
                $table->string('provider', 50)->nullable()->after('avatar_path');
            }
            if (! Schema::hasColumn('users', 'provider_id')) {
                $table->string('provider_id', 255)->nullable()->after('provider');
                $table->unique(['provider', 'provider_id'], 'users_provider_provider_id_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'provider_id')) {
                $table->dropUnique('users_provider_provider_id_unique');
                $table->dropColumn('provider_id');
            }
            if (Schema::hasColumn('users', 'provider')) {
                $table->dropColumn('provider');
            }
        });
    }
};

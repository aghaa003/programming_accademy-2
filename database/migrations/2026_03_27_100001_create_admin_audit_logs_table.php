<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * M3: Admin audit log table — records every mutating admin action.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('action', 100);          // e.g. 'create_challenge', 'delete_user'
            $table->string('target_type', 80)->nullable(); // e.g. 'Challenge', 'User'
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('payload')->nullable();    // sanitised request snapshot
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
    }
};

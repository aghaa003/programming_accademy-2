<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('password_resets', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('email', 255);
            $table->string('token', 255)->unique();
            $table->dateTime('expires_at');
            $table->timestamp('created_at')->useCurrent();

            $table->index('expires_at', 'idx_reset_expiry');
            $table->index(['email', 'expires_at'], 'idx_reset_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_resets');
    }
};

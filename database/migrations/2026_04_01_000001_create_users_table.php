<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('firstName', 50);
            $table->string('lastName', 50);
            $table->string('email', 100)->unique();
            $table->string('preferred_language', 5)->default('ar');
            $table->string('phone', 25)->nullable();
            $table->string('username', 50)->unique();
            $table->string('password', 255);
            $table->string('remember_token', 100)->nullable();
            $table->string('country', 50)->nullable();
            $table->string('experience', 50)->nullable();
            $table->string('goal', 50)->nullable();
            $table->string('interest', 50)->nullable();
            $table->dateTime('joinDate')->useCurrent();
            $table->string('avatar_path', 255)->nullable();

            $table->index('joinDate', 'idx_user_join_date');
            $table->index('id', 'idx_admin_users');
            $table->index('experience', 'idx_user_experience');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

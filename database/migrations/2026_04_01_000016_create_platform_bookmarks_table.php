<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_bookmarks', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('user_id');
            $table->integer('platform_id');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'platform_id'], 'unique_user_bookmark');
            $table->index('platform_id');

            $table->foreign('user_id', 'platform_bookmarks_ibfk_1')
                  ->references('id')->on('users')->onDelete('cascade');
            $table->foreign('platform_id', 'platform_bookmarks_ibfk_2')
                  ->references('id')->on('platforms')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_bookmarks');
    }
};

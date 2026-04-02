<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academy_reviews', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->tinyInteger('rating');
            $table->text('review_text');
            $table->timestamp('created_at')->useCurrent();

            $table->unique('user_id');
            $table->index('created_at', 'idx_reviews_recent');
            $table->index(['rating', 'created_at'], 'idx_reviews_rating');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academy_reviews');
    }
};

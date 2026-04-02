<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examples', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 255);
            $table->text('description');
            $table->enum('category', ['frontend', 'backend', 'mobile', 'algorithms']);
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced']);
            $table->string('image_url', 500)->nullable();
            $table->text('code_snippet');
            $table->string('code_language', 50);
            $table->json('technologies')->nullable();
            $table->string('demo_url', 500)->nullable();
            $table->tinyInteger('requires_special_env')->default(0);
            $table->text('special_env_message')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['category', 'difficulty', 'is_active'], 'idx_example_filter');
            $table->index(['code_language', 'is_active'], 'idx_example_tech');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examples');
    }
};

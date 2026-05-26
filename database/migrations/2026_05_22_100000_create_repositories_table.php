<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repositories', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('technologies')->nullable();
            $table->string('repo_url')->nullable();
            $table->string('live_demo_url')->nullable();
            $table->string('file_url')->nullable();
            $table->json('code_files_urls')->nullable();
            $table->json('pdf_files_urls')->nullable();
            $table->string('cover_image_url')->nullable();
            $table->integer('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedInteger('likes')->default(0);
            $table->boolean('is_public')->default(true);
            $table->boolean('is_draft')->default(false);
            $table->string('source_project')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('is_public');
            $table->index('is_draft');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repositories');
    }
};

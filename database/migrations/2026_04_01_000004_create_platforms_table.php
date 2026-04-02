<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platforms', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('url', 255);
            $table->enum('category', ['global', 'arabic'])->default('global');
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->enum('language', ['english', 'arabic', 'both'])->default('english');
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->integer('user_count')->default(0);
            $table->integer('problem_count')->default(0);
            $table->json('features')->nullable();
            $table->string('logo_url', 255)->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['category', 'level', 'language', 'is_active'], 'idx_platform_filter');
            $table->index(['rating', 'user_count'], 'idx_platform_rating');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platforms');
    }
};

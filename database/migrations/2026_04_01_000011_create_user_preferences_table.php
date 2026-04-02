<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->integer('id')->autoIncrement();
            $table->integer('user_id');
            $table->enum('preferred_level', ['مبتدئ', 'متوسط', 'متقدم'])->nullable();
            $table->enum('preferred_language', ['العربية', 'الإنجليزية', 'لا يهم'])->nullable();
            $table->string('goals', 255)->nullable();
            $table->enum('time_commitment', ['يومياً', 'أسبوعياً', 'شهرياً'])->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique('user_id', 'unique_user_prefs');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('question_categories');
            $table->text('question_text');
            $table->enum('question_type', ['multiple_choice', 'true_false', 'qa']); // Çoktan seçmeli, doğru-yanlış, soru-cevap
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->string('image_path')->nullable(); // Soru için resim
            $table->json('metadata')->nullable(); // Ek özellikler için
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};

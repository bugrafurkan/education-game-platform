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
        Schema::create('question_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();  // Otomatik üretilen benzersiz kod
            $table->enum('question_type', ['multiple_choice', 'true_false', 'qa']);
            $table->foreignId('game_id')->constrained('games');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Pivot tablo: soru grupları ve sorular arasındaki ilişki
        Schema::create('question_group_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_group_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->integer('order')->nullable(); // Soruların sırasını belirler
            $table->timestamps();

            // Bir soru grubu içinde aynı soru birden fazla olamaz
            $table->unique(['question_group_id', 'question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_group_questions');
        Schema::dropIfExists('question_groups');
    }
};

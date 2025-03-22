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
        Schema::create('question_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('grade'); // Sınıf: 5, 6, 7, 8, 9, etc.
            $table->string('subject'); // Ders: Matematik, Fen, Türkçe, etc.
            $table->string('unit')->nullable(); // Ünite: Üçgenler, Bölünebilme, etc.
            $table->text('description')->nullable();
            $table->timestamps();

            // Önemli filtreleme alanları için index
            $table->index(['grade', 'subject']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_categories');
    }
};

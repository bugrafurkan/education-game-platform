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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['banner', 'video']); // Sabit banner veya video reklam
            $table->string('media_path'); // Resim/video dosya yolu
            $table->string('target_grade')->nullable(); // Hedef sınıf
            $table->string('target_subject')->nullable(); // Hedef ders
            $table->string('target_game_type')->nullable(); // Hedef oyun tipi
            $table->string('link_url')->nullable(); // Tıklama linki
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};

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
        Schema::create('exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained();
            $table->string('version');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('download_url')->nullable();
            $table->boolean('uploaded_to_fernus')->default(false);
            $table->string('fernus_url')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->json('config_snapshot')->nullable(); // Export anında oyun konfigürasyonunun anlık kopyası
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exports');
    }
};

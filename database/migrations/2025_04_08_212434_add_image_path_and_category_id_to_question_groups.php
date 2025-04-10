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
        Schema::table('question_groups', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('created_by'); // Etkinlik için resim
            $table->foreignId('category_id')->nullable()->after('game_id')->constrained('question_categories');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_groups', function (Blueprint $table) {
            $table->dropColumn('image_path');
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};

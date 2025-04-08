<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('exports', function (Blueprint $table) {
            // game_type sütununu kaldır
            $table->dropColumn('game_type');

            // yeni game_id sütununu ekle
            $table->unsignedBigInteger('game_id')->after('question_group_id');

            // ilişkisel bütünlük (isteğe bağlı)
            $table->foreign('game_id')->references('id')->on('games')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('exports', function (Blueprint $table) {
            $table->dropForeign(['game_id']);
            $table->dropColumn('game_id');
            $table->string('game_type')->nullable()->after('question_group_id');
        });
    }
};

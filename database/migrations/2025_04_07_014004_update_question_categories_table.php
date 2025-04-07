<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('question_categories', function (Blueprint $table) {
            $table->dropColumn(['grade', 'subject', 'unit', 'description']);

            $table->foreignId('grade_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('subject_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('unit_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('topic_id')->nullable()->constrained()->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('question_categories', function (Blueprint $table) {
            $table->dropForeign(['grade_id']);
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['topic_id']);

            $table->dropColumn(['grade_id', 'subject_id', 'unit_id', 'topic_id']);

            $table->string('grade')->nullable();
            $table->string('subject')->nullable();
            $table->string('unit')->nullable();
            $table->string('description')->nullable();
        });
    }
};


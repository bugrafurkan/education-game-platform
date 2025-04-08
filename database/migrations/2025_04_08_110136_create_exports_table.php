<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exports', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('question_group_id')->nullable();
            $table->string('game_type');
            $table->string('status')->default('pending');
            $table->string('output_url')->nullable();
            $table->json('config_snapshot')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();

            $table->foreign('question_group_id')->references('id')->on('question_groups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exports');
    }
};

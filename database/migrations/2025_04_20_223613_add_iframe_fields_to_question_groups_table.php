<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIframeFieldsToQuestionGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('question_groups', function (Blueprint $table) {
            $table->string('iframe_url')->nullable()->after('code');
            $table->text('iframe_code')->nullable()->after('iframe_url');
            $table->enum('iframe_status', ['pending', 'processing', 'completed', 'failed'])
                ->default('pending')
                ->after('iframe_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('question_groups', function (Blueprint $table) {
            $table->dropColumn(['iframe_url', 'iframe_code', 'iframe_status']);
        });
    }
}

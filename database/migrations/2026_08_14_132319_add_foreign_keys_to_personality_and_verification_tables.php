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
        Schema::table('personality_question_options', function (Blueprint $table) {
            $table->foreign('question_id')->references('id')->on('personality_questions')->cascadeOnDelete();
        });

        Schema::table('personality_answers', function (Blueprint $table) {
            $table->foreign('question_id')->references('id')->on('personality_questions')->cascadeOnDelete();
            $table->foreign('selected_option_id')->references('id')->on('personality_question_options')->cascadeOnDelete();
        });

        Schema::table('candidate_verifications', function (Blueprint $table) {
            $table->foreign('verification_type_id')->references('id')->on('verification_types')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidate_verifications', function (Blueprint $table) {
            $table->dropForeign(['verification_type_id']);
        });

        Schema::table('personality_answers', function (Blueprint $table) {
            $table->dropForeign(['selected_option_id']);
            $table->dropForeign(['question_id']);
        });

        Schema::table('personality_question_options', function (Blueprint $table) {
            $table->dropForeign(['question_id']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personality_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('question_id');
            $table->unsignedBigInteger('selected_option_id');
            $table->timestamps();

            $table->index('candidate_id');
            $table->index('question_id');
            $table->index('selected_option_id');
            $table->unique(['candidate_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personality_answers');
    }
};

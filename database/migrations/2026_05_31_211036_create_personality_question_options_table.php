<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personality_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('personality_questions')->cascadeOnDelete();
            $table->string('option_text');
            $table->string('signal_key');
            $table->integer('signal_value')->default(1);
            $table->timestamps();

            $table->index('question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personality_question_options');
    }
};

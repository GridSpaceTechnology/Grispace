<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personality_questions', function (Blueprint $table) {
            $table->id();
            $table->string('category');
            $table->text('question_text');
            $table->string('question_type')->default('single_choice');
            $table->boolean('active_status')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->index('category');
            $table->index('active_status');
            $table->index('display_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personality_questions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trust_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedSmallInteger('score')->default(0);
            $table->string('level')->default('Beginner');
            $table->timestamps();

            $table->unique('candidate_id');
            $table->index('score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trust_scores');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_match_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('job_id')->constrained('job_listings')->cascadeOnDelete();
            $table->integer('skills_fit_score')->default(0);
            $table->integer('personality_fit_score')->default(0);
            $table->integer('culture_fit_score')->default(0);
            $table->integer('temperament_fit_score')->default(0);
            $table->integer('overall_match_score')->default(0);
            $table->timestamps();

            $table->index('candidate_id');
            $table->index('job_id');
            $table->index('overall_match_score');
            $table->unique(['candidate_id', 'job_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_match_scores');
    }
};

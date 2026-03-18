<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();

            $table->unsignedTinyInteger('overall_score')->index();
            $table->unsignedTinyInteger('skill_score')->default(0);
            $table->unsignedTinyInteger('experience_score')->default(0);
            $table->unsignedTinyInteger('salary_score')->default(0);
            $table->unsignedTinyInteger('work_preference_score')->default(0);
            $table->unsignedTinyInteger('personality_score')->default(0);
            $table->unsignedTinyInteger('education_score')->default(0);
            $table->unsignedTinyInteger('availability_score')->default(0);

            $table->json('matched_skills')->nullable();
            $table->json('missing_skills')->nullable();
            $table->json('matched_requirements')->nullable();
            $table->json('missing_requirements')->nullable();

            $table->timestamp('scored_at')->useCurrent();
            $table->boolean('is_latest')->default(true)->index();

            $table->timestamps();

            $table->index(['application_id', 'is_latest']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_profiles');
    }
};

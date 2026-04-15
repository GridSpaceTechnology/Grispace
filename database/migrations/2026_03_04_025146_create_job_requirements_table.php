<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->enum('requirement_type', [
                'skill',
                'experience',
                'education',
                'certification',
                'language',
                'personality',
                'work_preference',
                'salary_range',
                'location',
            ])->index();
            $table->string('requirement_value', 255)->index();
            $table->unsignedTinyInteger('weight')->default(100);
            $table->boolean('is_mandatory')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['job_id', 'requirement_type', 'requirement_value']);
            $table->index(['requirement_type', 'requirement_value', 'is_mandatory']);
        });

        Schema::create('job_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('min_proficiency')->nullable();
            $table->boolean('is_required')->default(true)->index();
            $table->unsignedTinyInteger('weight')->default(100);
            $table->timestamps();

            $table->unique(['job_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_skills');
        Schema::dropIfExists('job_requirements');
    }
};

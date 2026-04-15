<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signal_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->text('description')->nullable();
            $table->smallInteger('display_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('candidate_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('signal_categories')->cascadeOnDelete();
            $table->enum('signal_type', [
                'technical_skill',
                'soft_skill',
                'tool_proficiency',
                'certification',
                'language',
                'work_style',
                'value',
                'achievement',
                'industry_experience',
                'project_type',
                'team_role',
                'leadership_level',
            ])->index();
            $table->string('value', 255)->index();
            $table->json('metadata')->nullable();
            $table->unsignedTinyInteger('proficiency_level')->nullable();
            $table->unsignedSmallInteger('years_experience')->nullable();
            $table->boolean('is_verified')->default(false)->index();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'signal_type', 'value']);
            $table->index(['signal_type', 'value']);
            $table->index(['category_id', 'is_verified']);
        });

        DB::table('signal_categories')->insert([
            ['name' => 'Technical Skills', 'slug' => 'technical_skills', 'description' => 'Programming languages, frameworks, and technical competencies', 'display_order' => 1, 'is_active' => true],
            ['name' => 'Soft Skills', 'slug' => 'soft_skills', 'description' => 'Communication, leadership, and interpersonal skills', 'display_order' => 2, 'is_active' => true],
            ['name' => 'Tools & Platforms', 'slug' => 'tools_platforms', 'description' => 'Software tools, platforms, and technologies', 'display_order' => 3, 'is_active' => true],
            ['name' => 'Certifications', 'slug' => 'certifications', 'description' => 'Professional certifications and credentials', 'display_order' => 4, 'is_active' => true],
            ['name' => 'Languages', 'slug' => 'languages', 'description' => 'Spoken languages and proficiency', 'display_order' => 5, 'is_active' => true],
            ['name' => 'Work Style', 'slug' => 'work_style', 'description' => 'Preferred work environment and style', 'display_order' => 6, 'is_active' => true],
            ['name' => 'Values & Motivations', 'slug' => 'values_motivations', 'description' => 'What drives and motivates the candidate', 'display_order' => 7, 'is_active' => true],
            ['name' => 'Achievements', 'slug' => 'achievements', 'description' => 'Notable accomplishments and results', 'display_order' => 8, 'is_active' => true],
            ['name' => 'Industry Experience', 'slug' => 'industry_experience', 'description' => 'Industry-specific experience', 'display_order' => 9, 'is_active' => true],
            ['name' => 'Project Types', 'slug' => 'project_types', 'description' => 'Types of projects worked on', 'display_order' => 10, 'is_active' => true],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_signals');
        Schema::dropIfExists('signal_categories');
    }
};

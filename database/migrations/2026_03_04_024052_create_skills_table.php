<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->string('category', 100)->nullable()->index();
            $table->string('type', 50)->default('technical')->index();
            $table->text('description')->nullable();
            $table->boolean('is_verified')->default(false)->index();
            $table->unsignedSmallInteger('demand_score')->default(50);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['category', 'is_active']);
            $table->index(['type', 'is_active']);
        });

        DB::table('skills')->insert([
            ['name' => 'PHP', 'slug' => 'php', 'category' => 'Programming Languages', 'type' => 'technical', 'is_verified' => true, 'demand_score' => 90],
            ['name' => 'Laravel', 'slug' => 'laravel', 'category' => 'Frameworks', 'type' => 'technical', 'is_verified' => true, 'demand_score' => 85],
            ['name' => 'JavaScript', 'slug' => 'javascript', 'category' => 'Programming Languages', 'type' => 'technical', 'is_verified' => true, 'demand_score' => 95],
            ['name' => 'React', 'slug' => 'react', 'category' => 'Frameworks', 'type' => 'technical', 'is_verified' => true, 'demand_score' => 90],
            ['name' => 'Python', 'slug' => 'python', 'category' => 'Programming Languages', 'type' => 'technical', 'is_verified' => true, 'demand_score' => 95],
            ['name' => 'SQL', 'slug' => 'sql', 'category' => 'Databases', 'type' => 'technical', 'is_verified' => true, 'demand_score' => 90],
            ['name' => 'AWS', 'slug' => 'aws', 'category' => 'Cloud Platforms', 'type' => 'technical', 'is_verified' => true, 'demand_score' => 85],
            ['name' => 'Docker', 'slug' => 'docker', 'category' => 'DevOps', 'type' => 'technical', 'is_verified' => true, 'demand_score' => 80],
            ['name' => 'Git', 'slug' => 'git', 'category' => 'Tools', 'type' => 'technical', 'is_verified' => true, 'demand_score' => 95],
            ['name' => 'REST API', 'slug' => 'rest-api', 'category' => 'Concepts', 'type' => 'technical', 'is_verified' => true, 'demand_score' => 85],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('skills');
    }
};

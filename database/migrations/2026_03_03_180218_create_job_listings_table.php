<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employer_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('company_id')->nullable()->constrained('employers')->nullOnDelete();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('description')->nullable();
            $table->string('role', 255)->index();
            $table->string('industry', 100)->nullable()->index();
            $table->enum('employment_type', [
                'full_time',
                'part_time',
                'contract',
                'freelance',
                'internship',
            ])->index();
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->boolean('salary_visible')->default(true);
            $table->enum('work_preference', [
                'remote',
                'hybrid',
                'onsite',
                'flexible',
            ])->index();
            $table->string('location', 255)->nullable();
            $table->string('location_country', 100)->nullable()->index();
            $table->unsignedSmallInteger('minimum_experience')->default(0)->index();
            $table->enum('experience_level', [
                'entry',
                'junior',
                'mid',
                'senior',
                'lead',
                'principal',
                'any',
            ])->default('any')->index();
            $table->enum('temperament_preference', [
                'analytical',
                'expressive',
                'amiable',
                'driver',
            ])->nullable();
            $table->json('nice_to_have')->nullable();
            $table->enum('status', [
                'draft',
                'open',
                'paused',
                'closed',
                'filled',
            ])->default('draft')->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('applications_count')->default(0);
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index(['employer_id', 'status']);
            $table->index(['industry', 'status', 'work_preference']);
            $table->index(['experience_level', 'employment_type']);
            $table->index(['location_country', 'work_preference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};

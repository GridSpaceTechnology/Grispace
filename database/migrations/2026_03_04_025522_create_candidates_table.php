<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('current_role', 255)->nullable()->index();
            $table->string('desired_role', 255)->nullable()->index();
            $table->unsignedSmallInteger('years_of_experience')->nullable()->index();
            $table->string('industry', 100)->nullable()->index();
            $table->enum('employment_type_preference', [
                'full_time',
                'part_time',
                'contract',
                'freelance',
                'internship',
            ])->nullable()->index();
            $table->decimal('salary_expectation_min', 12, 2)->nullable();
            $table->decimal('salary_expectation_max', 12, 2)->nullable();
            $table->enum('availability', [
                'immediately',
                '2_weeks',
                '1_month',
                '2_months',
                '3_months',
                'passive',
            ])->default('passive')->index();
            $table->enum('experience_level', [
                'entry',
                'junior',
                'mid',
                'senior',
                'lead',
                'principal',
                'executive',
            ])->nullable()->index();
            $table->enum('work_preference', [
                'remote',
                'hybrid',
                'onsite',
                'flexible',
            ])->nullable()->index();
            $table->string('location', 255)->nullable();
            $table->string('location_country', 100)->nullable()->index();
            $table->text('greatest_achievement')->nullable();
            $table->unsignedTinyInteger('profile_completion_percentage')->default(0);
            $table->boolean('onboarding_completed')->default(false)->index();
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->timestamps();

            $table->index(['experience_level', 'work_preference']);
            $table->index(['years_of_experience', 'experience_level']);
            $table->index(['location_country', 'work_preference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};

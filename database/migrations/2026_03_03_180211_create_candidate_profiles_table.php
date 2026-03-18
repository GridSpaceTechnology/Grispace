<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('current_role')->nullable();
            $table->string('desired_role')->nullable();
            $table->integer('years_of_experience')->default(0);
            $table->string('industry')->nullable();
            $table->string('employment_type_preference')->nullable();
            $table->decimal('salary_expectation', 12, 2)->nullable();
            $table->string('work_preference')->nullable();
            $table->text('greatest_achievement')->nullable();
            $table->integer('profile_completion_percentage')->default(0);
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_profiles');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('candidate_behavioral_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->unique();
            $table->json('role_signals')->nullable();
            $table->json('domain_signals')->nullable();
            $table->json('skill_signals')->nullable();
            $table->json('work_preference_signals')->nullable();
            $table->json('location_signals')->nullable();
            $table->json('viewed_jobs')->nullable();
            $table->json('applied_jobs')->nullable();
            $table->json('saved_jobs')->nullable();
            $table->unsignedInteger('total_events')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_behavioral_profiles');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_personality_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->string('work_style')->nullable();
            $table->string('communication_style')->nullable();
            $table->string('collaboration_style')->nullable();
            $table->string('leadership_style')->nullable();
            $table->string('motivation_type')->nullable();
            $table->string('temperament_type')->nullable();
            $table->string('organizational_fit')->nullable();
            $table->text('personality_summary')->nullable();
            $table->text('work_style_summary')->nullable();
            $table->text('strengths_summary')->nullable();
            $table->boolean('assessment_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('candidate_id');
            $table->index('assessment_completed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_personality_profiles');
    }
};

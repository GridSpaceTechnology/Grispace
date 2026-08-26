<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_match_scores', function (Blueprint $table) {
            $table->unsignedTinyInteger('skill_score')->default(0)->after('candidate_id');
            $table->unsignedTinyInteger('role_score')->default(0)->after('skill_score');
            $table->unsignedTinyInteger('experience_score')->default(0)->after('role_score');
            $table->unsignedTinyInteger('personality_score')->default(0)->after('experience_score');
            $table->unsignedTinyInteger('work_preference_score')->default(0)->after('personality_score');
            $table->unsignedTinyInteger('salary_score')->default(0)->after('work_preference_score');
            $table->unsignedTinyInteger('education_score')->default(0)->after('salary_score');
            $table->unsignedTinyInteger('availability_score')->default(0)->after('education_score');

            $table->json('matched_skills')->nullable()->after('availability_score');
            $table->json('missing_skills')->nullable()->after('matched_skills');
            $table->json('strengths')->nullable()->after('missing_skills');
            $table->json('gaps')->nullable()->after('strengths');
            $table->json('reasons')->nullable()->after('gaps');

            $table->timestamp('scored_at')->nullable()->after('reasons');
            $table->boolean('is_latest')->default(true)->after('scored_at');

            $table->index('is_latest');
            $table->index(['is_latest', 'overall_match_score']);
        });

        // Preserve meaning of legacy scores while backfilling the new
        // canonical columns so existing rows remain interpretable.
        DB::table('job_match_scores')->update([
            'skill_score' => DB::raw('skills_fit_score'),
            'personality_score' => DB::raw('personality_fit_score'),
            'scored_at' => DB::raw('updated_at'),
            'is_latest' => true,
        ]);
    }

    public function down(): void
    {
        Schema::table('job_match_scores', function (Blueprint $table) {
            $table->dropIndex(['is_latest', 'overall_match_score']);
            $table->dropIndex(['is_latest']);

            $table->dropColumn([
                'skill_score',
                'role_score',
                'experience_score',
                'personality_score',
                'work_preference_score',
                'salary_score',
                'education_score',
                'availability_score',
                'matched_skills',
                'missing_skills',
                'strengths',
                'gaps',
                'reasons',
                'scored_at',
                'is_latest',
            ]);
        });
    }
};

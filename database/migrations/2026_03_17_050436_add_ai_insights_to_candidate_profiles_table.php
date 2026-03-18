<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_profiles', function (Blueprint $table) {
            $table->text('ai_summary')->nullable()->after('profile_completion_percentage');
            $table->json('ai_strengths')->nullable()->after('ai_summary');
            $table->json('ai_risks')->nullable()->after('ai_strengths');
            $table->text('ai_recommendation')->nullable()->after('ai_risks');
            $table->timestamp('ai_last_generated_at')->nullable()->after('ai_recommendation');
        });
    }

    public function down(): void
    {
        Schema::table('candidate_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'ai_summary',
                'ai_strengths',
                'ai_risks',
                'ai_recommendation',
                'ai_last_generated_at',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employer_culture_profiles', function (Blueprint $table) {
            $table->string('work_environment')->nullable()->after('user_id');
            $table->string('company_pace')->nullable()->after('collaboration_level');
            $table->json('preferred_traits')->nullable()->after('company_pace');
            $table->json('motivation_factors')->nullable()->after('preferred_traits');
            $table->string('independence_level')->nullable()->after('motivation_factors');
            $table->text('culture_summary')->nullable()->after('independence_level');
        });
    }

    public function down(): void
    {
        Schema::table('employer_culture_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'work_environment',
                'company_pace',
                'preferred_traits',
                'motivation_factors',
                'independence_level',
                'culture_summary',
            ]);
        });
    }
};

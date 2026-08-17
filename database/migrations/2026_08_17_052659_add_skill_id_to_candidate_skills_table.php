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
        Schema::table('candidate_skills', function (Blueprint $table) {
            $table->foreignId('skill_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->index('skill_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidate_skills', function (Blueprint $table) {
            $table->dropForeign(['skill_id']);
            $table->dropIndex('candidate_skills_skill_id_index');
            $table->dropColumn('skill_id');
        });
    }
};

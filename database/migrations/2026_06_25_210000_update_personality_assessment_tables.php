<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personality_questions', function (Blueprint $table) {
            $table->renameColumn('active_status', 'is_active');
        });

        Schema::table('personality_question_options', function (Blueprint $table) {
            $table->integer('option_value')->default(1)->after('option_text');
            $table->string('personality_dimension')->nullable()->after('option_value');
            $table->integer('weight')->default(1)->after('personality_dimension');
        });

        Schema::table('candidate_personality_profiles', function (Blueprint $table) {
            $table->json('dimension_scores')->nullable()->after('strengths_summary');
            $table->json('dominant_traits')->nullable()->after('dimension_scores');
            $table->text('workplace_compatibility')->nullable()->after('dominant_traits');
        });
    }

    public function down(): void
    {
        Schema::table('personality_questions', function (Blueprint $table) {
            $table->renameColumn('is_active', 'active_status');
        });

        Schema::table('personality_question_options', function (Blueprint $table) {
            $table->dropColumn(['option_value', 'personality_dimension', 'weight']);
        });

        Schema::table('candidate_personality_profiles', function (Blueprint $table) {
            $table->dropColumn(['dimension_scores', 'dominant_traits', 'workplace_compatibility']);
        });
    }
};

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
            $table->unsignedTinyInteger('recommendation_score')->default(0)->after('overall_match_score');
            $table->string('match_status', 20)->nullable()->after('recommendation_score');
            $table->unsignedSmallInteger('algorithm_version')->default(0)->after('match_status');
            $table->char('data_checksum', 64)->nullable()->after('algorithm_version');
            $table->timestamp('expires_at')->nullable()->after('data_checksum');

            $table->index(['is_latest', 'algorithm_version']);
            $table->index(['is_latest', 'recommendation_score']);
        });

        Schema::table('match_profiles', function (Blueprint $table) {
            $table->unsignedSmallInteger('algorithm_version')->default(0)->after('is_latest');
            $table->char('data_checksum', 64)->nullable()->after('algorithm_version');
        });

        // Existing rows were produced by the current algorithm and carry no
        // checksum, so flag them with the current version so version-based
        // staleness checks treat them as fresh-enough baseline data.
        $version = (int) config('matching.algorithm_version', 3);

        DB::table('job_match_scores')->update([
            'recommendation_score' => DB::raw('overall_match_score'),
            'algorithm_version' => $version,
        ]);

        DB::table('match_profiles')->update([
            'algorithm_version' => $version,
        ]);
    }

    public function down(): void
    {
        Schema::table('job_match_scores', function (Blueprint $table) {
            $table->dropIndex(['is_latest', 'recommendation_score']);
            $table->dropIndex(['is_latest', 'algorithm_version']);

            $table->dropColumn([
                'recommendation_score',
                'match_status',
                'algorithm_version',
                'data_checksum',
                'expires_at',
            ]);
        });

        Schema::table('match_profiles', function (Blueprint $table) {
            $table->dropColumn(['algorithm_version', 'data_checksum']);
        });
    }
};

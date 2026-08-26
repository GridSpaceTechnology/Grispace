<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Repairs foreign keys that were originally created with constrained()
 * and therefore pointed at an implied "jobs" table instead of the real
 * "job_listings" table used by the Job model.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_skills', function ($table) {
            $table->dropForeign(['job_id']);

            $table->foreign('job_id')
                ->references('id')
                ->on('job_listings')
                ->cascadeOnDelete();
        });

        Schema::table('job_requirements', function ($table) {
            $table->dropForeign(['job_id']);

            $table->foreign('job_id')
                ->references('id')
                ->on('job_listings')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_skills', function ($table) {
            $table->dropForeign(['job_id']);

            $table->foreign('job_id')->constrained();
        });

        Schema::table('job_requirements', function ($table) {
            $table->dropForeign(['job_id']);

            $table->foreign('job_id')->constrained();
        });
    }
};

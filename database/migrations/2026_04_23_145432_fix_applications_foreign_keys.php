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
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('applications');

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_id')->constrained('job_listings')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('applied');
            $table->integer('match_score')->default(0);
            $table->text('screening_answers')->nullable();
            $table->text('candidate_note')->nullable();
            $table->text('employer_note')->nullable();
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('shortlisted_at')->nullable();
            $table->timestamp('interview_at')->nullable();
            $table->timestamp('offer_sent_at')->nullable();
            $table->timestamp('hired_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->unique(['job_id', 'candidate_id']);
            $table->index(['candidate_id', 'status']);
            $table->index(['job_id', 'status']);
            $table->index(['status', 'applied_at']);
            $table->index(['status']);
            $table->index(['match_score']);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            //
        });
    }
};

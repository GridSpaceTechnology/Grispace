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
        Schema::create('candidate_search_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('query', 255)->nullable();
            $table->string('normalized_query', 255)->nullable();
            $table->string('domain', 64)->nullable();
            $table->string('detected_role', 255)->nullable();
            $table->json('skills')->nullable();
            $table->string('seniority', 32)->nullable();
            $table->string('location', 64)->nullable();
            $table->string('work_preference', 32)->nullable();
            $table->json('filters')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'normalized_query']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidate_search_histories');
    }
};

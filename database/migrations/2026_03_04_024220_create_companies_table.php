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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('industry')->nullable()->index();
            $table->string('company_size')->nullable();
            $table->smallInteger('founded_year')->nullable();
            $table->string('website')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('cover_image_url')->nullable();
            $table->string('location')->nullable();
            $table->string('location_country')->nullable();
            $table->json('culture_values_json')->nullable();
            $table->json('benefits_json')->nullable();
            $table->boolean('is_verified')->default(false)->index();
            $table->timestamps();
            $table->index(['industry', 'is_verified']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company_name', 255);
            $table->string('slug', 255)->unique();
            $table->string('tagline', 500)->nullable();
            $table->text('description')->nullable();
            $table->string('industry', 100)->nullable()->index();
            $table->enum('company_size', [
                '1-10',
                '11-50',
                '51-200',
                '201-500',
                '501-1000',
                '1000+',
            ])->nullable()->index();
            $table->unsignedSmallInteger('founded_year')->nullable();
            $table->string('website', 500)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->string('cover_image_url', 500)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('location_country', 100)->nullable()->index();
            $table->json('benefits')->nullable();
            $table->json('culture_values')->nullable();
            $table->json('tech_stack')->nullable();
            $table->boolean('is_verified')->default(false)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['industry', 'is_verified']);
            $table->index(['company_size', 'location_country']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employers');
    }
};

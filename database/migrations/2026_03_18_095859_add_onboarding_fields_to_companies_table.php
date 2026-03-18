<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('phone_number')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->text('culture_description')->nullable();
            $table->enum('work_model', ['remote', 'hybrid', 'onsite'])->default('onsite');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'phone_number',
                'linkedin_url',
                'instagram_url',
                'twitter_url',
                'culture_description',
                'work_model',
            ]);
        });
    }
};

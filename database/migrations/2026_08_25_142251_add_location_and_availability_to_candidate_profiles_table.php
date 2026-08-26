<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidate_profiles', function (Blueprint $table) {
            $table->string('location_country', 100)->nullable()->after('work_preference')->index();
            $table->string('availability', 30)->nullable()->after('location_country');
        });
    }

    public function down(): void
    {
        Schema::table('candidate_profiles', function (Blueprint $table) {
            $table->dropIndex(['location_country']);
            $table->dropColumn(['location_country', 'availability']);
        });
    }
};

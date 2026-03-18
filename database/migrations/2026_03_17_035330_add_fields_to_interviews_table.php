<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->foreignId('job_application_id')->nullable()->constrained('applications')->onDelete('set null');
            $table->string('meeting_link')->nullable()->after('scheduled_at');
            $table->string('location')->nullable()->after('meeting_link');
        });
    }

    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->dropForeign(['job_application_id']);
            $table->dropColumn(['job_application_id', 'meeting_link', 'location']);
        });
    }
};

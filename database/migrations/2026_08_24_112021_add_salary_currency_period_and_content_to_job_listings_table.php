<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->string('salary_currency', 3)->nullable()->after('salary_max');
            $table->enum('salary_period', [
                'hourly',
                'daily',
                'weekly',
                'monthly',
                'yearly',
            ])->nullable()->after('salary_currency');

            $table->text('responsibilities')->nullable()->after('description');
            $table->text('requirements')->nullable()->after('responsibilities');
            $table->text('benefits')->nullable()->after('requirements');
        });

        DB::table('job_listings')
            ->whereNull('salary_currency')
            ->update(['salary_currency' => 'NGN']);

        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropForeign(['company_id']);

            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropForeign(['company_id']);

            $table->foreign('company_id')
                ->references('id')
                ->on('employers')
                ->nullOnDelete();
        });

        Schema::table('job_listings', function (Blueprint $table) {
            $table->dropColumn([
                'salary_currency',
                'salary_period',
                'responsibilities',
                'requirements',
                'benefits',
            ]);
        });
    }
};

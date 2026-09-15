<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semantic_health_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50);
            $table->string('model', 100)->nullable();
            $table->string('action', 20);
            $table->string('status', 20);
            $table->unsignedInteger('latency_ms')->nullable();
            $table->string('message', 255)->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semantic_health_events');
    }
};

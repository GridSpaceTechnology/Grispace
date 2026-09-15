<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('semantic_embeddings', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type', 20);
            $table->unsignedBigInteger('entity_id');
            $table->string('provider', 50);
            $table->string('model', 100)->nullable();
            $table->string('embedding_version', 50);
            $table->unsignedSmallInteger('dimensions')->nullable();
            $table->json('embedding')->nullable();
            $table->char('content_hash', 64)->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();

            $table->unique(['entity_type', 'entity_id', 'provider', 'model', 'embedding_version'], 'semantic_embeddings_entity_unique');
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('semantic_embeddings');
    }
};

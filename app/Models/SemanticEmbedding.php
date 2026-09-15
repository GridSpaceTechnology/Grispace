<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A stored embedding for a candidate or job, tagged with the provider, model
 * and deterministic content hash that produced it. Embeddings are sensitive
 * derived data: they are never rendered through API resources or exposed in
 * any serialization.
 */
class SemanticEmbedding extends Model
{
    protected $table = 'semantic_embeddings';

    protected $fillable = [
        'entity_type',
        'entity_id',
        'provider',
        'model',
        'embedding_version',
        'dimensions',
        'embedding',
        'content_hash',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function isCurrent(string $hash): bool
    {
        return $this->content_hash !== null && hash_equals($this->content_hash, $hash);
    }

    public function vector(): ?array
    {
        return $this->embedding;
    }
}

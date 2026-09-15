<?php

namespace App\Services\Semantic;

/**
 * Resolves the active semantic provider from config. The lexical provider is
 * the zero-cost, always-available default; the embedding provider is only
 * selected when matching.semantic.provider is 'embeddings' and is still
 * covered by the same bounded, explainable contract.
 */
class SemanticProviderFactory
{
    public function __construct(
        protected LexicalSemanticProvider $lexical,
        protected EmbeddingSemanticProvider $embeddings,
    ) {}

    public function name(): string
    {
        return (string) config('matching.semantic.provider', 'lexical');
    }

    public function resolve(): SemanticProviderInterface
    {
        return $this->name() === 'embeddings' ? $this->embeddings : $this->lexical;
    }

    public function lexical(): SemanticProviderInterface
    {
        return $this->lexical;
    }
}

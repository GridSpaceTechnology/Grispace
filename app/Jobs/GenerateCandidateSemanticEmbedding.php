<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Semantic\SemanticEmbeddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Generates (or refreshes) the stored embedding for a single candidate. Only
 * active when the embedding provider is configured; idempotent via the content
 * hash, so a candidate whose representation has not changed is skipped.
 */
class GenerateCandidateSemanticEmbedding implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120];

    public int $timeout = 300;

    public function __construct(public User $candidate)
    {
        $this->queue = 'matching';
    }

    public function uniqueId(): string
    {
        return "candidate-semantic-embedding:{$this->candidate->id}";
    }

    public function uniqueFor(): int
    {
        return 3600;
    }

    public function handle(SemanticEmbeddingService $embeddings): void
    {
        if (! $this->candidate->isCandidate()) {
            return;
        }

        $embeddings->generateForCandidate($this->candidate->refresh());
    }
}

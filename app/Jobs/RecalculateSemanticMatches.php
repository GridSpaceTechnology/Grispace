<?php

namespace App\Jobs;

use App\Models\Job;
use App\Models\User;
use App\Services\Semantic\SemanticEmbeddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Regenerate the semantic state for a single entity (candidate or job):
 * ensures its stored embedding is current with the provider content hash.
 * Dispatched in bulk by the semantic:refresh command for shared hosting
 * without a persistent queue worker.
 */
class RecalculateSemanticMatches implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120];

    public int $timeout = 300;

    public function __construct(public User|Job $entity)
    {
        $this->queue = 'matching';
    }

    public function uniqueId(): string
    {
        $type = $this->entity instanceof Job ? 'job' : 'candidate';

        return "semantic-match-recalc:{$type}:{$this->entity->id}";
    }

    public function uniqueFor(): int
    {
        return 3600;
    }

    public function handle(SemanticEmbeddingService $embeddings): void
    {
        $embeddings->generateForEntity($this->entity->refresh());
    }
}

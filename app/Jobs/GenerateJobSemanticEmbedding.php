<?php

namespace App\Jobs;

use App\Models\Job;
use App\Services\Semantic\SemanticEmbeddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Generates (or refreshes) the stored embedding for a single open job. Only
 * active when the embedding provider is configured; idempotent via the content
 * hash, so listings whose content has not changed are skipped.
 */
class GenerateJobSemanticEmbedding implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120];

    public int $timeout = 300;

    public function __construct(public Job $job)
    {
        $this->queue = 'matching';
    }

    public function uniqueId(): string
    {
        return "job-semantic-embedding:{$this->job->id}";
    }

    public function uniqueFor(): int
    {
        return 3600;
    }

    public function handle(SemanticEmbeddingService $embeddings): void
    {
        if ($this->job->status !== 'open') {
            return;
        }

        $embeddings->generateForJob($this->job->refresh());
    }
}

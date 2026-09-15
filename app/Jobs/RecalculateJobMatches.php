<?php

namespace App\Jobs;

use App\Models\Job;
use App\Services\MatchingEngineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculateJobMatches implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120];

    public int $timeout = 600;

    public function __construct(public Job $jobListing)
    {
        $this->queue = 'matching';
    }

    // No two recalculation jobs for the same listing should ever be queued at
    // once, so a flurry of edits does not enqueue a duplicate storm.
    public function uniqueId(): string
    {
        return "job-match-recalc:{$this->jobListing->id}";
    }

    public function uniqueFor(): int
    {
        return 3600;
    }

    public function handle(MatchingEngineService $engine): void
    {
        $job = $this->jobListing->refresh();

        if ($job->status !== 'open') {
            return;
        }

        $engine->recalculateForJob($job);

        // Semantic vectors become stale whenever the listing changes; queue a
        // regeneration so the embedding provider (when enabled) stays current.
        if ((bool) config('matching.semantic.enabled', false)
            && config('matching.semantic.provider', 'lexical') === 'embeddings') {
            GenerateJobSemanticEmbedding::dispatch($this->jobListing);
        }
    }
}

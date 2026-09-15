<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\MatchingEngineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculateCandidateMatches implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120];

    public int $timeout = 600;

    public function __construct(public User $candidate)
    {
        $this->queue = 'matching';
    }

    // No two recalculation jobs for the same candidate should ever be queued
    // at once, so a flurry of profile edits cannot pile up duplicate work.
    public function uniqueId(): string
    {
        return "candidate-match-recalc:{$this->candidate->id}";
    }

    public function uniqueFor(): int
    {
        return 3600;
    }

    public function handle(MatchingEngineService $engine): void
    {
        if (! $this->candidate->isCandidate() || ! $this->candidate->onboarding_completed) {
            return;
        }

        $engine->recalculateForCandidate($this->candidate->refresh());

        // Semantic vectors become stale whenever the profile changes; queue a
        // regeneration so the embedding provider (when enabled) stays current.
        if ($this->semanticEmbedsEnabled()) {
            GenerateCandidateSemanticEmbedding::dispatch($this->candidate);
        }
    }

    private function semanticEmbedsEnabled(): bool
    {
        return (bool) config('matching.semantic.enabled', false)
            && config('matching.semantic.provider', 'lexical') === 'embeddings';
    }
}

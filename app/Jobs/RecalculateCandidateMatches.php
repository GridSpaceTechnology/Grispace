<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\MatchingEngineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculateCandidateMatches implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120];

    public function __construct(public User $candidate) {}

    public function handle(MatchingEngineService $engine): void
    {
        if (! $this->candidate->isCandidate() || ! $this->candidate->onboarding_completed) {
            return;
        }

        $engine->recalculateForCandidate($this->candidate->refresh());
    }
}

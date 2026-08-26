<?php

namespace App\Jobs;

use App\Models\Job;
use App\Services\MatchingEngineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RecalculateJobMatches implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120];

    public function __construct(public Job $jobListing) {}

    public function handle(MatchingEngineService $engine): void
    {
        $engine->recalculateForJob($this->jobListing->refresh());
    }
}

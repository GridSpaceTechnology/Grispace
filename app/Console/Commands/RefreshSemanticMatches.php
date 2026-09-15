<?php

namespace App\Console\Commands;

use App\Jobs\GenerateCandidateSemanticEmbedding;
use App\Jobs\GenerateJobSemanticEmbedding;
use App\Models\Job;
use App\Models\User;
use App\Services\Semantic\SemanticEmbeddingService;
use Illuminate\Console\Command;

/**
 * Refresh semantic state (stored embedding vectors) for onboarding-complete
 * candidates and open jobs. Queue-based by default so a schedule/cron can
 * batch this on shared hosting without a persistent worker; --sync runs each
 * generation inline for immediate backfills.
 */
class RefreshSemanticMatches extends Command
{
    protected $signature = 'semantic:refresh
        {--entity=candidates : Which entities to refresh: candidates|jobs|all}
        {--limit=500 : Maximum entities to consider}
        {--sync : Run generations synchronously instead of dispatching jobs}';

    protected $description = 'Regenerate semantic embeddings for candidates/jobs (embedding provider only)';

    public function handle(SemanticEmbeddingService $embeddings): int
    {
        if (! (bool) config('matching.semantic.enabled', false)) {
            $this->warn('Semantic matching is disabled (matching.semantic.enabled). Nothing to refresh.');

            return self::SUCCESS;
        }

        if (config('matching.semantic.provider', 'lexical') !== 'embeddings') {
            $this->info('Provider is lexical - no stored vectors to refresh.');

            return self::SUCCESS;
        }

        if (trim((string) config('matching.embedding.api_key', '')) === '') {
            $this->warn('Embedding provider configured but EMBEDDING_API_KEY is empty.');

            return self::SUCCESS;
        }

        $entity = $this->option('entity');
        $limit = max(1, (int) $this->option('limit'));
        $sync = (bool) $this->option('sync');

        $candidates = [];
        $jobs = [];

        if (in_array($entity, ['candidates', 'all'], true)) {
            $candidates = User::query()
                ->where('role', 'candidate')
                ->where('onboarding_completed', true)
                ->limit($limit)
                ->pluck('id')
                ->all();
        }

        if (in_array($entity, ['jobs', 'all'], true)) {
            $jobs = Job::query()
                ->where('status', 'open')
                ->limit($limit)
                ->pluck('id')
                ->all();
        }

        $this->info(sprintf('Refreshing %d candidate(s) and %d job(s).', count($candidates), count($jobs)));

        foreach ($candidates as $candidateId) {
            if ($sync) {
                $embeddings->generateForCandidate(User::find($candidateId));
            } else {
                GenerateCandidateSemanticEmbedding::dispatch(User::find($candidateId));
            }
        }

        foreach ($jobs as $jobId) {
            if ($sync) {
                $embeddings->generateForJob(Job::find($jobId));
            } else {
                GenerateJobSemanticEmbedding::dispatch(Job::find($jobId));
            }
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}

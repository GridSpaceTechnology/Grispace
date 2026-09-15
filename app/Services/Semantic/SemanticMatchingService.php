<?php

namespace App\Services\Semantic;

use App\Models\Job;
use App\Models\SemanticHealthEvent;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Public entry point for the semantic understanding layer.
 *
 * Produces a bounded, explainable reasoning payload for a candidate/job pair
 * layered on top of the deterministic breakdown. Guarantees:
 *
 *  - It is opt-in (off by default) and never fires for hard-gated,
 *    incompatible pairs.
 *  - Its only ranking effect is an additive, capped nudge to the
 *    recommendation score - never to the professional profile score.
 *  - Providers that fail fall back to the deterministic lexical provider so
 *    ranking never breaks.
 *  - Cross-domain pairs are capped so semantics can never make an unrelated
 *    profession look related.
 *  - Reasons are evidence-based ("your Postgres experience matches
 *    PostgreSQL"), never false-certainty percentages.
 */
class SemanticMatchingService
{
    public function __construct(
        protected SemanticRepresentation $representations,
        protected SemanticProviderFactory $providers,
        protected SemanticEmbeddingService $embeddingService,
    ) {}

    /**
     * @return array{
     *     score: ?int,
     *     points: int,
     *     reasons: array,
     *     details: array,
     *     provider: ?string,
     *     applied: bool,
     *     fallback: bool,
     *     disabled: bool
     * }
     */
    public function forPair(User $candidate, Job $job, array $breakdown, string $perspective = 'candidate'): array
    {
        $result = [
            'score' => null,
            'points' => 0,
            'reasons' => [],
            'details' => [],
            'provider' => null,
            'applied' => false,
            'fallback' => false,
            'disabled' => true,
        ];

        if (! $this->enabled()) {
            return $result;
        }

        $result['disabled'] = false;

        // The professional domain gate is absolute: semantic understanding is
        // a ranking refinement for compatible pairs only.
        if (($breakdown['domain_compatible'] ?? true) === false || ($breakdown['match_status'] ?? '') === 'incompatible') {
            return $result;
        }

        $candidatePayload = $this->representations->forCandidate($candidate);
        $jobPayload = $this->representations->forJob($job);

        $context = [
            'candidate_domain' => $candidatePayload['domain'],
            'job_domain' => $jobPayload['domain'],
            'profile_match_score' => (int) ($breakdown['profile_match_score'] ?? 0),
            'job_title' => (string) ($job->title ?? ''),
            'perspective' => $perspective,
        ];

        // Fetch the candidate vector once so embedding scoring does not
        // re-read it for every job in a ranking loop.
        if ($this->providers->name() === 'embeddings') {
            $context['candidate_vector'] = $this->embeddingService->vectorFor('candidate', $candidate->id);
        }

        $provider = $this->providers->resolve();
        $started = microtime(true);
        $usedProvider = $this->providers->name();
        $similarity = $provider->similarities($candidatePayload, $jobPayload, $context);

        if ($similarity === null && $provider instanceof EmbeddingSemanticProvider) {
            $similarity = $this->providers->lexical()->similarities($candidatePayload, $jobPayload, $context);
            $usedProvider = 'lexical';
            $result['fallback'] = true;
        }

        if ($similarity === null) {
            $this->report('score', 'failure', $usedProvider, $this->latencyMs($started), error: 'Semantic provider returned no result');

            return $result;
        }

        $score = (int) round(min(100, max(0, $similarity['score'])));

        // Cross-domain separation, mirroring the deterministic gate.
        if ($candidatePayload['domain'] !== null
            && $jobPayload['domain'] !== null
            && $candidatePayload['domain'] !== $jobPayload['domain']) {
            $score = min($score, (int) config('matching.semantic.incompatible_cap', 20));
        }

        $maximumInfluence = max(0, (int) config('matching.semantic.maximum_influence', 15));
        $points = (int) round(($score / 100) * $maximumInfluence);

        return [
            'score' => $score,
            'points' => $points,
            'reasons' => array_slice($similarity['reasons'] ?? [], 0, (int) config('matching.semantic.reasons_max', 3)),
            'details' => $similarity['details'] ?? [],
            'provider' => $this->providers->name(),
            'applied' => $points > 0,
            'fallback' => $result['fallback'],
            'disabled' => false,
        ];
    }

    public function enabled(): bool
    {
        return (bool) config('matching.semantic.enabled', false);
    }

    private function report(string $action, string $status, string $provider, ?int $latencyMs = null, ?string $message = null, ?string $error = null): void
    {
        SemanticHealthEvent::create([
            'provider' => $provider,
            'model' => config('matching.embedding.model'),
            'action' => $action,
            'status' => $status,
            'latency_ms' => $latencyMs,
            'message' => $message,
            'error' => $error !== null ? Str::limit($error, 500) : null,
        ]);
    }

    private function latencyMs(float $started): int
    {
        return (int) round((microtime(true) - $started) * 1000);
    }
}

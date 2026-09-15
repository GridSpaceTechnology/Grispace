<?php

namespace App\Services\Semantic;

/**
 * Hosted-embedding semantic provider.
 *
 * Numeric similarity comes from cosine distance between the stored candidate
 * and job vectors (generated asynchronously by the queue jobs). Evidence and
 * explanations are produced by the deterministic lexical provider so the UI
 * reasons remain concrete ("your Postgres experience matches PostgreSQL"),
 * never a bare "AI says 90%". A small lexical domain blend keeps unrelated
 * professions separated even when an embedding model would not.
 *
 * Returns null when either vector is missing or stale - the caller then falls
 * back to the lexical provider so ranking never breaks.
 */
class EmbeddingSemanticProvider implements SemanticProviderInterface
{
    public function __construct(
        protected SemanticEmbeddingService $embeddings,
        protected LexicalSemanticProvider $lexical,
    ) {}

    public function similarities(array $candidate, array $job, array $context): ?array
    {
        $candidateVector = $context['candidate_vector'] ?? $this->embeddings->vectorFor('candidate', (int) ($candidate['id'] ?? 0));
        $jobVector = $context['job_vector'] ?? $this->embeddings->vectorFor('job', (int) ($job['id'] ?? 0));

        if (! is_array($candidateVector) || ! is_array($jobVector)) {
            return null;
        }

        $cosine = $this->embeddings->cosine($candidateVector, $jobVector);
        $cosineScore = (int) round(min(1.0, max(0.0, $cosine)) * 100);

        $domainScore = $this->domainScore($candidate['domain'] ?? null, $job['domain'] ?? null);
        $lexicalResult = $this->lexical->similarities($candidate, $job, $context) ?? [];

        $weights = config('matching.semantic.weights', [
            'role' => 0.35,
            'skill' => 0.35,
            'description' => 0.20,
            'domain' => 0.10,
        ]);

        $score = (int) round(($cosineScore * (1 - $weights['domain'])) + ($domainScore * $weights['domain']));
        $score = min(100, max(0, $score));

        return [
            'score' => $score,
            'role_score' => $cosineScore,
            'skill_score' => $cosineScore,
            'description_score' => $cosineScore,
            'domain_score' => $domainScore,
            'reasons' => $lexicalResult['reasons'] ?? [],
            'details' => array_merge($lexicalResult['details'] ?? [], ['cosine_score' => $cosineScore]),
        ];
    }

    private function domainScore(?string $candidateDomain, ?string $jobDomain): int
    {
        if ($candidateDomain === null || $jobDomain === null) {
            return 50;
        }

        return $candidateDomain === $jobDomain ? 100 : 0;
    }
}

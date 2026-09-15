<?php

namespace App\Services\Semantic;

/**
 * Contract for a semantic similarity provider.
 *
 * Implementations turn the privacy-filtered candidate/job representations
 * into a bounded 0-100 semantic score plus evidence-based explanations. A
 * provider returns null when it cannot produce a score (provider failure,
 * missing embeddings, misconfiguration) - the caller then falls back to the
 * deterministic lexical provider or skips the semantic nudge entirely.
 */
interface SemanticProviderInterface
{
    /**
     * Compute semantic similarity between a candidate and a job.
     *
     * @param  array{id: int, entity_type: string, role: string, skills: array, description: string, domain: ?string, content_hash: string}  $candidate
     * @param  array{id: int, entity_type: string, role: string, skills: array, description: string, domain: ?string, content_hash: string}  $job
     * @param  array{candidate_domain: ?string, job_domain: ?string, profile_match_score: int, job_title: string, perspective: string}  $context
     * @return array{
     *     score: int,
     *     role_score: int,
     *     skill_score: int,
     *     description_score: int,
     *     domain_score: int,
     *     reasons: array,
     *     details: array
     * }|null
     */
    public function similarities(array $candidate, array $job, array $context): ?array;
}

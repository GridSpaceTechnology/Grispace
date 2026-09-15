<?php

namespace App\Services\Semantic;

/**
 * Deterministic, zero-cost semantic provider.
 *
 * Scores similarity from configured vocabularies only: role synonym groups,
 * skill name aliases (PostgreSQL/Postgres, REST/RESTful, Node.js/NodeJS) and
 * a stop-word-filtered description token overlap. No external calls, no
 * stored vectors, no personal data leaving the application - fully testable
 * and repeated identically on every request.
 *
 * This is the production-default provider on constrained shared hosting: it
 * honours the same bounded, explainable contract as a hosted embedding
 * provider while costing nothing.
 */
class LexicalSemanticProvider implements SemanticProviderInterface
{
    public function similarities(array $candidate, array $job, array $context): ?array
    {
        $roleScore = $this->roleSimilarity((string) ($candidate['role'] ?? ''), (string) ($job['role'] ?? ''));
        $skillScore = $this->skillSimilarity((array) ($candidate['skills'] ?? []), (array) ($job['skills'] ?? []));
        $descriptionScore = $this->descriptionSimilarity(
            (string) ($candidate['description'] ?? ''),
            (string) ($job['description'] ?? '')
        );
        $domainScore = $this->domainScore(
            $candidate['domain'] ?? $context['candidate_domain'] ?? null,
            $job['domain'] ?? $context['job_domain'] ?? null,
        );

        $weights = config('matching.semantic.weights', [
            'role' => 0.35,
            'skill' => 0.35,
            'description' => 0.20,
            'domain' => 0.10,
        ]);

        $score = (int) round(
            ($roleScore * $weights['role'])
            + ($skillScore * $weights['skill'])
            + ($descriptionScore * $weights['description'])
            + ($domainScore * $weights['domain'])
        );

        $score = min(100, max(0, $score));

        [$skillMatches, $skillReasons] = $this->skillEvidence((array) ($candidate['skills'] ?? []), (array) ($job['skills'] ?? []));

        return [
            'score' => $score,
            'role_score' => $roleScore,
            'skill_score' => $skillScore,
            'description_score' => $descriptionScore,
            'domain_score' => $domainScore,
            'reasons' => $this->reasons($roleScore, $skillScore, $descriptionScore, $skillReasons, $context),
            'details' => [
                'role_score' => $roleScore,
                'skill_score' => $skillScore,
                'description_score' => $descriptionScore,
                'domain_score' => $domainScore,
                'skill_matches' => $skillMatches,
                'matched_skills' => array_column($skillMatches, 'job'),
            ],
        ];
    }

    private function roleSimilarity(string $candidateRole, string $jobRole): int
    {
        if (trim($candidateRole) === '' || trim($jobRole) === '') {
            return 50;
        }

        $candidateTerms = $this->roleTerms($candidateRole);
        $jobTerms = $this->roleTerms($jobRole);

        if ($candidateTerms === [] || $jobTerms === []) {
            return 50;
        }

        return $this->jaccard($candidateTerms, $jobTerms);
    }

    private function roleTerms(string $role): array
    {
        $normalized = mb_strtolower(trim($role));

        // Collapse each synonym group onto its canonical first member so
        // "backend developer" and "server-side engineer" share terms.
        foreach (config('matching.semantic.role_synonym_groups', []) as $group) {
            foreach ($group as $member) {
                if ($this->containsPhrase($normalized, $member)) {
                    $normalized = $this->replacePhrase($normalized, $member, $group[0]);
                }
            }
        }

        // Keep the deterministic role synonyms as a secondary equality layer.
        foreach (config('matching.role.synonym_groups', []) as $group) {
            foreach ($group as $member) {
                if ($this->containsWord($normalized, $member)) {
                    $normalized = preg_replace('/\b'.preg_quote($member, '/').'\b/', $group[0], $normalized) ?? $normalized;
                }
            }
        }

        return $this->tokenize($normalized);
    }

    private function skillSimilarity(array $candidateSkills, array $jobSkills): int
    {
        $candidate = $this->aliasNormalizeSet($candidateSkills);
        $job = $this->aliasNormalizeSet($jobSkills);

        if ($candidate === [] || $job === []) {
            return 50;
        }

        $intersection = count(array_intersect_key($candidate, $job));
        $union = count(array_unique(array_merge(array_keys($candidate), array_keys($job))));

        return $union > 0 ? (int) round(($intersection / $union) * 100) : 50;
    }

    /**
     * Which job skills the candidate's profile covers (alias-aware), for
     * evidence and reasons. Returns matched pairs plus human phrasings.
     */
    private function skillEvidence(array $candidateSkills, array $jobSkills): array
    {
        if ($candidateSkills === [] || $jobSkills === []) {
            return [[], []];
        }

        $candidateAliases = $this->buildAliases($candidateSkills);

        $matches = [];
        $reasons = [];

        foreach ($jobSkills as $jobSkill) {
            $jobNormalized = $this->aliasNormalize((string) $jobSkill);
            $jobLower = mb_strtolower(trim((string) $jobSkill));

            if (in_array($jobNormalized, $candidateAliases, true)) {
                $candidateOriginal = $this->firstOriginalFor($candidateSkills, $jobNormalized);

                $matches[] = [
                    'candidate' => $candidateOriginal,
                    'job' => (string) $jobSkill,
                    'alias' => $candidateOriginal !== null && mb_strtolower($candidateOriginal) !== $jobLower,
                ];

                if ($candidateOriginal !== null && mb_strtolower($candidateOriginal) !== $jobLower) {
                    $reasons[] = $this->aliasReason($candidateOriginal, (string) $jobSkill);
                }
            }
        }

        return [$matches, $reasons];
    }

    private function firstOriginalFor(array $candidateSkills, string $canonical): ?string
    {
        foreach ($candidateSkills as $skill) {
            if ($this->aliasNormalize((string) $skill) === $canonical) {
                return (string) $skill;
            }
        }

        return null;
    }

    private function aliasReason(string $candidateSkill, string $jobSkill): string
    {
        return "Your {$candidateSkill} experience matches {$jobSkill} on this listing";
    }

    private function descriptionSimilarity(string $candidateDescription, string $jobDescription): int
    {
        if (mb_strlen($jobDescription) < (int) config('matching.semantic.min_description_chars', 100)) {
            return 50;
        }

        $candidateTokens = $this->descriptionTokens($candidateDescription);
        $jobTokens = $this->descriptionTokens($jobDescription);

        if ($candidateTokens === [] || $jobTokens === []) {
            return 50;
        }

        return $this->jaccard($candidateTokens, $jobTokens);
    }

    private function descriptionTokens(string $text): array
    {
        $stopwords = config('matching.semantic.stopwords', []);

        $tokens = array_values(array_filter(
            array_map(fn ($token) => mb_strtolower(trim($token)), preg_split('/[^a-z0-9+#.]+/i', $text) ?: []),
            fn ($token) => $token !== '' && ! in_array($token, $stopwords, true),
        ));

        return array_values(array_unique($tokens));
    }

    private function domainScore(?string $candidateDomain, ?string $jobDomain): int
    {
        if ($candidateDomain === null || $jobDomain === null) {
            return 50;
        }

        return $candidateDomain === $jobDomain ? 100 : 0;
    }

    private function jaccard(array $a, array $b): int
    {
        $intersection = count(array_intersect($a, $b));
        $union = count(array_unique(array_merge($a, $b)));

        return $union > 0 ? (int) round(($intersection / $union) * 100) : 50;
    }

    /**
     * @return array<string, string>
     */
    private function aliasNormalizeSet(array $skills): array
    {
        $normalized = [];

        foreach ($skills as $skill) {
            $normalized[$this->aliasNormalize((string) $skill)] = true;
        }

        return array_map(fn () => true, $normalized);
    }

    private function buildAliases(array $skills): array
    {
        $aliases = [];

        foreach ($skills as $skill) {
            $aliases[$this->aliasNormalize((string) $skill)] = true;
        }

        return array_keys($aliases);
    }

    /**
     * Normalise a skill name through the configured alias table so different
     * spellings of the same skill compare equal. Distinct skills that happen
     * to share a prefix (Java vs JavaScript) are deliberately NOT aliased.
     */
    private function aliasNormalize(string $skill): string
    {
        $name = mb_strtolower(trim($skill));

        if ($name === '') {
            return '';
        }

        // Apply the longest matching alias exactly once. Single-pass longest
        // matching prevents double rewriting (REST -> REST API -> ...) while
        // keeping distinct skills (Java vs JavaScript) separate.
        $longestAlias = null;
        $longestPattern = null;
        $longestLength = -1;

        foreach (config('matching.semantic.skill_aliases', []) as $alias => $canonical) {
            $pattern = '/(?<![a-z0-9.])'.preg_quote($alias, '/').'(?![a-z0-9])/';

            if (preg_match($pattern, $name) && strlen($alias) > $longestLength) {
                $longestAlias = $canonical;
                $longestPattern = $pattern;
                $longestLength = strlen($alias);
            }
        }

        if ($longestPattern !== null) {
            $name = preg_replace($longestPattern, $longestAlias, $name) ?? $name;
        }

        return trim(preg_replace('/\s+/', ' ', $name) ?? $name);
    }

    private function reasons(
        int $roleScore,
        int $skillScore,
        int $descriptionScore,
        array $skillReasons,
        array $context
    ): array {
        $reasons = [];
        $perspective = $context['perspective'] ?? 'candidate';
        $jobTitle = (string) ($context['job_title'] ?? '');

        if ($roleScore >= 85) {
            $reasons[] = $perspective === 'employer'
                ? 'This candidate has semantically aligned experience for this role'
                : 'Your experience is closely aligned with this role';
        }

        $reasons = array_merge($reasons, array_slice($skillReasons, 0, 2));

        if ($skillScore >= 85 && $skillReasons === []) {
            $reasons[] = $perspective === 'employer'
                ? 'The candidate covers the majority of skills this role requires'
                : 'You cover the majority of the skills this role requires';
        }

        if ($descriptionScore >= 80) {
            $reasons[] = $perspective === 'employer'
                ? 'The candidate background has strong keyword overlap with this role'
                : 'Your background has strong keyword overlap with this role';
        }

        return array_slice($reasons, 0, (int) config('matching.semantic.reasons_max', 3));
    }

    private function containsPhrase(string $haystack, string $needle): bool
    {
        return str_contains($haystack, mb_strtolower(trim($needle)));
    }

    private function replacePhrase(string $haystack, string $needle, string $replacement): string
    {
        return str_replace(mb_strtolower(trim($needle)), $replacement, $haystack);
    }

    private function containsWord(string $haystack, string $word): bool
    {
        return (bool) preg_match('/\b'.preg_quote($word, '/').'\b/', $haystack);
    }

    private function tokenize(string $text): array
    {
        $tokens = array_values(array_filter(
            array_map(fn ($token) => mb_strtolower(trim((string) $token)), preg_split('/[^a-z0-9+#]+/i', $text) ?: []),
            fn ($token) => $token !== '',
        ));

        return array_values(array_unique($tokens));
    }
}

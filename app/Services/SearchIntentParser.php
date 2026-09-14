<?php

namespace App\Services;

use App\Models\Skill;

/**
 * Deterministic search intent parser.
 *
 * Turns a free-text search query into a normalised signal payload (domain,
 * role phrase, skills, seniority, location, work arrangement) using only
 * configured vocabularies and the professional domain taxonomy. No AI, no
 * external services - repeatable and testable.
 */
class SearchIntentParser
{
    /**
     * Parse a candidate search query plus applied filters.
     *
     * @return array{
     *     query: string,
     *     normalized_query: string,
     *     domain: ?string,
     *     role_phrase: ?string,
     *     skills: array,
     *     seniority: ?string,
     *     location: ?string,
     *     work_preference: ?string,
     *     filters: array
     * }
     */
    public function parse(string $query, array $filters = []): array
    {
        $normalized = $this->normalize($query);

        $workPreference = $this->detectWorkArrangement($normalized, $filters);
        $location = $this->detectLocation($normalized, $filters);
        $seniority = $this->detectSeniority($normalized);
        $skills = $this->detectSkills($normalized);

        $rolePhrase = $this->extractRolePhrase(
            $normalized,
            $workPreference,
            $location,
            $seniority,
            $skills
        );

        return [
            'query' => $query,
            'normalized_query' => $normalized,
            'domain' => $this->detectDomain($rolePhrase !== '' ? $rolePhrase : $normalized),
            'role_phrase' => $rolePhrase !== '' ? $rolePhrase : null,
            'skills' => $skills,
            'seniority' => $seniority,
            'location' => $location,
            'work_preference' => $workPreference,
            'filters' => $this->cleanFilters($filters),
        ];
    }

    public function normalize(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $value = mb_strtolower(trim($value));
        $value = preg_replace('/[^\p{L}\p{N}\s\-]/u', '', $value) ?? $value;

        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }

    /**
     * Determine the professional domain of a role string using the same
     * config/professional_domains.php taxonomy as the matching engine.
     */
    public function detectDomain(?string $role): ?string
    {
        if ($role === null || trim($role) === '') {
            return null;
        }

        $normalized = $this->normalize($role);
        $domains = config('professional_domains.domains', []);

        $scores = [];

        foreach ($domains as $key => $domain) {
            $score = 0;

            foreach ($domain['keywords'] as $keyword) {
                if (str_contains($normalized, $keyword)) {
                    $score += str_word_count($keyword) >= 2 ? 3 : 1;
                }
            }

            if ($score > 0) {
                $scores[$key] = $score;
            }
        }

        if ($scores === []) {
            return null;
        }

        arsort($scores);
        $topDomain = array_key_first($scores);
        $topScore = $scores[$topDomain];

        $secondScore = 0;

        foreach ($scores as $key => $score) {
            if ($key !== $topDomain) {
                $secondScore = $score;
                break;
            }
        }

        return $topScore > $secondScore ? $topDomain : null;
    }

    public function signalKey(?string $value): ?string
    {
        $normalized = $this->normalize($value);

        return $normalized !== '' ? $normalized : null;
    }

    private function detectWorkArrangement(string $normalized, array $filters = []): ?string
    {
        $filtersValue = $filters['work_preference'] ?? null;

        if ($filtersValue !== null && in_array($filtersValue, config('matching.behavioral.work_arrangements', []), true)) {
            return $filtersValue;
        }

        foreach (config('matching.behavioral.work_arrangements', []) as $arrangement) {
            if (str_contains($normalized, $arrangement)) {
                return $arrangement;
            }
        }

        return null;
    }

    private function detectLocation(string $normalized, array $filters = []): ?string
    {
        $filtersValue = $filters['location'] ?? null;

        if ($filtersValue !== null && trim($filtersValue) !== '') {
            return $this->normalize($filtersValue) !== '' ? $this->normalize($filtersValue) : null;
        }

        foreach (config('matching.behavioral.locations', []) as $location) {
            if (str_contains($normalized, $location)) {
                return $location;
            }
        }

        return null;
    }

    private function detectSeniority(string $normalized): ?string
    {
        foreach (config('matching.behavioral.seniority_keywords', []) as $keyword => $label) {
            if (str_contains($normalized, $keyword)) {
                return $label;
            }
        }

        return null;
    }

    /**
     * Match free-text against the known Skill vocabulary (longest match wins).
     */
    private function detectSkills(string $normalized): array
    {
        $names = Skill::query()
            ->where('is_active', true)
            ->whereNotNull('name')
            ->pluck('name')
            ->filter(fn ($name) => $this->signalKey($name) !== null && $this->signalKey($name) !== '')
            ->sortByDesc(fn ($name) => strlen((string) $name))
            ->values();

        $found = [];

        foreach ($names as $name) {
            $key = $this->signalKey($name);

            if ($key === null || str_contains($normalized, $key)) {
                $found[] = (string) $name;
            }
        }

        return array_values(array_unique($found));
    }

    private function extractRolePhrase(
        string $normalized,
        ?string $workPreference,
        ?string $location,
        ?string $seniority,
        array $skills
    ): string {
        $remove = array_merge(
            $workPreference !== null ? [$workPreference] : [],
            $location !== null ? [$location] : [],
            $seniority !== null ? [$seniority, strtolower($seniority)] : [],
            array_map(fn ($skill) => $this->signalKey($skill) ?? $skill, $skills),
        );

        $phrase = $normalized;

        foreach ($remove as $token) {
            $phrase = str_replace($token, ' ', $phrase);
        }

        return $this->normalize($phrase);
    }

    /**
     * Persist only the search filters that meaningfully narrow results.
     */
    private function cleanFilters(array $filters): array
    {
        $allowed = [
            'keyword',
            'industry',
            'employment_type',
            'work_preference',
            'salary_min',
            'salary_max',
            'experience_min',
            'skills',
            'location',
        ];

        $clean = [];

        foreach ($allowed as $key) {
            if (! array_key_exists($key, $filters) || $filters[$key] === null || $filters[$key] === '') {
                continue;
            }

            if (is_array($filters[$key])) {
                $clean[$key] = array_values(array_filter($filters[$key], fn ($value) => $value !== null && trim((string) $value) !== ''));
            } else {
                $clean[$key] = $filters[$key];
            }
        }

        return $clean;
    }
}

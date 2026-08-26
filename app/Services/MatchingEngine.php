<?php

namespace App\Services;

use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Legacy facade kept for existing dashboards.
 *
 * All scoring is delegated to the canonical JobMatchingService; this class
 * preserves the historical return shapes (total_score / breakdown arrays)
 * consumed by CandidateDashboardController, CandidateRecommendationController,
 * EmployerMarketplaceController, EmployerPipelineController and
 * EmployerJobCandidateController.
 */
class MatchingEngine
{
    public function __construct(protected JobMatchingService $engine) {}

    public function calculateMatch(User $candidate, Job $job): array
    {
        $breakdown = $this->engine->calculateBreakdown($candidate, $job);

        return [
            'total_score' => $breakdown['overall_score'],
            'breakdown' => [
                'skills' => $breakdown['components']['skills']['score'],
                'experience' => $breakdown['components']['experience']['score'],
                'location' => $breakdown['components']['work_preference']['score'],
                'salary' => $breakdown['components']['salary']['score'],
                'personality' => $breakdown['components']['personality']['score'],
                'temperament' => $this->engine->temperamentScore($candidate, $job),
                'trust' => $this->calculateTrustMatch($candidate),
            ],
        ];
    }

    public function calculateTrustMatch(User $candidate): int
    {
        $trustScore = $candidate->trustScore;

        return $trustScore ? $trustScore->score : 0;
    }

    public function getTopMatchingJobs(User $candidate, int $limit = 10): Collection
    {
        $recommendations = $this->engine->recommendJobsForCandidate($candidate, [], max($limit, 1));

        return collect($recommendations->items())
            ->take($limit)
            ->map(function (array $item) {
                $components = $item['breakdown']['components'] ?? [];

                return [
                    'job' => $item['job'],
                    'match_percentage' => $item['overall_score'],
                    'category' => $item['category'],
                    'matched_skills' => $item['matched_skills'],
                    'missing_skills' => $item['missing_skills'],
                    'top_reasons' => $item['top_reasons'],
                    'skill_score' => $components['skills']['score'] ?? 0,
                    'experience_score' => $components['experience']['score'] ?? 0,
                    'personality_score' => $components['personality']['score'] ?? 0,
                    'temperament_score' => $components['work_preference']['score'] ?? 0,
                ];
            })
            ->values();
    }

    public function getTopMatchingCandidates(Job $job, int $limit = 10): Collection
    {
        $ranked = $this->engine->rankCandidatesForJob($job, [], max($limit, 1));

        return collect($ranked->items())
            ->take($limit)
            ->map(fn (array $item) => [
                'candidate' => $item['candidate'],
                'match_percentage' => $item['overall_score'],
                'category' => $item['category'],
                'matched_skills' => $item['matched_skills'],
                'missing_skills' => $item['missing_skills'],
                'strengths' => $item['strengths'],
            ])
            ->values();
    }
}

<?php

namespace App\Services;

use App\Models\Job;
use App\Models\JobMatchScore;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Persists candidate-to-job match scores.
 *
 * Scoring is delegated to the canonical JobMatchingService; this service owns
 * the job_match_scores storage strategy - one upserted "latest" row per
 * candidate/job pair containing the full component breakdown.
 */
class MatchingEngineService
{
    public function __construct(protected JobMatchingService $engine) {}

    /**
     * Legacy four-component shape, preserved for existing callers.
     */
    public function calculateMatch(User $candidate, Job $job): array
    {
        $breakdown = $this->engine->calculateBreakdown($candidate, $job);

        return [
            'skills_fit_score' => $breakdown['components']['skills']['score'],
            'personality_fit_score' => $breakdown['components']['personality']['score'],
            'culture_fit_score' => $this->engine->cultureScore($candidate, $job),
            'temperament_fit_score' => $this->engine->temperamentScore($candidate, $job),
            'overall_match_score' => $breakdown['overall_score'],
        ];
    }

    public function saveMatch(User $candidate, Job $job): JobMatchScore
    {
        $breakdown = $this->engine->calculateBreakdown($candidate, $job);

        return JobMatchScore::updateOrCreate(
            [
                'candidate_id' => $candidate->id,
                'job_id' => $job->id,
            ],
            [
                'skills_fit_score' => $breakdown['components']['skills']['score'],
                'personality_fit_score' => $breakdown['components']['personality']['score'],
                'culture_fit_score' => $this->engine->cultureScore($candidate, $job),
                'temperament_fit_score' => $this->engine->temperamentScore($candidate, $job),
                'overall_match_score' => $breakdown['overall_score'],

                'skill_score' => $breakdown['components']['skills']['score'],
                'role_score' => $breakdown['components']['role']['score'],
                'experience_score' => $breakdown['components']['experience']['score'],
                'personality_score' => $breakdown['components']['personality']['score'],
                'work_preference_score' => $breakdown['components']['work_preference']['score'],
                'salary_score' => $breakdown['components']['salary']['score'],
                'education_score' => $breakdown['components']['education']['score'],
                'availability_score' => $breakdown['components']['availability']['score'],

                'matched_skills' => $breakdown['matched_skills'],
                'missing_skills' => $breakdown['missing_skills'],
                'strengths' => $breakdown['strengths'],
                'gaps' => $breakdown['gaps'],
                'reasons' => $breakdown['reasons'],

                'scored_at' => now(),
                'is_latest' => true,
            ]
        );
    }

    public function getTopMatchingJobs(User $candidate, int $limit = 10): Collection
    {
        $recommendations = $this->engine->recommendJobsForCandidate($candidate, [], max($limit, 1));

        return collect($recommendations->items())
            ->take($limit)
            ->map(fn (array $item) => [
                'job' => $item['job'],
                'overall_match_score' => $item['overall_score'],
                'category' => $item['category'],
                'matched_skills' => $item['matched_skills'],
                'top_reasons' => $item['top_reasons'],
            ])
            ->values();
    }

    public function getTopMatchingCandidates(Job $job, int $limit = 10): Collection
    {
        $ranked = $this->engine->rankCandidatesForJob($job, [], max($limit, 1));

        return collect($ranked->items())
            ->take($limit)
            ->map(fn (array $item) => [
                'candidate' => $item['candidate'],
                'overall_match_score' => $item['overall_score'],
                'category' => $item['category'],
                'matched_skills' => $item['matched_skills'],
                'missing_skills' => $item['missing_skills'],
                'strengths' => $item['strengths'],
            ])
            ->values();
    }

    public function recalculateForCandidate(User $candidate): void
    {
        Job::query()
            ->where('status', 'open')
            ->where('employer_id', '!=', $candidate->id)
            ->get()
            ->each(fn (Job $job) => $this->saveMatch($candidate, $job));
    }

    public function recalculateForJob(Job $job): void
    {
        if ($job->status !== 'open') {
            return;
        }

        User::query()
            ->where('role', 'candidate')
            ->where('onboarding_completed', true)
            ->each(fn (User $candidate) => $this->saveMatch($candidate, $job));
    }
}

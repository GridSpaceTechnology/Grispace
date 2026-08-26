<?php

namespace App\Services;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\MatchProfile;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Application-level match snapshots.
 *
 * Scoring is delegated to the canonical JobMatchingService so there is a
 * single matching algorithm; this class preserves the per-application
 * MatchProfile persistence API used by ApplicationService, TalentController
 * and MarketplaceController.
 */
class MatchService
{
    public function __construct(protected JobMatchingService $engine) {}

    public function calculateMatchScore(User $candidate, Job $job): int
    {
        return $this->engine->overall($candidate, $job);
    }

    public function calculateFullMatchData(User $candidate, Job $job): array
    {
        $breakdown = $this->engine->calculateBreakdown($candidate, $job);

        $skills = $breakdown['components']['skills'];
        $experience = $breakdown['components']['experience'];

        return [
            'overall_score' => $breakdown['overall_score'],
            'skill_score' => $skills['score'],
            'experience_score' => $experience['score'],
            'salary_score' => $breakdown['components']['salary']['score'],
            'work_preference_score' => $breakdown['components']['work_preference']['score'],
            'personality_score' => $breakdown['components']['personality']['score'],
            'education_score' => $breakdown['components']['education']['score'],
            'availability_score' => $breakdown['components']['availability']['score'],
            'matched_skills' => $skills['details']['matched'] ?? [],
            'missing_skills' => $skills['details']['missing'] ?? [],
            'critical_missing' => collect($skills['details']['missing'] ?? [])
                ->where('required', true)
                ->values()
                ->all(),
            'requirements_met' => $experience['details']['requirements_met'] ?? [],
            'requirements_missing' => $experience['details']['requirements_missing'] ?? [],
        ];
    }

    public function saveMatchProfile(JobApplication $application): MatchProfile
    {
        $matchData = $this->calculateFullMatchData($application->candidate, $application->job);

        $application->matchProfiles()->update(['is_latest' => false]);

        return MatchProfile::create([
            'application_id' => $application->id,
            'overall_score' => $matchData['overall_score'],
            'skill_score' => $matchData['skill_score'],
            'experience_score' => $matchData['experience_score'],
            'salary_score' => $matchData['salary_score'],
            'work_preference_score' => $matchData['work_preference_score'],
            'personality_score' => $matchData['personality_score'],
            'education_score' => $matchData['education_score'],
            'availability_score' => $matchData['availability_score'],
            'matched_skills' => $matchData['matched_skills'],
            'missing_skills' => $matchData['missing_skills'],
            'matched_requirements' => $matchData['requirements_met'] ?? [],
            'missing_requirements' => $matchData['requirements_missing'] ?? [],
            'scored_at' => now(),
            'is_latest' => true,
        ]);
    }

    public function recalculateMatchProfile(JobApplication $application): MatchProfile
    {
        return $this->saveMatchProfile($application);
    }

    public function getTopMatchingJobs(User $candidate, int $limit = 10): Collection
    {
        $recommendations = $this->engine->recommendJobsForCandidate($candidate, [], max($limit, 1));

        return collect($recommendations->items())
            ->take($limit)
            ->map(fn (array $item) => [
                'job' => $item['job'],
                'match_percentage' => $item['overall_score'],
                'category' => $item['category'],
                'matched_skills' => $item['matched_skills'],
                'missing_skills' => $item['missing_skills'],
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
                'match_percentage' => $item['overall_score'],
                'category' => $item['category'],
                'matched_skills' => $item['matched_skills'],
                'missing_skills' => $item['missing_skills'],
            ])
            ->values();
    }
}

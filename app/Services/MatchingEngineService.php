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
 * candidate/job pair containing the full component breakdown plus the
 * algorithm version, a deterministic data checksum and an expiry time used
 * to detect stale matches.
 */
class MatchingEngineService
{
    public function __construct(
        protected JobMatchingService $engine,
        protected MatchChecksumService $checksums,
    ) {}

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
        // Score from persisted state so the stored breakdown matches what a
        // later freshMatch would see (in-memory models may hold pre-default
        // attribute values immediately after mass-assignment).
        $candidate = $candidate->refresh();
        $job = $job->refresh();

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
                'recommendation_score' => $breakdown['recommendation_score'],
                'match_status' => $breakdown['match_status'],

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

                'algorithm_version' => (int) config('matching.algorithm_version', 0),
                'data_checksum' => $this->checksums->for($candidate, $job),
                'scored_at' => now(),
                'expires_at' => now()->addHours((int) config('matching.staleness.ttl_hours', 24)),
                'is_latest' => true,
            ]
        );
    }

    /**
     * Return the persisted latest score for a candidate/job pair when it is
     * still usable, or null when the version, checksum or expiry say it is
     * stale and must be recomputed.
     */
    public function freshMatch(User $candidate, Job $job): ?JobMatchScore
    {
        $row = JobMatchScore::where('candidate_id', $candidate->id)
            ->where('job_id', $job->id)
            ->where('is_latest', true)
            ->first();

        if (! $row) {
            return null;
        }

        if ((int) $row->algorithm_version !== (int) config('matching.algorithm_version', 0)) {
            return null;
        }

        if ($row->expires_at !== null && $row->expires_at->isPast()) {
            return null;
        }

        $checksum = $this->checksums->for($candidate, $job);

        if ($row->data_checksum === null || ! hash_equals($row->data_checksum, $checksum)) {
            return null;
        }

        return $row;
    }

    public function getTopMatchingJobs(User $candidate, int $limit = 10): Collection
    {
        $recommendations = $this->engine->recommendJobsForCandidate($candidate, [], max($limit, 1));

        return collect($recommendations->items())
            ->take($limit)
            ->map(fn (array $item) => [
                'job' => $item['job'],
                'overall_match_score' => $item['profile_match_score'],
                'recommendation_score' => $item['recommendation_score'],
                'match_status' => $item['match_status'],
                'category' => $item['category'],
                'matched_skills' => $item['matched_skills'],
                'top_reasons' => $item['top_reasons'],
            ])
            ->values();
    }

    /**
     * Employer-facing matching candidates, preferring freshly persisted scores
     * when staleness.use_persisted_matches is enabled so large pools never pay
     * to score every candidate on every request. Rows that are stale by
     * version, checksum or expiry are discarded and topped up from live
     * scoring when fallbackToLive is set.
     */
    public function getCachedTopMatchingCandidates(Job $job, int $limit = 10, bool $fallbackToLive = true): Collection
    {
        $limit = max($limit, 1);

        $rows = collect();

        if ((bool) config('matching.staleness.use_persisted_matches', true)) {
            $rows = JobMatchScore::query()
                ->where('job_id', $job->id)
                ->where('is_latest', true)
                ->where('algorithm_version', (int) config('matching.algorithm_version', 0))
                ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->orderByDesc('overall_match_score')
                ->take($limit)
                ->with(['candidate.candidateProfile'])
                ->get();
        }

        $maps = $rows->map(fn (JobMatchScore $row) => [
            'candidate' => $row->candidate,
            'match_percentage' => (int) $row->overall_match_score,
            'category' => $this->engine->categoryFor((int) $row->overall_match_score),
            'match_status' => (string) $row->match_status,
            'match_score' => (int) $row->overall_match_score,
            'matched_skills' => $row->matched_skills ?? [],
            'missing_skills' => $row->missing_skills ?? [],
            'strengths' => $row->strengths ?? [],
            'from_cache' => true,
        ]);

        if ($maps->count() < $limit && $fallbackToLive) {
            $ranked = $this->getTopMatchingCandidates($job, $limit);

            $excludedIds = $maps->pluck('candidate.id')->all();

            $topup = $ranked
                ->reject(fn (array $item) => in_array($item['candidate']->id, $excludedIds, true))
                ->take($limit - $maps->count());

            $maps = $maps->concat($topup);
        }

        return $maps->take($limit)->values();
    }

    public function getTopMatchingCandidates(Job $job, int $limit = 10): Collection
    {
        $ranked = $this->engine->rankCandidatesForJob($job, [], max($limit, 1));

        return collect($ranked->items())
            ->take($limit)
            ->map(fn (array $item) => [
                'candidate' => $item['candidate'],
                'overall_match_score' => $item['overall_score'],
                'match_percentage' => $item['overall_score'],
                'match_score' => $item['overall_score'],
                'match_status' => $item['match_status'],
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
            ->chunkById(200, function ($jobs) use ($candidate) {
                foreach ($jobs as $job) {
                    $this->saveMatch($candidate, $job);
                }
            });
    }

    public function recalculateForJob(Job $job): void
    {
        if ($job->status !== 'open') {
            return;
        }

        User::query()
            ->where('role', 'candidate')
            ->where('onboarding_completed', true)
            ->chunkById(200, function ($candidates) use ($job) {
                foreach ($candidates as $candidate) {
                    $this->saveMatch($candidate, $job);
                }
            });
    }
}

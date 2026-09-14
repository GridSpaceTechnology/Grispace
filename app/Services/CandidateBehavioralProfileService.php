<?php

namespace App\Services;

use App\Enums\CandidateJobInteractionType;
use App\Models\CandidateBehavioralProfile;
use App\Models\CandidateJobInteraction;
use App\Models\CandidateSearchHistory;
use App\Models\Job;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Candidate behavioral intelligence.
 *
 * Records candidate searches, job views, saves and applications into a
 * private per-candidate profile and derives a behavioral "relevance" score
 * used to gently re-rank professionally compatible jobs. Behaviour is a
 * ranking modifier only - it never overrides the professional domain gate
 * and is never exposed to employers. Recording is synchronous; there is no
 * queue worker dependency.
 */
class CandidateBehavioralProfileService
{
    public function __construct(protected SearchIntentParser $parser) {}

    public function recordSearch(User $user, string $query, array $filters = []): void
    {
        if (! $this->enabled() || trim($query) === '') {
            return;
        }

        $intent = $this->parser->parse($query, $filters);

        CandidateSearchHistory::create([
            'user_id' => $user->id,
            'query' => $intent['query'],
            'normalized_query' => $intent['normalized_query'] ?: null,
            'domain' => $intent['domain'],
            'detected_role' => $intent['role_phrase'],
            'skills' => $intent['skills'] ?: null,
            'seniority' => $intent['seniority'],
            'location' => $intent['location'],
            'work_preference' => $intent['work_preference'],
            'filters' => $intent['filters'] ?: null,
        ]);

        $this->aggregate($user, [
            'type' => 'search',
            'domain' => $intent['domain'],
            'role' => $intent['role_phrase'],
            'skills' => $intent['skills'],
            'location' => $intent['location'],
            'work_preference' => $intent['work_preference'],
        ]);
    }

    /**
     * Record a job view. Repeated views of the same job within the dedup
     * window refresh recency but are not counted twice.
     */
    public function recordView(User $user, Job $job): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        $minutes = (int) config('matching.behavioral.view_dedup_minutes', 30);

        $recent = CandidateJobInteraction::query()
            ->where('user_id', $user->id)
            ->where('job_id', $job->id)
            ->where('interaction_type', CandidateJobInteractionType::View)
            ->where('acted_at', '>=', now()->subMinutes($minutes))
            ->first();

        if ($recent) {
            $recent->update(['acted_at' => now()]);
            $this->aggregate($user, [
                'type' => 'view_touch',
                'job_id' => $job->id,
            ]);

            return false;
        }

        CandidateJobInteraction::create([
            'user_id' => $user->id,
            'job_id' => $job->id,
            'interaction_type' => CandidateJobInteractionType::View,
            'acted_at' => now(),
        ]);

        $this->aggregate($user, [
            'type' => 'view',
            'domain' => $this->jobDomain($job),
            'role' => $job->title ?? null,
            'skills' => $job->getRequiredSkills(),
            'job_id' => $job->id,
        ]);

        return true;
    }

    public function recordSave(User $user, Job $job): void
    {
        if (! $this->enabled()) {
            return;
        }

        CandidateJobInteraction::updateOrCreate(
            [
                'user_id' => $user->id,
                'job_id' => $job->id,
                'interaction_type' => CandidateJobInteractionType::Save,
            ],
            ['acted_at' => now()],
        );

        $this->aggregate($user, [
            'type' => 'save',
            'domain' => $this->jobDomain($job),
            'role' => $job->title ?? null,
            'skills' => $job->getRequiredSkills(),
            'job_id' => $job->id,
        ]);
    }

    public function recordApply(User $user, Job $job): void
    {
        if (! $this->enabled()) {
            return;
        }

        $this->aggregate($user, [
            'type' => 'apply',
            'domain' => $this->jobDomain($job),
            'role' => $job->title ?? null,
            'skills' => $job->getRequiredSkills(),
            'job_id' => $job->id,
        ]);
    }

    /**
     * Compute behavioral relevance (0-100) for a job against a candidate's
     * private behavioral profile.
     */
    public function behavioralRelevance(User $user, Job $job): int
    {
        $profile = $user->behavioralProfile;

        return $profile ? $this->relevanceFromProfile($profile, $job) : 0;
    }

    public function behavioralBoost(User $user, Job $job, array $breakdown): int
    {
        $profile = $user->behavioralProfile;

        if (! $profile) {
            return 0;
        }

        [, $boost] = $this->forRecommendation($profile, $job, $breakdown);

        return $boost;
    }

    public function behavioralReasons(User $user, Job $job, array $breakdown): array
    {
        $profile = $user->behavioralProfile;

        if (! $profile) {
            return [];
        }

        [, , $reasons] = $this->forRecommendation($profile, $job, $breakdown);

        return $reasons;
    }

    /**
     * Ranking-layer hook used by the matching engine. Returns
     * [relevance, boost, reasons] without re-querying the candidate.
     *
     * @return array{0: int, 1: int, 2: array}
     */
    public function forRecommendation(?CandidateBehavioralProfile $profile, Job $job, array $breakdown): array
    {
        if (! $this->profileActive($profile)) {
            return [0, 0, []];
        }

        $relevance = $this->relevanceFromProfile($profile, $job);
        $boost = $breakdown['domain_compatible'] === false
            ? 0
            : $this->boostForRelevance($relevance);
        $reasons = $boost > 0 ? $this->reasonsFromProfile($profile, $job) : [];

        return [$relevance, $boost, $reasons];
    }

    /**
     * Scale a relevance score into ranking boost points, capped.
     */
    public function boostForRelevance(int $relevance): int
    {
        if ($relevance <= 0) {
            return 0;
        }

        $max = (float) config('matching.behavioral.boost.max_points', 12);

        return (int) round(min($max, $relevance / 100 * $max));
    }

    /**
     * Maintenance helper: rebuild a candidate's behavioral profile from the
     * raw event logs so it stays consistent even if the aggregate row was
     * lost or altered.
     */
    public function rebuild(User $user): CandidateBehavioralProfile
    {
        $profile = $this->profileFor($user);

        $profile->forceFill([
            'role_signals' => [],
            'domain_signals' => [],
            'skill_signals' => [],
            'work_preference_signals' => [],
            'location_signals' => [],
            'viewed_jobs' => [],
            'applied_jobs' => [],
            'saved_jobs' => [],
            'total_events' => 0,
            'last_activity_at' => null,
        ]);

        $totalEvents = 0;

        foreach ($user->searchHistories()->orderBy('created_at')->get() as $history) {
            $this->applyEvent($profile, [
                'type' => 'search',
                'domain' => $history->domain,
                'role' => $history->detected_role,
                'skills' => $history->skills ?? [],
                'location' => $history->location,
                'work_preference' => $history->work_preference,
            ], $history->created_at);

            $totalEvents++;
        }

        $user->jobInteractions()
            ->orderBy('acted_at')
            ->get()
            ->each(function (CandidateJobInteraction $interaction) use ($profile, &$totalEvents) {
                $skillNames = $interaction->job?->getRequiredSkills() ?? [];

                $this->applyEvent($profile, [
                    'type' => $interaction->interaction_type->value,
                    'domain' => $interaction->job ? $this->jobDomain($interaction->job) : null,
                    'role' => $interaction->job?->title,
                    'skills' => $skillNames,
                    'job_id' => $interaction->job_id,
                ], $interaction->acted_at);

                $totalEvents++;
            });

        $user->jobApplications()
            ->whereNotNull('applied_at')
            ->orderBy('applied_at')
            ->get()
            ->each(function ($application) use ($profile, &$totalEvents) {
                $job = $application->job;

                $this->applyEvent($profile, [
                    'type' => 'apply',
                    'domain' => $job ? $this->jobDomain($job) : null,
                    'role' => $job?->title,
                    'skills' => $job?->getRequiredSkills() ?? [],
                    'job_id' => $application->job_id,
                ], $application->applied_at);

                $totalEvents++;
            });

        $profile->total_events = $totalEvents;
        $profile->last_activity_at = $user->searchHistories()
            ->orderByDesc('created_at')
            ->value('created_at')
            ?? $user->jobInteractions()->orderByDesc('acted_at')->value('acted_at')
            ?? $user->jobApplications()->orderByDesc('applied_at')->value('applied_at');
        $profile->save();

        $user->setRelation('behavioralProfile', $profile);

        return $profile;
    }

    public function profileFor(User $user): CandidateBehavioralProfile
    {
        if ($user->relationLoaded('behavioralProfile') && $user->behavioralProfile !== null) {
            return $user->behavioralProfile;
        }

        $profile = $user->behavioralProfile()->first()
            ?? CandidateBehavioralProfile::create([
                'user_id' => $user->id,
                'role_signals' => [],
                'domain_signals' => [],
                'skill_signals' => [],
                'work_preference_signals' => [],
                'location_signals' => [],
                'viewed_jobs' => [],
                'applied_jobs' => [],
                'saved_jobs' => [],
                'total_events' => 0,
            ]);

        $user->setRelation('behavioralProfile', $profile);

        return $profile;
    }

    // ------------------------------------------------------------------
    // Aggregation
    // ------------------------------------------------------------------

    private function aggregate(User $user, array $event): void
    {
        $profile = $this->profileFor($user);

        $this->applyEvent($profile, $event, now());

        $profile->total_events++;
        $profile->last_activity_at = now();
        $profile->save();
    }

    private function applyEvent(CandidateBehavioralProfile $profile, array $event, CarbonInterface $at): void
    {
        $type = $event['type'];
        $weight = (float) config("matching.behavioral.event_weights.{$type}", 0);

        if ($weight > 0 || $type === 'view_touch') {
            $role = $this->parser->signalKey($event['role'] ?? null);

            if ($role !== null) {
                $signals = $profile->role_signals ?? [];
                $signals[$role] = $this->bump($signals[$role] ?? null, $weight, $at);
                $profile->role_signals = $signals;
            }

            if (! empty($event['domain'])) {
                $signals = $profile->domain_signals ?? [];
                $signals[$event['domain']] = $this->bump($signals[$event['domain']] ?? null, $weight, $at);
                $profile->domain_signals = $signals;
            }

            foreach ($event['skills'] ?? [] as $skill) {
                $key = $this->parser->signalKey($skill);

                if ($key !== null) {
                    $signals = $profile->skill_signals ?? [];
                    $signals[$key] = $this->bump($signals[$key] ?? null, $weight, $at);
                    $profile->skill_signals = $signals;
                }
            }

            if (! empty($event['location'])) {
                $signals = $profile->location_signals ?? [];
                $signals[$event['location']] = $this->bump($signals[$event['location']] ?? null, $weight, $at);
                $profile->location_signals = $signals;
            }

            if (! empty($event['work_preference'])) {
                $signals = $profile->work_preference_signals ?? [];
                $signals[$event['work_preference']] = $this->bump($signals[$event['work_preference']] ?? null, $weight, $at);
                $profile->work_preference_signals = $signals;
            }
        }

        $jobId = isset($event['job_id']) ? (int) $event['job_id'] : null;

        if ($jobId !== null && in_array($type, ['view', 'save', 'apply'], true)) {
            $mapName = match ($type) {
                'view' => 'viewed_jobs',
                'save' => 'saved_jobs',
                'apply' => 'applied_jobs',
            };

            $map = $profile->$mapName ?? [];
            $key = (string) $jobId;
            $entry = $map[$key] ?? null;
            $map[$key] = [
                'count' => ($entry['count'] ?? 0) + 1,
                'last_at' => $at->toDateTimeString(),
            ];
            $profile->$mapName = $map;
        }

        if ($jobId !== null && $type === 'view_touch') {
            $map = $profile->viewed_jobs ?? [];
            $key = (string) $jobId;
            $entry = $map[$key] ?? null;

            if ($entry !== null) {
                $map[$key] = [
                    'count' => (int) ($entry['count'] ?? 1),
                    'last_at' => $at->toDateTimeString(),
                ];
                $profile->viewed_jobs = $map;
            }
        }
    }

    private function bump(?array $entry, float $weight, CarbonInterface $at): array
    {
        $decayed = $entry
            ? $this->decay((float) $entry['interest'], $this->parseTime($entry['updated_at'] ?? null))
            : 0.0;

        $interest = min((float) config('matching.behavioral.interest_cap', 100), $decayed + max(0, $weight));

        return [
            'interest' => $interest,
            'updated_at' => $at->toDateTimeString(),
        ];
    }

    private function decay(float $interest, ?CarbonInterface $actedAt): float
    {
        $halfLife = (float) config('matching.behavioral.half_life_days', 30);

        if ($actedAt === null || $halfLife <= 0 || $interest <= 0) {
            return $interest;
        }

        $elapsedDays = max(0, now()->getTimestamp() - $actedAt->getTimestamp()) / 86400;

        return $interest * pow(0.5, $elapsedDays / $halfLife);
    }

    // ------------------------------------------------------------------
    // Scoring
    // ------------------------------------------------------------------

    public function isActive(User $user): bool
    {
        return $this->profileActive($user->behavioralProfile);
    }

    private function profileActive(?CandidateBehavioralProfile $profile): bool
    {
        if (! $this->enabled() || $profile === null) {
            return false;
        }

        return (int) $profile->total_events >= (int) config('matching.behavioral.min_activity_events', 3);
    }

    private function relevanceFromProfile(CandidateBehavioralProfile $profile, Job $job): int
    {
        $domain = $this->domainInterest($profile, $job);
        $role = $this->roleInterest($profile, $job);
        $skills = $this->skillInterest($profile, $job);
        $jobSpecific = $this->jobSpecificInterest($profile, $job);

        $weights = config('matching.behavioral.relevance_weights', [
            'domain' => 0.35,
            'role' => 0.30,
            'skills' => 0.20,
            'job_specific' => 0.15,
        ]);

        $score = $domain * $weights['domain']
            + $role * $weights['role']
            + $skills * $weights['skills']
            + $jobSpecific * $weights['job_specific'];

        return (int) round(min(100, max(0, $score)));
    }

    private function domainInterest(CandidateBehavioralProfile $profile, Job $job): float
    {
        $jobDomain = $this->jobDomain($job);

        if ($jobDomain === null) {
            return 0;
        }

        return $this->mapInterest($profile->domain_signals ?? [], $jobDomain);
    }

    private function roleInterest(CandidateBehavioralProfile $profile, Job $job): float
    {
        $jobPhrase = $this->parser->normalize(($job->title ?? '').' '.($job->role ?? ''));
        $best = 0.0;

        foreach ($profile->role_signals ?? [] as $key => $entry) {
            if ($this->tokensMatch($key, $jobPhrase)) {
                $best = max($best, $this->interestOf($entry));
            }
        }

        return $best;
    }

    private function skillInterest(CandidateBehavioralProfile $profile, Job $job): float
    {
        $matched = [];

        foreach ($job->getRequiredSkills() as $name) {
            $key = $this->parser->signalKey($name);

            if ($key !== null) {
                $matched[] = $this->mapInterest($profile->skill_signals ?? [], $key);
            }
        }

        $matched = array_values(array_filter($matched, fn ($value) => $value > 0));

        if ($matched === []) {
            return 0;
        }

        return array_sum($matched) / count($matched);
    }

    private function jobSpecificInterest(CandidateBehavioralProfile $profile, Job $job): float
    {
        $best = 0.0;
        $jobId = (int) $job->id;

        foreach ([
            'apply' => 'applied_jobs',
            'save' => 'saved_jobs',
            'view' => 'viewed_jobs',
        ] as $type => $mapName) {
            $weight = (float) config("matching.behavioral.event_weights.{$type}", 0);
            $best = max($best, $this->jobMapInterest($profile->$mapName ?? [], $jobId, $weight));
        }

        return $best;
    }

    private function reasonsFromProfile(CandidateBehavioralProfile $profile, Job $job): array
    {
        $reasons = [];

        $domain = $this->domainInterest($profile, $job);
        if ($domain > 0) {
            $jobDomain = $this->jobDomain($job);
            $label = $jobDomain
                ? config("professional_domains.domains.{$jobDomain}.label", ucfirst($jobDomain))
                : 'the';
            $reasons[] = "You've been exploring {$label} roles";
        }

        if ($this->roleInterest($profile, $job) > 0) {
            $reasons[] = 'Matches roles you have been searching for';
        }

        if ($this->skillInterest($profile, $job) > 0) {
            $reasons[] = 'Uses skills you have shown interest in';
        }

        $jobId = (int) $job->id;

        $maps = [
            'applied_jobs' => "You've applied to this role before",
            'saved_jobs' => "You've saved this role before",
            'viewed_jobs' => "You've viewed this role recently",
        ];

        foreach ($maps as $mapName => $message) {
            if (! empty(($profile->$mapName ?? [])[(string) $jobId])) {
                $reasons[] = $message;
                break;
            }
        }

        return array_slice($reasons, 0, 3);
    }

    // ------------------------------------------------------------------
    // Small helpers
    // ------------------------------------------------------------------

    private function enabled(): bool
    {
        return (bool) config('matching.behavioral.enabled', true);
    }

    private function jobDomain(Job $job): ?string
    {
        return $this->parser->detectDomain(trim(($job->title ?? '').' '.($job->role ?? '')));
    }

    private function mapInterest(array $map, string $key): float
    {
        return $this->interestOf($map[$key] ?? null);
    }

    private function interestOf($entry): float
    {
        if (! is_array($entry) || ! isset($entry['interest'])) {
            return 0;
        }

        return $this->decay((float) $entry['interest'], $this->parseTime($entry['updated_at'] ?? null));
    }

    private function jobMapInterest(array $map, int $jobId, float $weight): float
    {
        $entry = $map[(string) $jobId] ?? null;

        if (! is_array($entry) || $weight <= 0) {
            return 0;
        }

        $count = (int) ($entry['count'] ?? 1);

        return $this->decay($weight * max(1, $count), $this->parseTime($entry['last_at'] ?? null));
    }

    private function parseTime(?string $value): ?CarbonInterface
    {
        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }

    private function tokensMatch(string $a, string $b): bool
    {
        $aTokens = array_values(array_filter(explode(' ', $this->parser->normalize($a))));
        $bTokens = array_values(array_filter(explode(' ', $this->parser->normalize($b))));

        if ($aTokens === [] || $bTokens === []) {
            return false;
        }

        return array_intersect($aTokens, $bTokens) !== [];
    }
}

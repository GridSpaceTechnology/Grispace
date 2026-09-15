<?php

use App\Jobs\RecalculateCandidateMatches;
use App\Jobs\RecalculateJobMatches;
use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\Company;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobMatchScore;
use App\Models\User;
use App\Services\CandidateBehavioralProfileService;
use App\Services\JobMatchingService;
use App\Services\MatchingEngineService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldBeUnique;

function phase3Employer(): User
{
    $user = User::factory()->create(['role' => 'employer']);

    Company::create([
        'user_id' => $user->id,
        'name' => 'Acme Ltd',
        'slug' => 'acme-'.str()->random(8),
        'allow_candidate_messages' => true,
    ]);

    return $user;
}

function phase3Job(User $employer, array $attributes = []): Job
{
    return Job::create([
        'employer_id' => $employer->id,
        'title' => 'Senior Backend Engineer',
        'role' => 'Backend Developer',
        'slug' => str()->random(10),
        'employment_type' => 'full_time',
        'work_preference' => 'remote',
        'salary_min' => 500000,
        'salary_max' => 900000,
        'salary_currency' => 'NGN',
        'status' => 'open',
        'required_skills_json' => ['PHP', 'Laravel', 'MySQL', 'Docker'],
        ...$attributes,
    ]);
}

function phase3Candidate(array $profileAttributes = [], array $skills = []): User
{
    $user = User::factory()->create([
        'role' => 'candidate',
        'onboarding_completed' => true,
    ]);

    CandidateProfile::create([
        'user_id' => $user->id,
        'desired_role' => 'Backend Developer',
        'years_of_experience' => 4,
        'salary_expectation' => 700000,
        'work_preference' => 'remote',
        'location_country' => 'Nigeria',
        ...$profileAttributes,
    ]);

    foreach ($skills as $skill) {
        CandidateSkill::create([
            'user_id' => $user->id,
            'skill_name' => $skill,
            'proficiency_level' => 3,
        ]);
    }

    return $user;
}

function phase3RecommendationItems(User $candidate, array $filters = [], int $perPage = 50): array
{
    return app(JobMatchingService::class)
        ->recommendJobsForCandidate($candidate, $filters, $perPage)
        ->items();
}

beforeEach(function () {
    $this->originalPrune = config('matching.recommendation.hard_prune');
    $this->originalStaleness = config('matching.staleness');
    $this->originalVersion = config('matching.algorithm_version');
    $this->originalDedupe = config('matching.recommendation.dedupe_identical_jobs');
    $this->originalDiversity = config('matching.recommendation.diversity_max_per_company');
});

afterEach(function () {
    config([
        'matching.recommendation.hard_prune' => $this->originalPrune,
        'matching.staleness' => $this->originalStaleness,
        'matching.algorithm_version' => $this->originalVersion,
        'matching.recommendation.dedupe_identical_jobs' => $this->originalDedupe,
        'matching.recommendation.diversity_max_per_company' => $this->originalDiversity,
    ]);

    Carbon::setTestNow();
});

it('marks a cross-domain match as incompatible even when the numeric score is middling', function () {
    $engine = app(JobMatchingService::class);
    $candidate = phase3Candidate(['desired_role' => 'Software Engineer'], ['PHP', 'Laravel']);
    $job = phase3Job(phase3Employer(), [
        'title' => 'Accountant',
        'role' => 'Accounting',
        'required_skills_json' => ['Bookkeeping', 'Accounting'],
    ]);

    $breakdown = $engine->calculateBreakdown($candidate, $job);

    expect($breakdown['domain_compatible'])->toBeFalse()
        ->and($breakdown['match_status'])->toBe('incompatible')
        ->and($breakdown['overall_score'])->toBe(config('matching.domain_gate_cap'));
});

it('derives the match status from the profile score using the configured buckets', function () {
    $engine = app(JobMatchingService::class);

    expect($engine->matchStatusFor(95))->toBe('excellent')
        ->and($engine->matchStatusFor(85))->toBe('strong')
        ->and($engine->matchStatusFor(65))->toBe('moderate')
        ->and($engine->matchStatusFor(45))->toBe('weak');
});

it('lets the domain gate override an otherwise strong status', function () {
    $engine = app(JobMatchingService::class);

    expect($engine->matchStatusFor(93, gateApplied: true))->toBe('incompatible');
});

it('keeps profile and recommendation scores equal at the pure breakdown level', function () {
    $engine = app(JobMatchingService::class);
    $candidate = phase3Candidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $job = phase3Job(phase3Employer());

    $breakdown = $engine->calculateBreakdown($candidate, $job);

    expect($breakdown['profile_match_score'])->toBe($breakdown['overall_score'])
        ->and($breakdown['recommendation_score'])->toBe($breakdown['overall_score']);
});

it('boosts only the recommendation score with behavioral interest, never the profile score', function () {
    $behavior = app(CandidateBehavioralProfileService::class);
    $engine = app(JobMatchingService::class);
    $candidate = phase3Candidate();
    $job = phase3Job(phase3Employer());

    $behavior->recordSearch($candidate, 'backend developer');
    $behavior->recordSearch($candidate, 'backend developer');
    $behavior->recordView($candidate, $job);

    $item = collect(phase3RecommendationItems($candidate))->firstWhere('job.id', $job->id);

    expect($item)->not->toBeNull()
        ->and($item['match_status'])->not->toBe('incompatible')
        ->and($item['recommendation_score'])->toBeGreaterThan($item['profile_match_score'])
        ->and($item['profile_match_score'])->toBe($item['overall_score'])
        ->and($item['recommendation_score'])->toBeLessThanOrEqual(100);
});

it('never raises an incompatible match through behavioral interest', function () {
    $behavior = app(CandidateBehavioralProfileService::class);
    $engine = app(JobMatchingService::class);
    $candidate = phase3Candidate(['desired_role' => 'Backend Developer']);

    $accountantJob = phase3Job(phase3Employer(), [
        'title' => 'Accountant',
        'role' => 'Accountant',
        'required_skills_json' => ['Bookkeeping'],
    ]);

    $behavior->recordSearch($candidate, 'accountant jobs');
    $behavior->recordSearch($candidate, 'accountant jobs');
    $behavior->recordSearch($candidate, 'accountant jobs');
    $behavior->recordApply($candidate, $accountantJob);

    $item = collect(phase3RecommendationItems($candidate))->firstWhere('job.id', $accountantJob->id);

    expect($item['match_status'])->toBe('incompatible')
        ->and($item['recommendation_score'])->toBeLessThanOrEqual(config('matching.domain_gate_cap'));
});

it('classifies missing required skills as hard gaps and salary stretch as a soft gap', function () {
    $engine = app(JobMatchingService::class);
    $candidate = phase3Candidate(['salary_expectation' => 8000000], ['PHP']);
    $job = phase3Job(phase3Employer(), [
        'required_skills_json' => ['PHP', 'Laravel', 'MySQL'],
        'salary_min' => 200000,
        'salary_max' => 300000,
    ]);

    $breakdown = $engine->calculateBreakdown($candidate, $job);

    expect($breakdown['hard_gaps'])->toBeArray()
        ->and($breakdown['soft_gaps'])->toBeArray()
        ->and($breakdown['hard_gaps'])->toContain('Missing required skill: Laravel')
        ->and($breakdown['soft_gaps'])->toContain('Top of the advertised range is below your expectation')
        ->and($breakdown['hard_gaps'])->not->toContain('Top of the advertised range is below your expectation');
});

it('prunes jobs that demand more experience than the candidate has', function () {
    $engine = app(JobMatchingService::class);
    $candidate = phase3Candidate(['years_of_experience' => 1], ['PHP', 'Laravel']);

    $reachable = phase3Job(phase3Employer(), ['minimum_experience' => 1, 'required_skills_json' => ['PHP', 'Laravel']]);
    $outOfReach = phase3Job(phase3Employer(), ['minimum_experience' => 8, 'required_skills_json' => ['PHP', 'Laravel']]);

    $ids = collect(phase3RecommendationItems($candidate))->pluck('job.id')->all();

    expect($ids)->toContain($reachable->id)
        ->and($ids)->not->toContain($outOfReach->id);
});

it('prunes hard mismatches of working arrangement before scoring', function () {
    $engine = app(JobMatchingService::class);
    $candidate = phase3Candidate(['work_preference' => 'remote'], ['PHP', 'Laravel']);

    $remoteJob = phase3Job(phase3Employer(), ['work_preference' => 'remote']);
    $onsiteJob = phase3Job(phase3Employer(), ['work_preference' => 'onsite']);

    $ids = collect(phase3RecommendationItems($candidate))->pluck('job.id')->all();

    expect($ids)->toContain($remoteJob->id)
        ->and($ids)->not->toContain($onsiteJob->id);
});

it('prunes on-site roles based in another country for a candidate elsewhere', function () {
    $engine = app(JobMatchingService::class);
    $candidate = phase3Candidate([
        'work_preference' => 'onsite',
        'location_country' => 'Nigeria',
    ], ['PHP', 'Laravel']);

    $localJob = phase3Job(phase3Employer(), ['work_preference' => 'onsite', 'location_country' => 'Nigeria']);
    $abroadJob = phase3Job(phase3Employer(), ['work_preference' => 'onsite', 'location_country' => 'Kenya']);

    $ids = collect(phase3RecommendationItems($candidate))->pluck('job.id')->all();

    expect($ids)->toContain($localJob->id)
        ->and($ids)->not->toContain($abroadJob->id);
});

it('keeps otherwise-pruned jobs when the hard prune is disabled', function () {
    config(['matching.recommendation.hard_prune' => [
        'experience' => false,
        'work_preference' => false,
        'location_country' => false,
        'required_skill_overlap' => false,
    ]]);

    $engine = app(JobMatchingService::class);
    $candidate = phase3Candidate(['years_of_experience' => 1], ['PHP', 'Laravel']);

    $job = phase3Job(phase3Employer(), ['minimum_experience' => 8]);

    $ids = collect(phase3RecommendationItems($candidate))->pluck('job.id')->all();

    expect($ids)->toContain($job->id);
});

it('optionally prunes jobs with zero required-skill overlap', function () {
    config(['matching.recommendation.hard_prune.required_skill_overlap' => true]);

    $engine = app(JobMatchingService::class);
    $candidate = phase3Candidate(skills: ['PHP', 'Laravel']);

    $matchingJob = phase3Job(phase3Employer(), ['required_skills_json' => ['PHP']]);
    $noOverlapJob = phase3Job(phase3Employer(), ['required_skills_json' => ['Accounting', 'Auditing']]);

    $ids = collect(phase3RecommendationItems($candidate))->pluck('job.id')->all();

    expect($ids)->toContain($matchingJob->id)
        ->and($ids)->not->toContain($noOverlapJob->id);
});

it('keeps zero-overlap jobs by default when the prune is off', function () {
    $engine = app(JobMatchingService::class);
    $candidate = phase3Candidate(skills: ['PHP', 'Laravel']);

    $noOverlapJob = phase3Job(phase3Employer(), ['required_skills_json' => ['Accounting', 'Auditing']]);

    $ids = collect(phase3RecommendationItems($candidate))->pluck('job.id')->all();

    expect($ids)->toContain($noOverlapJob->id);
});

it('deduplicates identical listings from the same company keeping the best score', function () {
    $employer = phase3Employer();

    $strongJob = phase3Job($employer, ['title' => 'Backend Developer', 'required_skills_json' => ['PHP', 'Laravel']]);
    $twinJob = phase3Job($employer, ['title' => 'Backend Developer', 'required_skills_json' => ['PHP', 'Laravel']]);

    $candidate = phase3Candidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);

    $items = phase3RecommendationItems($candidate);

    expect(count($items))->toBe(1)
        ->and($items[0]['job']->id)->toEqual($strongJob->id)
        ->and($twinJob->id)->not->toEqual($strongJob->id);
});

it('caps how many listings one company can hold in the leading results', function () {
    $employerA = phase3Employer();
    $employerB = phase3Employer();

    // Company A's jobs match the candidate perfectly, B's do not, so A's four
    // equal-scored listings deterministically lead the ranking.
    foreach (['One', 'Two', 'Three', 'Four'] as $suffix) {
        phase3Job($employerA, [
            'title' => "Backend Engineer {$suffix}",
            'required_skills_json' => ['PHP', 'Laravel', 'MySQL', 'Docker'],
        ]);
        phase3Job($employerB, [
            'title' => "Backend Developer {$suffix}",
            'required_skills_json' => ['Go', 'Rust', 'Elixir', 'ReScript'],
        ]);
    }

    $candidate = phase3Candidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);

    $items = phase3RecommendationItems($candidate, perPage: 20);

    expect(count($items))->toBe(8);

    // Every listing is kept, but the cap pushes each company's excess jobs to
    // the tail: A holds only its best three in the head block.
    $sequence = array_map(fn ($item) => $item['job']->employer_id, $items);

    expect($sequence)->toEqual([
        $employerA->id, $employerA->id, $employerA->id,
        $employerB->id, $employerB->id, $employerB->id,
        $employerA->id, $employerB->id,
    ]);
});

it('excludes jobs the candidate already applied to when exclude_applied is set', function () {
    $engine = app(JobMatchingService::class);
    $candidate = phase3Candidate(skills: ['PHP', 'Laravel']);

    $appliedJob = phase3Job(phase3Employer(), ['required_skills_json' => ['PHP', 'Laravel']]);
    $otherJob = phase3Job(phase3Employer(), ['required_skills_json' => ['PHP', 'Laravel']]);

    $candidate->jobApplications()->create([
        'job_id' => $appliedJob->id,
        'status' => JobApplication::STATUS_APPLIED,
        'applied_at' => now(),
    ]);

    $ids = collect(phase3RecommendationItems($candidate, ['exclude_applied' => true]))->pluck('job.id')->all();

    expect($ids)->not->toContain($appliedJob->id)
        ->and($ids)->toContain($otherJob->id);
});

it('excludes candidates who already applied when ranking for an employer', function () {
    $engine = app(JobMatchingService::class);
    $job = phase3Job(phase3Employer());

    $applied = phase3Candidate(skills: ['PHP', 'Laravel']);
    $fresh = phase3Candidate(skills: ['PHP', 'Laravel']);

    $applied->jobApplications()->create([
        'job_id' => $job->id,
        'status' => JobApplication::STATUS_APPLIED,
        'applied_at' => now(),
    ]);

    $ids = collect($engine->rankCandidatesForJob($job, ['exclude_applied' => true], 20)->items())
        ->pluck('candidate.id')
        ->all();

    expect($ids)->not->toContain($applied->id)
        ->and($ids)->toContain($fresh->id);
});

it('persists the algorithm version, status, recommendation score, checksum and expiry', function () {
    $service = app(MatchingEngineService::class);
    $candidate = phase3Candidate(skills: ['PHP', 'Laravel']);
    $job = phase3Job(phase3Employer());

    $row = $service->saveMatch($candidate, $job);

    $expectedStatus = app(JobMatchingService::class)->matchStatusFor($row->overall_match_score);

    expect((int) $row->algorithm_version)->toBe((int) config('matching.algorithm_version'))
        ->and($row->match_status)->toBe($expectedStatus)
        ->and((int) $row->recommendation_score)->toBe((int) $row->overall_match_score)
        ->and($row->data_checksum)->not->toBeNull()
        ->and(strlen((string) $row->data_checksum))->toBe(64)
        ->and($row->expires_at)->not->toBeNull()
        ->and($row->expires_at->isFuture())->toBeTrue();
});

it('treats a persisted row as fresh when data and version are unchanged', function () {
    $service = app(MatchingEngineService::class);
    $candidate = phase3Candidate(skills: ['PHP', 'Laravel']);
    $job = phase3Job(phase3Employer());

    $service->saveMatch($candidate, $job);

    $fresh = $service->freshMatch($candidate->refresh(), $job->refresh());

    expect($fresh)->not->toBeNull()
        ->and($fresh->is_latest)->toBeTrue();
});

it('flags a persisted row as stale when the candidate data changes', function () {
    $service = app(MatchingEngineService::class);
    $candidate = phase3Candidate(skills: ['PHP']);
    $job = phase3Job(phase3Employer());

    $service->saveMatch($candidate, $job);

    $candidate->candidateProfile()->update(['years_of_experience' => 9]);
    $candidate->candidateSkills()->create(['skill_name' => 'Laravel', 'proficiency_level' => 4]);

    expect($service->freshMatch($candidate->refresh(), $job->refresh()))->toBeNull();
});

it('flags a persisted row as stale after the expiry window', function () {
    $service = app(MatchingEngineService::class);
    $candidate = phase3Candidate(skills: ['PHP', 'Laravel']);
    $job = phase3Job(phase3Employer());

    Carbon::setTestNow('2026-01-01 12:00:00');

    $service->saveMatch($candidate->refresh(), $job->refresh());

    Carbon::setTestNow('2026-01-02 13:00:00');

    expect($service->freshMatch($candidate->refresh(), $job->refresh()))->toBeNull();
});

it('flags a persisted row as stale when the algorithm version changes', function () {
    $service = app(MatchingEngineService::class);
    $candidate = phase3Candidate(skills: ['PHP', 'Laravel']);
    $job = phase3Job(phase3Employer());

    $service->saveMatch($candidate, $job);

    $originalVersion = (int) config('matching.algorithm_version');

    config(['matching.algorithm_version' => $originalVersion + 1]);

    expect($service->freshMatch($candidate, $job))->toBeNull();

    config(['matching.algorithm_version' => $originalVersion]);
});

it('recalculates idempotently without duplicating rows', function () {
    $service = app(MatchingEngineService::class);
    $candidate = phase3Candidate(skills: ['PHP', 'Laravel']);
    phase3Job(phase3Employer());
    phase3Job(phase3Employer());

    $service->recalculateForCandidate($candidate);
    $service->recalculateForCandidate($candidate->refresh());

    expect(JobMatchScore::where('candidate_id', $candidate->id)->count())->toBe(2)
        ->and(JobMatchScore::where('candidate_id', $candidate->id)->where('is_latest', true)->count())->toBe(2);
});

it('does not recalculate matches for closed jobs', function () {
    $service = app(MatchingEngineService::class);
    $candidate = phase3Candidate(skills: ['PHP', 'Laravel']);
    $closedJob = phase3Job(phase3Employer(), ['status' => 'closed']);
    $openJob = phase3Job(phase3Employer());

    $service->recalculateForJob($closedJob->refresh());

    expect(JobMatchScore::where('job_id', $closedJob->id)->count())->toBe(0);

    $service->recalculateForJob($openJob->refresh());

    expect(JobMatchScore::where('candidate_id', $candidate->id)->where('job_id', $openJob->id)->exists())->toBeTrue();
});

it('queues recalculation jobs that are unique per job and per candidate', function () {
    $job = phase3Job(phase3Employer());
    $candidate = phase3Candidate();

    $jobJob = new RecalculateJobMatches($job);
    $candidateJob = new RecalculateCandidateMatches($candidate);

    expect($jobJob)->toBeInstanceOf(ShouldBeUnique::class)
        ->and($jobJob->uniqueId())->toBe("job-match-recalc:{$job->id}")
        ->and($candidateJob)->toBeInstanceOf(ShouldBeUnique::class)
        ->and($candidateJob->uniqueId())->toBe("candidate-match-recalc:{$candidate->id}");
});

it('prefers fresh persisted scores for employer candidate matching', function () {
    $service = app(MatchingEngineService::class);
    $job = phase3Job(phase3Employer());
    $candidate = phase3Candidate(skills: ['PHP', 'Laravel']);

    $service->saveMatch($candidate, $job);

    $matches = $service->getCachedTopMatchingCandidates($job, 10);

    $item = $matches->firstWhere('candidate.id', $candidate->id);

    expect($item)->not->toBeNull()
        ->and($item['from_cache'])->toBe(true);
});

it('tops up missing candidates from live scoring when persisted rows are absent', function () {
    $service = app(MatchingEngineService::class);
    $job = phase3Job(phase3Employer());

    phase3Candidate(['years_of_experience' => 5], ['PHP', 'Laravel', 'MySQL', 'Docker']);

    $matches = $service->getCachedTopMatchingCandidates($job, 10);

    expect($matches)->not->toBeEmpty()
        ->and($matches->first()['match_percentage'])->toBeGreaterThan(0);
});

it('never exposes behavioral data on the employer-facing ranking', function () {
    $behavior = app(CandidateBehavioralProfileService::class);
    $engine = app(JobMatchingService::class);
    $candidate = phase3Candidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $job = phase3Job(phase3Employer());

    $behavior->recordSearch($candidate, 'backend developer');
    $behavior->recordView($candidate, $job);
    $behavior->recordApply($candidate, $job);

    $ranked = collect($engine->rankCandidatesForJob($job, [], 20)->items())
        ->firstWhere('candidate.id', $candidate->id);

    expect($ranked)->not->toBeNull()
        ->and($ranked)->not->toHaveKey('behavioral_relevance')
        ->and($ranked)->not->toHaveKey('behavioral_reasons');
});

it('computes deterministically: identical inputs always give identical output', function () {
    $engine = app(JobMatchingService::class);
    $candidate = phase3Candidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $job = phase3Job(phase3Employer());

    $first = $engine->calculateBreakdown($candidate, $job);
    $second = $engine->calculateBreakdown($candidate->refresh(), $job->refresh());

    expect($second)->toEqual($first);
});

it('produces only bounded integer scores across all components for a spread of jobs', function () {
    $engine = app(JobMatchingService::class);
    $candidate = phase3Candidate(skills: ['PHP', 'Laravel']);
    $jobs = [
        phase3Job(phase3Employer(), ['required_skills_json' => ['PHP', 'Laravel', 'MySQL', 'Docker']]),
        phase3Job(phase3Employer(), [
            'title' => 'Accountant',
            'role' => 'Accounting',
            'required_skills_json' => ['Accounting'],
        ]),
        phase3Job(phase3Employer(), ['work_preference' => 'onsite', 'salary_min' => null, 'salary_max' => null]),
    ];

    foreach ($jobs as $job) {
        $breakdown = $engine->calculateBreakdown($candidate, $job);

        expect($breakdown['overall_score'])->toBeInt()->toBeBetween(0, 100);

        foreach ($breakdown['components'] as $component) {
            expect($component['score'])->toBeInt()->toBeBetween(0, 100);
        }

        $encoded = json_encode($breakdown);

        expect($encoded)->not->toContain('NAN')
            ->and($encoded)->not->toContain('INF');
    }
});

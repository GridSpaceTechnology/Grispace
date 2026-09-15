<?php

use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use App\Services\CandidateBehavioralProfileService;
use App\Services\JobMatchingService;
use App\Services\MatchOutcomeService;
use App\Services\MatchSnapshotService;
use Carbon\Carbon;

/**
 * Golden regression cases for the matching + measurement system.
 *
 * These scenarios lock in the behaviour of the canonical engine and the Phase 4
 * outcome/snapshot layer with synthetic data only (no real personal data).
 * Helpers use the phase4 prefix so they never collide with older matching tests.
 */

function phase4Employer(): User
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

function phase4Job(User $employer, array $attributes = []): Job
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

function phase4Candidate(array $profileAttributes = [], array $skills = []): User
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

function phase4RecommendationItems(User $candidate, array $filters = [], int $perPage = 50): array
{
    return app(JobMatchingService::class)
        ->recommendJobsForCandidate($candidate, $filters, $perPage)
        ->items();
}

beforeEach(function () {
    $this->phase4Config = [
        'matching.recommendation.negative_signal' => config('matching.recommendation.negative_signal'),
        'matching.recommendation.hard_prune' => config('matching.recommendation.hard_prune'),
        'matching.recommendation.dedupe_identical_jobs' => config('matching.recommendation.dedupe_identical_jobs'),
        'matching.recommendation.diversity_max_per_company' => config('matching.recommendation.diversity_max_per_company'),
        'matching.algorithm_version' => config('matching.algorithm_version'),
    ];

    config([
        'matching.recommendation.hard_prune.experience' => true,
        'matching.recommendation.hard_prune.work_preference' => true,
        'matching.recommendation.hard_prune.location_country' => false,
        'matching.recommendation.hard_prune.required_skill_overlap' => false,
        'matching.recommendation.dedupe_identical_jobs' => true,
        'matching.recommendation.diversity_max_per_company' => 3,
        'matching.algorithm_version' => 3,
    ]);
});

afterEach(function () {
    config($this->phase4Config);
    Carbon::setTestNow();
});

it('GOLDEN: a perfect candidate profile scores an excellent match with all skills matched', function () {
    $candidate = phase4Candidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $job = phase4Job(phase4Employer());

    $breakdown = app(JobMatchingService::class)->calculateBreakdown($candidate, $job);

    expect($breakdown['overall_score'])->toBeGreaterThanOrEqual(90)
        ->and($breakdown['overall_score'])->toBeLessThanOrEqual(100)
        ->and($breakdown['match_status'])->toBe('excellent')
        ->and($breakdown['match_status'])->not->toBe('incompatible')
        ->and($breakdown['matched_skills'])->toContain('PHP', 'Laravel', 'MySQL', 'Docker')
        ->and($breakdown['missing_skills'])->toBe([]);
});

it('GOLDEN: a cross-domain role is capped and marked incompatible no matter how strong', function () {
    $candidate = phase4Candidate(['desired_role' => 'Software Engineer'], ['PHP', 'Laravel']);
    $job = phase4Job(phase4Employer(), [
        'title' => 'Accountant',
        'role' => 'Accounting',
        'required_skills_json' => ['Bookkeeping', 'Accounting'],
    ]);

    $breakdown = app(JobMatchingService::class)->calculateBreakdown($candidate, $job);

    expect($breakdown['domain_compatible'])->toBeFalse()
        ->and($breakdown['match_status'])->toBe('incompatible')
        ->and($breakdown['overall_score'])->toBe(config('matching.domain_gate_cap'));
});

it('GOLDEN: a missing required skill is a hard gap and never rescues the domain gate', function () {
    $candidate = phase4Candidate(skills: ['PHP']);
    $job = phase4Job(phase4Employer(), [
        'required_skills_json' => ['PHP', 'Laravel', 'MySQL', 'Kubernetes'],
    ]);

    $breakdown = app(JobMatchingService::class)->calculateBreakdown($candidate, $job);

    expect($breakdown['hard_gaps'])->toContain('Missing required skill: Laravel')
        ->and($breakdown['missing_skills'])->toContain('Laravel')
        ->and($breakdown['match_status'])->not->toBe('incompatible');
});

it('GOLDEN: a salary stretch is a soft gap, never treated as a missing requirement', function () {
    $candidate = phase4Candidate(['salary_expectation' => 6000000], ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $job = phase4Job(phase4Employer(), ['salary_max' => 900000]);

    $breakdown = app(JobMatchingService::class)->calculateBreakdown($candidate, $job);

    expect($breakdown['soft_gaps'])->toContain('Top of the advertised range is below your expectation')
        ->and($breakdown['hard_gaps'])->not->toContain('Optional field: salary_expectation')
        ->and($breakdown['missing_skills'])->toBe([]);
});

it('GOLDEN: an over-qualified candidate scores lower, but never resembles a requirement miss', function () {
    $candidate = phase4Candidate(['years_of_experience' => 12], ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $job = phase4Job(phase4Employer(), ['salary_max' => 900000]);

    $breakdown = app(JobMatchingService::class)->calculateBreakdown($candidate, $job);

    expect($breakdown['overall_score'])->toBeGreaterThanOrEqual(85)
        ->and($breakdown['hard_gaps'])->not->toContain('Missing required skill: ');
});

it('GOLDEN: a remote-only candidate is hard-pruned from on-site roles', function () {
    $candidate = phase4Candidate(skills: ['PHP', 'Laravel']);
    $onsite = phase4Job(phase4Employer(), ['work_preference' => 'onsite']);

    $items = phase4RecommendationItems($candidate);

    expect(collect($items)->pluck('job.id'))->not->toContain($onsite->id);
});

it('GOLDEN: a candidate below the experience requirement is pruned from the role', function () {
    $candidate = phase4Candidate(['years_of_experience' => 2], ['PHP', 'Laravel']);
    $senior = phase4Job(phase4Employer(), ['minimum_experience' => 6]);

    $items = phase4RecommendationItems($candidate);

    expect(collect($items)->pluck('job.id'))->not->toContain($senior->id);
});

it('GOLDEN: identical listings from the same company are deduplicated', function () {
    $employer = phase4Employer();
    $first = phase4Job($employer, ['slug' => 'a'.str()->random(9)]);
    $duplicate = phase4Job($employer, ['slug' => 'b'.str()->random(9)]);

    $candidate = phase4Candidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $items = phase4RecommendationItems($candidate);

    $ids = collect($items)->pluck('job.id');

    expect($ids->contains($first->id) || $ids->contains($duplicate->id))->toBeTrue()
        ->and($ids->contains($first->id) && $ids->contains($duplicate->id))->toBeFalse();
});

it('GOLDEN: per-company diversity keeps a single company from sweeping the top results', function () {
    $employerA = phase4Employer();
    $employerB = phase4Employer();

    foreach (['A1', 'A2', 'A3', 'A4'] as $i => $tag) {
        phase4Job($employerA, ['slug' => 'a'.$tag.str()->random(6)]);
    }
    foreach (['B1', 'B2', 'B3', 'B4'] as $i => $tag) {
        phase4Job($employerB, ['slug' => 'b'.$tag.str()->random(6)]);
    }

    $candidate = phase4Candidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $items = array_slice(phase4RecommendationItems($candidate), 0, 4);

    $companies = collect($items)
        ->pluck('job.employer_id')
        ->unique()
        ->count();

    expect($companies)->toBeGreaterThanOrEqual(2);
});

it('GOLDEN: behavioral interest boosts the ranking score but never the profile score', function () {
    $behavior = app(CandidateBehavioralProfileService::class);
    $candidate = phase4Candidate();
    $job = phase4Job(phase4Employer());

    $behavior->recordSearch($candidate, 'backend developer');
    $behavior->recordSearch($candidate, 'backend developer');
    $behavior->recordView($candidate, $job);

    $item = collect(phase4RecommendationItems($candidate))->firstWhere('job.id', $job->id);

    expect($item)->not->toBeNull()
        ->and($item['recommendation_score'])->toBeGreaterThan($item['profile_match_score'])
        ->and($item['profile_match_score'])->toBe($item['overall_score'])
        ->and($item['recommendation_score'])->toBeLessThanOrEqual(100);
});

it('GOLDEN: candidate dismiss feedback moves a job down without touching the profile score', function () {
    $candidate = phase4Candidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $employer = phase4Employer();

    $target = phase4Job($employer, ['slug' => 'target'.str()->random(6)]);
    phase4Job($employer, ['title' => 'Backend API Engineer', 'slug' => 'api'.str()->random(6)]);
    phase4Job($employer, ['title' => 'Backend Platform Engineer', 'role' => 'Platform Engineer', 'slug' => 'platform'.str()->random(6)]);

    $items = phase4RecommendationItems($candidate);
    $before = collect($items)->firstWhere('job.id', $target->id);

    App\Models\CandidateRecommendationFeedback::create([
        'candidate_id' => $candidate->id,
        'job_id' => $target->id,
        'feedback_type' => 'not_relevant',
        'is_relevant' => false,
        'feedback_key' => "c:{$candidate->id}:{$target->id}",
    ]);

    $itemsAfter = phase4RecommendationItems($candidate);
    $after = collect($itemsAfter)->firstWhere('job.id', $target->id);
    $afterIndex = collect($itemsAfter)->search(fn ($item) => $item['job']->id === $target->id);

    expect($after['recommendation_score'])->toBeLessThan($before['recommendation_score'])
        ->and($after['profile_match_score'])->toBe($before['profile_match_score'])
        ->and($after['match_status'])->toBe($before['match_status'])
        ->and($afterIndex)->toBe(count($itemsAfter) - 1);
});

it('GOLDEN: the measurement layer records recommended jobs with rank and algorithm version', function () {
    $candidate = phase4Candidate(skills: ['PHP', 'Laravel']);
    $job = phase4Job(phase4Employer());

    $items = phase4RecommendationItems($candidate);
    $item = collect($items)->firstWhere('job.id', $job->id);

    app(MatchOutcomeService::class)->jobRecommended($candidate, $job, $item, 1);

    $snapshot = App\Models\MatchSnapshot::where('candidate_id', $candidate->id)
        ->where('job_id', $job->id)
        ->where('source', MatchSnapshotService::SOURCE_RECOMMENDED)
        ->first();

    $event = App\Models\MatchOutcomeEvent::where('event_type', 'job_recommended')->first();

    expect($snapshot)->not->toBeNull()
        ->and($snapshot->algorithm_version)->toBe((int) config('matching.algorithm_version'))
        ->and((int) $snapshot->recommendation_score)->toBe($item['recommendation_score'])
        ->and($event->context['rank'])->toBe(1);
});
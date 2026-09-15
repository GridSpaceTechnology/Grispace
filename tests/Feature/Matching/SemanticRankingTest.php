<?php

use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use App\Services\JobMatchingService;
use Illuminate\Support\Facades\Http;

/**
 * Phase 5 ranking semantics. Semantic understanding is strictly an additive,
 * capped nudge - it can never close a real structural gap, never bypass the
 * domain gate, and never touches the persisted profile score.
 */
function phase5RankEmployer(string $company = 'Rankix Ltd'): User
{
    $user = User::factory()->create(['role' => 'employer']);

    Company::create([
        'user_id' => $user->id,
        'name' => $company,
        'slug' => str()->slug($company).'-'.str()->random(5),
        'allow_candidate_messages' => true,
    ]);

    return $user;
}

function phase5RankJob(User $employer, array $attributes = []): Job
{
    return Job::create(array_merge([
        'employer_id' => $employer->id,
        'title' => 'Backend Engineer',
        'role' => 'Backend Engineer',
        'slug' => str()->random(10),
        'employment_type' => 'full_time',
        'work_preference' => 'remote',
        'salary_min' => 500000,
        'salary_max' => 900000,
        'salary_currency' => 'NGN',
        'status' => 'open',
        'required_skills_json' => ['PHP', 'Laravel', 'MySQL', 'Docker'],
    ], $attributes));
}

function phase5RankCandidate(array $profileAttributes = [], array $skills = []): User
{
    $user = User::factory()->create([
        'role' => 'candidate',
        'onboarding_completed' => true,
    ]);

    CandidateProfile::create(array_merge([
        'user_id' => $user->id,
        'desired_role' => 'Backend Developer',
        'years_of_experience' => 4,
        'salary_expectation' => 700000,
        'work_preference' => 'remote',
        'location_country' => 'Nigeria',
    ], $profileAttributes));

    foreach ($skills as $skill) {
        CandidateSkill::create([
            'user_id' => $user->id,
            'skill_name' => $skill,
            'proficiency_level' => 3,
        ]);
    }

    return $user;
}

function phase5RankItems(User $candidate, Job $job): array
{
    $items = app(JobMatchingService::class)->recommendJobsForCandidate($candidate, [], 50)
        ->getCollection()
        ->filter(fn (array $item) => ($item['job'] ?? null) !== null && $item['job']->is($job))
        ->values()
        ->all();

    return $items;
}

function phase5RankItem(User $candidate, Job $job): ?array
{
    $items = phase5RankItems($candidate, $job);

    return $items[0] ?? null;
}

beforeEach(function () {
    $this->phase5RankConfig = [
        'matching.semantic.enabled' => config('matching.semantic.enabled'),
        'matching.semantic.provider' => config('matching.semantic.provider'),
    ];

    config([
        'matching.semantic.enabled' => true,
        'matching.semantic.provider' => 'lexical',
    ]);
});

afterEach(function () {
    config($this->phase5RankConfig);
});

it('stays completely disabled unless explicitly enabled (default off)', function () {
    config(['matching.semantic.enabled' => false]);

    $candidate = phase5RankCandidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $job = phase5RankJob(phase5RankEmployer());

    $item = phase5RankItem($candidate, $job);

    expect($item)->not->toBeNull()
        ->and($item['semantic_similarity'])->toBeNull()
        ->and($item['semantic_points'])->toBe(0)
        ->and($item['semantic_provider'])->toBeNull()
        ->and($item['semantic_fallback'])->toBeFalse();
});

it('running with semantic on only ever adds up to maximum_influence points (5A vs 5B)', function () {
    $this->phase5RankConfig['matching.semantic.enabled'] = false;
    config(['matching.semantic.enabled' => false]);

    $candidate = phase5RankCandidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $job = phase5RankJob(phase5RankEmployer());

    $armA = phase5RankItem($candidate, $job);
    $baseScore = $armA['recommendation_score'];

    config(['matching.semantic.enabled' => true]);

    $armB = phase5RankItem($candidate, $job);

    expect($armB['semantic_points'])->toBeGreaterThan(0)
        ->and($armB['recommendation_score'])->toBeGreaterThanOrEqual($baseScore)
        ->and($armB['recommendation_score'] - $baseScore)->toBeLessThanOrEqual((int) config('matching.semantic.maximum_influence', 15));
});

it('the professional profile score is never modified by semantic understanding', function () {
    $candidate = phase5RankCandidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $job = phase5RankJob(phase5RankEmployer());

    $item = phase5RankItem($candidate, $job);

    expect($item['semantic_points'])->toBeGreaterThan(0)
        ->and(collect($item['breakdown'])->get('profile_match_score'))->toBe($item['profile_match_score']);

    $breakdown = app(JobMatchingService::class)->calculateBreakdown($candidate, $job);

    expect($breakdown['profile_match_score'])->toBe($item['profile_match_score']);
});

it('semantic similarity can never close a real structural gap (monotonicity)', function () {
    $base = ['years_of_experience' => 4, 'salary_expectation' => 700000];

    $excellent = phase5RankCandidate($base, ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $wandering = phase5RankCandidate(
        array_merge(['years_of_experience' => 1, 'salary_expectation' => 1200000]),
        ['PHP', 'Postgres', 'Node', 'Go'],
    );

    $job = phase5RankJob(phase5RankEmployer(), [
        'required_skills_json' => ['PHP', 'Laravel', 'MySQL', 'Docker', 'PostgreSQL', 'Node', 'Golang'],
    ]);

    $excellentItem = phase5RankItem($excellent, $job);
    $wanderingItem = phase5RankItem($wandering, $job);

    // Both can ave high semantic understanding (aliases), but structure wins.
    expect($excellentItem['semantic_similarity'])->not->toBeNull()
        ->and($wanderingItem['semantic_similarity'])->not->toBeNull()
        ->and($excellentItem['profile_match_score'])->toBeGreaterThan($wanderingItem['profile_match_score'])
        ->and($excellentItem['recommendation_score'])->toBeGreaterThan($wanderingItem['recommendation_score'])
        ->and($wanderingItem['semantic_points'])->toBeLessThanOrEqual((int) config('matching.semantic.maximum_influence', 15))
        ->and($wanderingItem['recommendation_score'])->toBeLessThanOrEqual($wanderingItem['profile_match_score'] + (int) config('matching.semantic.maximum_influence', 15));
});

it('the employer ranking applies the same bounded semantics without behavioral data', function () {
    $good = phase5RankCandidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $weak = phase5RankCandidate(['desired_role' => 'Accountant'], ['QuickBooks']);

    $job = phase5RankJob(phase5RankEmployer());

    $items = app(JobMatchingService::class)->rankCandidatesForJob($job, [], 50)
        ->getCollection()
        ->all();

    expect(collect($items)->map(fn ($it) => $it['candidate']->id))
        ->toContain($good->id)
        ->toContain($weak->id);

    $goodItem = collect($items)->first(fn ($it) => $it['candidate']->is($good));
    $weakItem = collect($items)->first(fn ($it) => $it['candidate']->is($weak));

    expect($goodItem['semantic_similarity'])->not->toBeNull()
        ->and($goodItem['semantic_points'])->toBeGreaterThan(0)
        ->and($weakItem['semantic_similarity'])->toBeNull()
        ->and($weakItem['semantic_points'])->toBe(0)
        ->and(collect($items)->search(fn ($it) => $it['candidate']->is($good)))
        ->toBeLessThan(collect($items)->search(fn ($it) => $it['candidate']->is($weak)));
});

it('falls back to the lexical provider when embeddings are not yet available (cold start)', function () {
    Http::fake();

    config([
        'matching.semantic.provider' => 'embeddings',
        'matching.embedding.api_key' => 'secret-test',
        'matching.embedding.api_url' => 'https://embeddings.test/v1/embeddings',
        'matching.embedding.model' => 'test-embedder',
    ]);

    $candidate = phase5RankCandidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $job = phase5RankJob(phase5RankEmployer());

    $item = phase5RankItem($candidate, $job);

    expect($item['semantic_similarity'])->not->toBeNull()
        ->and($item['semantic_provider'])->toBe('embeddings')
        ->and($item['semantic_fallback'])->toBeTrue()
        ->and($item['semantic_points'])->toBeBetween(1, (int) config('matching.semantic.maximum_influence', 15));
});

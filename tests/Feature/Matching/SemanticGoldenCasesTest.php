<?php

use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use App\Services\JobMatchingService;
use App\Services\Semantic\SemanticMatchingService;

/**
 * Phase 5 golden semantic cases.
 *
 * Lock in what semantic understanding does (and must never do): different
 * wording of the same professional facts compares highly, distinct skills that
 * share a prefix stay distinct, unrelated professions stay separated, and
 * semantic similarity NEVER satisfies a required skill.
 * Helpers use the phase5 prefix so they never collide with phase4 matching.
 */
function phase5Employer(string $company = 'Semantix Ltd'): User
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

function phase5Job(User $employer, array $attributes = []): Job
{
    return Job::create(array_merge([
        'employer_id' => $employer->id,
        'title' => 'Backend Engineer',
        'role' => 'Backend Developer',
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

function phase5Candidate(array $profileAttributes = [], array $skills = []): User
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

function phase5Semantic(User $candidate, Job $job): array
{
    $breakdown = app(JobMatchingService::class)->calculateBreakdown($candidate, $job);

    return app(SemanticMatchingService::class)->forPair($candidate, $job, $breakdown);
}

beforeEach(function () {
    $this->phase5Config = [
        'matching.semantic.enabled' => config('matching.semantic.enabled'),
        'matching.semantic.provider' => config('matching.semantic.provider'),
    ];

    config([
        'matching.semantic.enabled' => true,
        'matching.semantic.provider' => 'lexical',
    ]);
});

afterEach(function () {
    config($this->phase5Config);
});

it('GOLDEN SEMANTIC: Backend Developer and Backend Engineer match highly despite different wording', function () {
    $candidate = phase5Candidate(['desired_role' => 'Backend Developer'], ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $job = phase5Job(phase5Employer(), ['title' => 'Backend Engineer', 'role' => 'Backend Engineer']);

    $semantic = phase5Semantic($candidate, $job);

    expect($semantic['disabled'])->toBeFalse()
        ->and($semantic['score'])->toBeGreaterThanOrEqual(85)
        ->and($semantic['points'])->toBeGreaterThanOrEqual(10)
        ->and($semantic['reasons'])->not->toBeEmpty();
});

it('GOLDEN SEMANTIC: Python Developer and Python API Engineer are related', function () {
    $candidate = phase5Candidate(['desired_role' => 'Python Developer'], ['Python', 'REST API']);
    $job = phase5Job(phase5Employer(), ['title' => 'Python API Engineer', 'role' => 'Python API Engineer', 'required_skills_json' => ['Python', 'REST API']]);

    $semantic = phase5Semantic($candidate, $job);

    expect($semantic['score'])->toBeGreaterThanOrEqual(55);
});

it('GOLDEN SEMANTIC: Frontend Developer and React Engineer are related', function () {
    $candidate = phase5Candidate(['desired_role' => 'Frontend Developer'], ['React']);
    $job = phase5Job(phase5Employer(), ['title' => 'React Engineer', 'role' => 'React Engineer', 'required_skills_json' => ['React']]);

    $semantic = phase5Semantic($candidate, $job);

    expect($semantic['score'])->toBeGreaterThanOrEqual(55);
});

it('GOLDEN SEMANTIC: REST API Developer and API Engineer are closely related', function () {
    $candidate = phase5Candidate(['desired_role' => 'REST API Developer'], ['REST API']);
    $job = phase5Job(phase5Employer(), ['title' => 'API Engineer', 'role' => 'API Engineer', 'required_skills_json' => ['RESTful APIs']]);

    $semantic = phase5Semantic($candidate, $job);

    expect($semantic['score'])->toBeGreaterThanOrEqual(80);
});

it('GOLDEN SEMANTIC: Laravel Developer and PHP Backend Developer are related', function () {
    $candidate = phase5Candidate(['desired_role' => 'Laravel Developer'], ['PHP', 'Laravel', 'MySQL']);
    $job = phase5Job(phase5Employer(), ['title' => 'PHP Backend Developer', 'role' => 'PHP Backend Developer', 'required_skills_json' => ['PHP', 'Laravel', 'MySQL']]);

    $semantic = phase5Semantic($candidate, $job);

    expect($semantic['score'])->toBeGreaterThanOrEqual(55);
});

it('GOLDEN SEMANTIC: Accountant and Finance Manager are related professions', function () {
    $candidate = phase5Candidate(['desired_role' => 'Accountant'], ['Accounting', 'Bookkeeping']);
    $job = phase5Job(phase5Employer(), ['title' => 'Finance Manager', 'role' => 'Finance Manager', 'required_skills_json' => ['Accounting', 'Bookkeeping']]);

    $breakdown = app(JobMatchingService::class)->calculateBreakdown($candidate, $job);
    $semantic = phase5Semantic($candidate, $job);

    expect($breakdown['domain_compatible'])->toBeTrue()
        ->and($semantic['score'])->toBeGreaterThanOrEqual(60);
});

it('GOLDEN SEMANTIC: Registered Nurse and Nursing roles are related', function () {
    $candidate = phase5Candidate(['desired_role' => 'Registered Nurse'], ['Patient Care', 'Nursing']);
    $job = phase5Job(phase5Employer(), ['title' => 'Staff Nurse', 'role' => 'Nursing', 'required_skills_json' => ['Patient Care', 'Nursing']]);

    $breakdown = app(JobMatchingService::class)->calculateBreakdown($candidate, $job);
    $semantic = phase5Semantic($candidate, $job);

    expect($breakdown['domain_compatible'])->toBeTrue()
        ->and($semantic['score'])->toBeGreaterThanOrEqual(60);
});

it('GOLDEN SEMANTIC: Software Engineer and Accountant stay separated', function () {
    $candidate = phase5Candidate(['desired_role' => 'Software Engineer'], ['PHP', 'JavaScript']);
    $job = phase5Job(phase5Employer(), ['title' => 'Accountant', 'role' => 'Accountant', 'required_skills_json' => ['QuickBooks']]);

    $breakdown = app(JobMatchingService::class)->calculateBreakdown($candidate, $job);
    $semantic = phase5Semantic($candidate, $job);

    expect($breakdown['domain_compatible'])->toBeFalse()
        ->and($semantic['score'])->toBeNull()
        ->and($semantic['points'])->toBe(0);

    $item = collect(app(JobMatchingService::class)->recommendJobsForCandidate($candidate, [], 50)->items())
        ->first(fn ($item) => $item['job']->is($job));

    expect($item)->not->toBeNull()
        ->and($item['recommendation_score'])->toBeLessThanOrEqual((int) config('matching.domain_gate_cap'))
        ->and($item['semantic_points'])->toBe(0)
        ->and($item['semantic_similarity'])->toBeNull();
});

it('GOLDEN SEMANTIC: Java Developer and JavaScript Developer are NOT equated', function () {
    $candidate = phase5Candidate(['desired_role' => 'Java Developer'], ['Java']);
    $job = phase5Job(phase5Employer(), ['title' => 'JavaScript Developer', 'role' => 'JavaScript Developer', 'required_skills_json' => ['JavaScript']]);

    $breakdown = app(JobMatchingService::class)->calculateBreakdown($candidate, $job);
    $semantic = phase5Semantic($candidate, $job);

    $javaCandidate = phase5Candidate(['desired_role' => 'Java Developer'], ['Java']);
    $javaJob = phase5Job(phase5Employer(), ['title' => 'Java Developer', 'role' => 'Java Developer', 'required_skills_json' => ['Java']]);
    $selfSemantic = phase5Semantic($javaCandidate, $javaJob);

    expect($breakdown['hard_gaps'])->toContain('Missing required skill: JavaScript')
        ->and($semantic['score'])->toBeLessThan(50)
        ->and($semantic['score'])->toBeLessThan($selfSemantic['score'])
        ->and($selfSemantic['score'])->toBeGreaterThanOrEqual(85);
});

it('GOLDEN SEMANTIC: Healthcare Assistant and Registered Nurse are related but never interchangeable', function () {
    $candidate = phase5Candidate(
        profileAttributes: ['desired_role' => 'Healthcare Assistant'],
        skills: ['Patient Care', 'Caregiving', 'CPR'],
    );
    $job = phase5Job(phase5Employer(), ['title' => 'Registered Nurse', 'role' => 'Registered Nurse', 'required_skills_json' => ['Registered Nurse']]);

    $breakdown = app(JobMatchingService::class)->calculateBreakdown($candidate, $job);
    $semantic = phase5Semantic($candidate, $job);

    // Related field, but the certification-level required skill stays a hard gap.
    expect($breakdown['domain_compatible'])->toBeTrue()
        ->and($breakdown['hard_gaps'])->toContain('Missing required skill: Registered Nurse')
        ->and($semantic['score'])->toBeLessThan(50)
        ->and($semantic['points'])->toBeLessThanOrEqual(6);
});

it('GOLDEN SEMANTIC: semantic boost is always bounded by maximum_influence', function () {
    $candidate = phase5Candidate(['desired_role' => 'Backend Developer'], ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $job = phase5Job(phase5Employer(), ['title' => 'Backend Engineer', 'role' => 'Backend Engineer', 'required_skills_json' => ['PHP', 'Laravel', 'MySQL', 'Docker']]);

    $items = app(JobMatchingService::class)->recommendJobsForCandidate($candidate, [], 50)->items();

    foreach ($items as $item) {
        expect($item['semantic_points'])->toBeLessThanOrEqual((int) config('matching.semantic.maximum_influence'))
            ->and($item['recommendation_score'])->toBeLessThanOrEqual(100);
    }
});

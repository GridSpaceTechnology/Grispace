<?php

use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\Company;
use App\Models\Job;
use App\Models\Skill;
use App\Models\User;
use App\Services\CandidateBehavioralProfileService;
use App\Services\JobMatchingService;
use Carbon\Carbon;

function behavioralSkill(string $name): Skill
{
    return Skill::firstOrCreate(
        ['name' => $name],
        [
            'slug' => str()->slug($name).'-'.uniqid(),
            'category' => 'technical',
            'is_active' => true,
        ]
    );
}

function behavioralEmployer(): User
{
    $user = User::factory()->create(['role' => 'employer']);

    Company::create([
        'user_id' => $user->id,
        'name' => 'Acme Ltd',
        'slug' => 'acme-'.uniqid(),
        'allow_candidate_messages' => true,
    ]);

    return $user;
}

function behavioralJob(User $employer, array $attributes = []): Job
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

function behavioralCandidate(array $profileAttributes = [], array $skills = []): User
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

it('builds role and domain interest from repeated searches', function () {
    $behavior = app(CandidateBehavioralProfileService::class);
    $engine = app(JobMatchingService::class);
    $candidate = behavioralCandidate();
    $job = behavioralJob(behavioralEmployer());

    $behavior->recordSearch($candidate, 'backend engineering jobs');
    $behavior->recordSearch($candidate, 'backend engineering jobs');
    $behavior->recordSearch($candidate, 'backend engineering jobs');

    $profile = $candidate->behavioralProfile;

    expect($profile)->not->toBeNull()
        ->and($profile->total_events)->toBe(3)
        ->and($candidate->searchHistories()->count())->toBe(3)
        ->and($profile->domain_signals)->not->toBeEmpty()
        ->and($profile->role_signals)->not->toBeEmpty()
        ->and($candidate->behavioralProfile()->count())->toBe(1);

    expect($behavior->behavioralRelevance($candidate, $job))->toBeGreaterThan(0);

    $breakdown = $engine->calculateBreakdown($candidate, $job);

    expect($behavior->behavioralBoost($candidate, $job, $breakdown))->toBeGreaterThan(0);
});

it('builds skill and domain interest from repeated python searches', function () {
    behavioralSkill('Python');

    $behavior = app(CandidateBehavioralProfileService::class);
    $candidate = behavioralCandidate();
    $pythonJob = behavioralJob(behavioralEmployer(), [
        'title' => 'Python Developer',
        'role' => 'Python Developer',
        'required_skills_json' => ['Python', 'Django'],
    ]);

    $behavior->recordSearch($candidate, 'python developer');
    $behavior->recordSearch($candidate, 'python developer');
    $behavior->recordSearch($candidate, 'python developer');

    $profile = $candidate->behavioralProfile;

    expect($profile->skill_signals)->toHaveKey('python')
        ->and($profile->domain_signals)->toHaveKey('technology');

    expect($behavior->behavioralRelevance($candidate, $pythonJob))->toBeGreaterThan(0);
});

it('does not let accountant searches rescue an unrelated match but boosts the searched technology', function () {
    $behavior = app(CandidateBehavioralProfileService::class);
    $engine = app(JobMatchingService::class);
    $candidate = behavioralCandidate(['desired_role' => 'Java Developer']);

    $techJob = behavioralJob(behavioralEmployer(), [
        'title' => 'Senior Java Engineer',
        'role' => 'Java Developer',
        'required_skills_json' => ['Java'],
    ]);
    $accountantJob = behavioralJob(behavioralEmployer(), [
        'title' => 'Accountant',
        'role' => 'Accountant',
        'required_skills_json' => ['Bookkeeping'],
    ]);

    $behavior->recordSearch($candidate, 'software engineer');
    $behavior->recordSearch($candidate, 'software engineer');
    $behavior->recordSearch($candidate, 'software engineer');
    $behavior->recordSearch($candidate, 'accountant');
    $behavior->recordSearch($candidate, 'accountant');
    $behavior->recordSearch($candidate, 'accountant');

    $accountantBreakdown = $engine->calculateBreakdown($candidate, $accountantJob);

    expect($accountantBreakdown['domain_compatible'])->toBeFalse()
        ->and($accountantBreakdown['overall_score'])->toBeLessThanOrEqual(15)
        ->and($behavior->behavioralBoost($candidate, $accountantJob, $accountantBreakdown))->toBe(0);

    $recommendations = collect($engine->recommendJobsForCandidate($candidate, [], 20)->items());

    $techItem = $recommendations->firstWhere('job.id', $techJob->id);
    $accountantItem = $recommendations->firstWhere('job.id', $accountantJob->id);

    expect($techItem)->not->toBeNull()
        ->and($accountantItem)->not->toBeNull()
        ->and($techItem['final_score'])->toBeGreaterThan($accountantItem['final_score']);
});

it('boosts backend jobs the candidate searches and views', function () {
    $behavior = app(CandidateBehavioralProfileService::class);
    $engine = app(JobMatchingService::class);
    $candidate = behavioralCandidate();
    $backend = behavioralJob(behavioralEmployer());

    $behavior->recordSearch($candidate, 'backend developer');
    $behavior->recordSearch($candidate, 'backend developer');
    $behavior->recordView($candidate, $backend);

    $breakdown = $engine->calculateBreakdown($candidate, $backend);

    expect($behavior->behavioralBoost($candidate, $backend, $breakdown))->toBeGreaterThan(0);

    $item = collect($engine->recommendJobsForCandidate($candidate, [], 20)->items())
        ->firstWhere('job.id', $backend->id);

    expect($item['final_score'])->toBeGreaterThan($item['overall_score'])
        ->and($item['behavioral_reasons'])->not->toBeEmpty();
});

it('boosts python jobs the candidate applies to and tracks the application', function () {
    behavioralSkill('Python');

    $behavior = app(CandidateBehavioralProfileService::class);
    $engine = app(JobMatchingService::class);
    $candidate = behavioralCandidate();
    $pythonJob = behavioralJob(behavioralEmployer(), [
        'title' => 'Python Developer',
        'role' => 'Python Developer',
        'required_skills_json' => ['Python', 'Django'],
    ]);

    $behavior->recordSearch($candidate, 'python developer');
    $behavior->recordSearch($candidate, 'python developer');
    $behavior->recordApply($candidate, $pythonJob);

    $profile = $candidate->behavioralProfile;

    expect($profile->applied_jobs)->toHaveKey((string) $pythonJob->id);

    $breakdown = $engine->calculateBreakdown($candidate, $pythonJob);

    expect($behavior->behavioralBoost($candidate, $pythonJob, $breakdown))->toBeGreaterThan(0);

    $item = collect($engine->recommendJobsForCandidate($candidate, [], 20)->items())
        ->firstWhere('job.id', $pythonJob->id);

    expect($item['final_score'])->toBeGreaterThan($item['overall_score']);
});

it('decays old behavioral signals so recent activity dominates', function () {
    $behavior = app(CandidateBehavioralProfileService::class);
    $candidate = behavioralCandidate();

    $oldViewed = behavioralJob(behavioralEmployer());
    $recentlyViewed = behavioralJob(behavioralEmployer());

    Carbon::setTestNow(now()->subDays(90));
    $behavior->recordView($candidate, $oldViewed);
    Carbon::setTestNow();

    $behavior->recordSearch($candidate, 'backend developer');
    $behavior->recordView($candidate, $recentlyViewed);

    $oldRelevance = $behavior->behavioralRelevance($candidate, $oldViewed);
    $recentRelevance = $behavior->behavioralRelevance($candidate, $recentlyViewed);

    expect($recentRelevance)->toBeGreaterThan($oldRelevance);
});

it('caps interest and boost from repeated identical searches', function () {
    behavioralSkill('Python');

    $behavior = app(CandidateBehavioralProfileService::class);
    $candidate = behavioralCandidate();
    $pythonJob = behavioralJob(behavioralEmployer(), [
        'title' => 'Python Developer',
        'role' => 'Python Developer',
        'required_skills_json' => ['Python'],
    ]);

    for ($i = 0; $i < 10; $i++) {
        $behavior->recordSearch($candidate, 'python developer');
    }

    $profile = $candidate->behavioralProfile;

    expect($profile->skill_signals['python']['interest'])->toBeLessThanOrEqual(100)
        ->and($behavior->behavioralRelevance($candidate, $pythonJob))->toBeLessThanOrEqual(100);

    $breakdown = app(JobMatchingService::class)->calculateBreakdown($candidate, $pythonJob);

    expect($behavior->behavioralBoost($candidate, $pythonJob, $breakdown))->toBeLessThanOrEqual(12)
        ->and($behavior->boostForRelevance(100))->toBe(12);
});

it('leaves recommendations untouched when a candidate has no behavioral history', function () {
    $engine = app(JobMatchingService::class);
    $candidate = behavioralCandidate(skills: ['PHP', 'Laravel']);

    $goodJob = behavioralJob(behavioralEmployer(), ['required_skills_json' => ['PHP', 'Laravel']]);
    $weakJob = behavioralJob(behavioralEmployer(), ['required_skills_json' => ['Accounting', 'Auditing']]);

    $recommendations = collect($engine->recommendJobsForCandidate($candidate, [], 12)->items());

    expect($recommendations->first()['job']->id)->toBe($goodJob->id);

    foreach ($recommendations as $item) {
        expect($item['behavioral_relevance'])->toBe(0)
            ->and($item['behavioral_reasons'])->toBe([])
            ->and($item['final_score'])->toBe($item['overall_score']);
    }

    expect($candidate->behavioralProfile)->toBeNull();
});

it('keeps behavioral influence minimal when the profile is strong but activity is weak and unrelated', function () {
    $behavior = app(CandidateBehavioralProfileService::class);
    $engine = app(JobMatchingService::class);
    $candidate = behavioralCandidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $job = behavioralJob(behavioralEmployer());

    $behavior->recordSearch($candidate, 'graphic design');
    $behavior->recordSearch($candidate, 'graphic design');
    $behavior->recordSearch($candidate, 'graphic design');

    $breakdown = $engine->calculateBreakdown($candidate, $job);

    expect($breakdown['overall_score'])->toBeGreaterThanOrEqual(80)
        ->and($behavior->behavioralBoost($candidate, $job, $breakdown))->toBe(0);
});

it('never lets strong behavior push a professionally incompatible job high', function () {
    $behavior = app(CandidateBehavioralProfileService::class);
    $engine = app(JobMatchingService::class);
    $candidate = behavioralCandidate();

    $accountantJob = behavioralJob(behavioralEmployer(), [
        'title' => 'Accountant',
        'role' => 'Accountant',
        'required_skills_json' => ['Bookkeeping'],
    ]);

    $behavior->recordSearch($candidate, 'accountant jobs');
    $behavior->recordSearch($candidate, 'accountant jobs');
    $behavior->recordSearch($candidate, 'accountant jobs');
    $behavior->recordApply($candidate, $accountantJob);

    expect($behavior->behavioralRelevance($candidate, $accountantJob))->toBeGreaterThan(0);

    $breakdown = $engine->calculateBreakdown($candidate, $accountantJob);

    expect($breakdown['overall_score'])->toBeLessThanOrEqual(15)
        ->and($behavior->behavioralBoost($candidate, $accountantJob, $breakdown))->toBe(0);

    $item = collect($engine->recommendJobsForCandidate($candidate, [], 20)->items())
        ->firstWhere('job.id', $accountantJob->id);

    expect($item['final_score'])->toBeLessThanOrEqual(15);
});

it('keeps the domain gate intact even with personality and behavioral support', function () {
    $behavior = app(CandidateBehavioralProfileService::class);
    $engine = app(JobMatchingService::class);
    $candidate = behavioralCandidate();

    $salesJob = behavioralJob(behavioralEmployer(), [
        'title' => 'Sales Manager',
        'role' => 'Sales Manager',
    ]);

    $behavior->recordSearch($candidate, 'sales manager');
    $behavior->recordSearch($candidate, 'sales manager');
    $behavior->recordSearch($candidate, 'sales manager');
    $behavior->recordApply($candidate, $salesJob);

    $breakdown = $engine->calculateBreakdown($candidate, $salesJob);

    expect($breakdown['domain_compatible'])->toBeFalse()
        ->and($breakdown['overall_score'])->toBeLessThanOrEqual(15)
        ->and($behavior->behavioralBoost($candidate, $salesJob, $breakdown))->toBe(0);
});

it('never exposes behavioral data on the employer-facing candidate ranking', function () {
    $behavior = app(CandidateBehavioralProfileService::class);
    $engine = app(JobMatchingService::class);
    $candidate = behavioralCandidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $job = behavioralJob(behavioralEmployer());

    $behavior->recordSearch($candidate, 'backend developer');
    $behavior->recordView($candidate, $job);
    $behavior->recordApply($candidate, $job);

    $ranked = collect($engine->rankCandidatesForJob($job, [], 12)->items())
        ->firstWhere('candidate.id', $candidate->id);

    expect($ranked)->not->toBeNull()
        ->and($ranked)->not->toHaveKey('behavioral_relevance')
        ->and($ranked)->not->toHaveKey('behavioral_reasons')
        ->and($ranked)->not->toHaveKey('final_score')
        ->and($ranked['overall_score'])->toBeGreaterThan(0);
});

it('treats saving a job as a stronger signal than viewing it', function () {
    $behavior = app(CandidateBehavioralProfileService::class);
    $candidate = behavioralCandidate();

    $savedJob = behavioralJob(behavioralEmployer());
    $viewedJob = behavioralJob(behavioralEmployer());

    $behavior->recordSave($candidate, $savedJob);
    $behavior->recordSave($candidate, $savedJob);
    $behavior->recordSave($candidate, $savedJob);
    $behavior->recordView($candidate, $viewedJob);
    $behavior->recordSearch($candidate, 'backend developer');
    $behavior->recordSearch($candidate, 'backend developer');

    $savedRelevance = $behavior->behavioralRelevance($candidate, $savedJob);
    $viewedRelevance = $behavior->behavioralRelevance($candidate, $viewedJob);

    expect($savedRelevance)->toBeGreaterThan($viewedRelevance);
});

it('treats applying to a job as a stronger signal than merely searching', function () {
    behavioralSkill('Python');

    $behavior = app(CandidateBehavioralProfileService::class);
    $candidate = behavioralCandidate();

    $appliedJob = behavioralJob(behavioralEmployer(), [
        'title' => 'Python Developer',
        'role' => 'Python Developer',
        'required_skills_json' => ['Python', 'Django'],
    ]);
    $searchedJob = behavioralJob(behavioralEmployer(), [
        'title' => 'Python Developer',
        'role' => 'Python Developer',
        'required_skills_json' => ['Python', 'Django'],
    ]);

    $behavior->recordSearch($candidate, 'python developer');
    $behavior->recordSearch($candidate, 'python developer');
    $behavior->recordApply($candidate, $appliedJob);

    expect($candidate->behavioralProfile->total_events)->toBe(3);

    $appliedRelevance = $behavior->behavioralRelevance($candidate, $appliedJob);
    $searchedRelevance = $behavior->behavioralRelevance($candidate, $searchedJob);

    expect($appliedRelevance)->toBeGreaterThan($searchedRelevance);
});

it('rebuilds a behavioral profile from the raw event logs', function () {
    $behavior = app(CandidateBehavioralProfileService::class);
    $candidate = behavioralCandidate();
    $job = behavioralJob(behavioralEmployer());

    $behavior->recordSearch($candidate, 'backend developer');
    $behavior->recordView($candidate, $job);

    $rebuilt = $behavior->rebuild($candidate);

    expect($rebuilt->id)->toBe($candidate->behavioralProfile->id)
        ->and($rebuilt->total_events)->toBe(2)
        ->and($rebuilt->domain_signals)->not->toBeEmpty()
        ->and($rebuilt->viewed_jobs)->toHaveKey((string) $job->id);
});

<?php

use App\Jobs\RecalculateJobMatches;
use App\Models\CandidateEducation;
use App\Models\CandidatePersonalityProfile;
use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\Company;
use App\Models\Job;
use App\Models\JobMatchScore;
use App\Models\User;
use App\Services\JobMatchingService;
use App\Services\MatchingEngineService;
use Illuminate\Support\Facades\Queue;

function matchingEmployer(): User
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

function matchJob(User $employer, array $attributes = []): Job
{
    return Job::create([
        'employer_id' => $employer->id,
        'title' => 'Senior Backend Engineer',
        'role' => 'Backend Developer',
        'slug' => Str::random(10),
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

function matchingCandidate(array $profileAttributes = [], array $skills = []): User
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

it('scores a perfect skill match at 100', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $job = matchJob(matchingEmployer());

    expect($engine->scoreSkills($candidate, $job)['score'])->toBe(100);
});

it('scores a partial skill match proportionally and lists missing skills', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(skills: ['PHP', 'Laravel', 'MySQL']);
    $job = matchJob(matchingEmployer());

    $result = $engine->scoreSkills($candidate, $job);

    expect($result['score'])->toBeGreaterThan(50)
        ->and($result['score'])->toBeLessThan(100)
        ->and(collect($result['details']['missing'])->pluck('name'))->toContain('Docker');
});

it('scores zero skill overlap low', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(skills: ['Graphic Design', 'Photography']);
    $job = matchJob(matchingEmployer());

    expect($engine->scoreSkills($candidate, $job)['score'])->toBeLessThanOrEqual(25);
});

it('treats skill names case-insensitively when no normalized ids exist', function () {
    $engine = app(JobMatchingService::class);
    $employer = matchingEmployer();
    $candidate = matchingCandidate(skills: ['php', 'LARAVEL']);
    $job = matchJob($employer, ['required_skills_json' => ['PHP', 'Laravel']]);

    expect($engine->scoreSkills($candidate, $job)['score'])->toBe(100);
});

it('gives a strong role score when titles differ but describe the same work', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate();
    $job = matchJob(matchingEmployer(), ['title' => 'Senior Backend Engineer']);

    expect($engine->scoreRole($candidate, $job)['score'])->toBeGreaterThanOrEqual(85);
});

it('gives a weak role score for unrelated desired roles', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(['desired_role' => 'Graphic Designer']);
    $job = matchJob(matchingEmployer(), ['title' => 'Backend Engineer', 'role' => 'Software Development']);

    expect($engine->scoreRole($candidate, $job)['score'])->toBeLessThanOrEqual(45);
});

it('rewards meeting the experience requirement and flags shortfalls', function () {
    $engine = app(JobMatchingService::class);
    $employer = matchingEmployer();

    $meets = matchingCandidate(['years_of_experience' => 4]);
    $below = matchingCandidate(['years_of_experience' => 1]);

    $job = matchJob($employer, ['minimum_experience' => 3]);

    $metScore = $engine->scoreExperience($meets, $job)['score'];
    $belowScore = $engine->scoreExperience($below, $job)['score'];

    expect($metScore)->toBeGreaterThanOrEqual(85)
        ->and($belowScore)->toBeLessThan(60);
});

it('excludes experience from scoring when the role sets no experience requirement', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(['years_of_experience' => 0]);
    $job = matchJob(matchingEmployer(), ['minimum_experience' => 0]);

    $result = $engine->scoreExperience($candidate, $job);

    expect($result['applicable'])->toBeFalse()
        ->and($result['score'])->toBe(config('matching.neutral_score'));
});

it('matches temperament when the assessment aligns with the job preference', function () {
    $engine = app(JobMatchingService::class);

    $aligned = matchingCandidate();
    $mismatched = matchingCandidate();

    foreach ([$aligned, $mismatched] as $index => $candidate) {
        CandidatePersonalityProfile::create([
            'candidate_id' => $candidate->id,
            'temperament_type' => $index === 0 ? 'Analytical' : 'Calm',
            'assessment_completed' => true,
        ]);
    }

    $job = matchJob(matchingEmployer(), ['temperament_preference' => 'analytical']);

    $alignedScore = $engine->temperamentScore($aligned->refresh(), $job);
    $mismatchedScore = $engine->temperamentScore($mismatched->refresh(), $job);

    expect($alignedScore)->toBe(100)
        ->and($mismatchedScore)->toBeLessThan($alignedScore)
        ->and($mismatchedScore)->toBeLessThan(60);
});

it('gracefully handles non-standard job temperament preferences', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate();

    CandidatePersonalityProfile::create([
        'candidate_id' => $candidate->id,
        'temperament_type' => 'Analytical',
        'assessment_completed' => true,
    ]);

    $job = matchJob(matchingEmployer(), ['temperament_preference' => 'creative']);

    expect($engine->temperamentScore($candidate->refresh(), $job))
        ->toBe(50);
});

it('uses a neutral work-style score without a completed assessment and marks it inapplicable', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate();
    $job = matchJob(matchingEmployer(), ['temperament_preference' => 'analytical']);

    $result = $engine->scorePersonality($candidate, $job);

    expect($result['score'])->toBe(config('matching.neutral_score'))
        ->and($result['applicable'])->toBeFalse();
});

it('frames personality results as work-style compatibility, never verdicts', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate();
    $job = matchJob(matchingEmployer());

    $component = $engine->scorePersonality($candidate, $job);

    expect($component['label'])->toBe('Work Style Compatibility');
});

it('scores identical work preferences at full marks', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(['work_preference' => 'remote']);
    $job = matchJob(matchingEmployer(), ['work_preference' => 'remote']);

    expect($engine->scoreWorkPreference($candidate, $job)['score'])->toBe(100);
});

it('heavily penalises remote-only candidates against mandatory onsite roles', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(['work_preference' => 'remote']);
    $job = matchJob(matchingEmployer(), ['work_preference' => 'onsite']);

    expect($engine->scoreWorkPreference($candidate, $job)['score'])->toBeLessThanOrEqual(30);
});

it('caps environment scores when onsite roles are in another country', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(['work_preference' => 'onsite', 'location_country' => 'Nigeria']);
    $job = matchJob(matchingEmployer(), [
        'work_preference' => 'onsite',
        'location_country' => 'Kenya',
    ]);

    expect($engine->scoreWorkPreference($candidate, $job)['score'])->toBeLessThanOrEqual(50);
});

it('rewards expectations inside the advertised salary range', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(['salary_expectation' => 700000]);
    $job = matchJob(matchingEmployer(), ['salary_min' => 500000, 'salary_max' => 900000]);

    expect($engine->scoreSalary($candidate, $job)['score'])->toBe(100);
});

it('scores expectations above the range lower but not fatally', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(['salary_expectation' => 5000000]);
    $job = matchJob(matchingEmployer(), ['salary_min' => 500000, 'salary_max' => 900000]);

    $score = $engine->scoreSalary($candidate, $job)['score'];

    expect($score)->toBeLessThan(70)->toBeGreaterThanOrEqual(25);
});

it('stays neutral when the job currency cannot be compared safely', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(['salary_expectation' => 700000]);
    $job = matchJob(matchingEmployer(), ['salary_currency' => 'USD']);

    $result = $engine->scoreSalary($candidate, $job);

    expect($result['score'])->toBe(config('matching.neutral_score'))
        ->and(implode(' ', $result['reasons']))->toContain('USD');
});

it('does not compare salaries across different currencies numerically', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(['salary_expectation' => 2000]);
    $job = matchJob(matchingEmployer(), ['salary_currency' => 'USD', 'salary_min' => 150000, 'salary_max' => 300000]);

    expect($engine->scoreSalary($candidate, $job)['score'])->toBe(config('matching.neutral_score'));
});

it('awards education only against stated requirements', function () {
    $engine = app(JobMatchingService::class);
    $employer = matchingEmployer();

    $graduate = matchingCandidate();
    CandidateEducation::create([
        'user_id' => $graduate->id,
        'institution' => 'UNILAG',
        'qualification' => "Bachelor's Degree",
        'year_completed' => 2020,
    ]);

    $noDegree = matchingCandidate();

    $jobWithoutRequirement = matchJob($employer);
    $jobWithRequirement = matchJob(matchingEmployer());
    $jobWithRequirement->jobRequirements()->create([
        'requirement_type' => 'education',
        'requirement_value' => "Bachelor's Degree",
        'is_mandatory' => true,
    ]);

    $noRequirement = $engine->scoreEducation($graduate, $jobWithoutRequirement);

    expect($noRequirement['score'])->toBe(config('matching.neutral_score'))
        ->and($noRequirement['applicable'])->toBeFalse();

    $noDegreeResult = $engine->scoreEducation($noDegree, $jobWithRequirement);

    expect($noDegreeResult['score'])->toBe(20)
        ->and($noDegreeResult['applicable'])->toBeTrue();
});

it('returns a neutral availability score only when the job sets no start-date requirement', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate();
    $job = matchJob(matchingEmployer());

    $result = $engine->scoreAvailability($candidate, $job);

    expect($result['score'])->toBe(config('matching.neutral_score'))
        ->and($result['applicable'])->toBeFalse();

    $job->jobRequirements()->create([
        'requirement_type' => 'availability',
        'requirement_value' => 'immediately',
        'is_mandatory' => true,
    ]);

    $lowResult = $engine->scoreAvailability($candidate->refresh(), $job->refresh());

    expect($lowResult['score'])->toBe(20)
        ->and($lowResult['applicable'])->toBeTrue();
});

it('gives a high score when a software engineer matches a software engineering job', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(['desired_role' => 'Software Engineer'], ['PHP', 'Laravel']);
    $job = matchJob(matchingEmployer(), [
        'title' => 'Senior Software Engineer',
        'role' => 'Engineering',
        'minimum_experience' => 3,
        'required_skills_json' => ['PHP', 'Laravel'],
    ]);

    $breakdown = $engine->calculateBreakdown($candidate, $job);

    expect($breakdown['domain_compatible'])->toBeTrue()
        ->and($breakdown['overall_score'])->toBeGreaterThanOrEqual(90);
});

it('gives a strong score for a Laravel developer applying to a backend role', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(['desired_role' => 'Laravel Developer'], ['Laravel', 'MySQL']);
    $job = matchJob(matchingEmployer(), [
        'minimum_experience' => 3,
        'required_skills_json' => ['Laravel', 'MySQL'],
    ]);

    $breakdown = $engine->calculateBreakdown($candidate, $job);

    expect($breakdown['candidate_domain'])->toBe(config('professional_domains.domains.technology.label'))
        ->and($breakdown['job_domain'])->toBe(config('professional_domains.domains.technology.label'))
        ->and($breakdown['domain_compatible'])->toBeTrue()
        ->and($breakdown['overall_score'])->toBeGreaterThanOrEqual(80);
});

it('collapse the overall score when a software engineer applies to an accounting role', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(['desired_role' => 'Software Engineer'], ['PHP', 'Laravel']);
    $job = matchJob(matchingEmployer(), [
        'title' => 'Accountant',
        'role' => 'Accounting',
        'required_skills_json' => ['Accounting', 'Auditing'],
    ]);

    $breakdown = $engine->calculateBreakdown($candidate, $job);

    expect($breakdown['domain_compatible'])->toBeFalse()
        ->and($breakdown['candidate_domain'])->toBe(config('professional_domains.domains.technology.label'))
        ->and($breakdown['job_domain'])->toBe(config('professional_domains.domains.finance.label'))
        ->and($breakdown['components']['role']['score'])->toBe(10)
        ->and($breakdown['overall_score'])->toBe(config('matching.domain_gate_cap'));
});

it('scores a same-domain accountancy-to-analyst switch strongly when skills align', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(['desired_role' => 'Accountant'], ['Accounting', 'Analysis']);
    $job = matchJob(matchingEmployer(), [
        'title' => 'Financial Analyst',
        'role' => 'Finance',
        'minimum_experience' => 2,
        'required_skills_json' => ['Accounting', 'Analysis'],
    ]);

    $breakdown = $engine->calculateBreakdown($candidate, $job);

    expect($breakdown['candidate_domain'])->toBe(config('professional_domains.domains.finance.label'))
        ->and($breakdown['domain_compatible'])->toBeTrue()
        ->and($breakdown['overall_score'])->toBeGreaterThanOrEqual(80);
});

it('gates unrelated creative-to-finance moves instead of letting preferences rescue them', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(['desired_role' => 'Graphic Designer'], ['Photoshop']);
    $job = matchJob(matchingEmployer(), [
        'title' => 'Accountant',
        'role' => 'Accounting',
        'required_skills_json' => ['Accounting'],
    ]);

    $breakdown = $engine->calculateBreakdown($candidate, $job);

    expect($breakdown['domain_compatible'])->toBeFalse()
        ->and($breakdown['overall_score'])->toBeLessThanOrEqual(config('matching.domain_gate_cap'));
});

it('does not inflate candidates with no skills against a skills-heavy job', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate();
    $job = matchJob(matchingEmployer());

    $breakdown = $engine->calculateBreakdown($candidate, $job);

    expect($breakdown['components']['skills']['score'])->toBe(0)
        ->and($breakdown['components']['skills']['applicable'])->toBeTrue()
        ->and($breakdown['overall_score'])->toBeLessThanOrEqual(60);
});

it('does not award education points when the job sets no education requirement', function () {
    $engine = app(JobMatchingService::class);
    $graduate = matchingCandidate();
    CandidateEducation::create([
        'user_id' => $graduate->id,
        'institution' => 'UNILAG',
        'qualification' => "Bachelor's Degree",
        'year_completed' => 2020,
    ]);
    $job = matchJob(matchingEmployer());

    $breakdown = $engine->calculateBreakdown($graduate, $job);

    expect($breakdown['components']['education']['applicable'])->toBeFalse()
        ->and($breakdown['components']['education']['score'])->toBe(config('matching.neutral_score'));
});

it('does not award personality points when the job states no preferences', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate();

    CandidatePersonalityProfile::create([
        'candidate_id' => $candidate->id,
        'temperament_type' => 'Analytical',
        'assessment_completed' => true,
    ]);

    $job = matchJob(matchingEmployer());

    $breakdown = $engine->calculateBreakdown($candidate->refresh(), $job);

    expect($breakdown['components']['personality']['applicable'])->toBeFalse()
        ->and($breakdown['components']['personality']['score'])->toBe(config('matching.neutral_score'));
});

it('blocks matches even when skills align perfectly across domains', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(['desired_role' => 'Software Engineer'], ['PHP', 'Laravel']);
    $job = matchJob(matchingEmployer(), [
        'title' => 'Accountant',
        'role' => 'Accounting',
        'required_skills_json' => ['PHP', 'Laravel'],
    ]);

    $breakdown = $engine->calculateBreakdown($candidate, $job);

    expect($breakdown['components']['skills']['score'])->toBe(100)
        ->and($breakdown['domain_compatible'])->toBeFalse()
        ->and($breakdown['overall_score'])->toBe(config('matching.domain_gate_cap'));
});

it('reduces strong role matches when required skills are missing', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(skills: ['PHP']);
    $job = matchJob(matchingEmployer(), [
        'minimum_experience' => 2,
        'required_skills_json' => ['PHP', 'Laravel', 'MySQL', 'Docker'],
    ]);

    $breakdown = $engine->calculateBreakdown($candidate, $job);

    expect($breakdown['components']['role']['score'])->toBe(100)
        ->and($breakdown['components']['skills']['score'])->toBeLessThan(50)
        ->and($breakdown['overall_score'])->toBeLessThan(85);
});

it('combines a compatible role and matching skills into a strong overall', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(['desired_role' => 'Full Stack Developer'], ['PHP', 'JavaScript', 'MySQL']);
    $job = matchJob(matchingEmployer(), [
        'title' => 'Full Stack Developer',
        'role' => 'Software Development',
        'minimum_experience' => 3,
        'required_skills_json' => ['PHP', 'JavaScript', 'MySQL'],
    ]);

    $breakdown = $engine->calculateBreakdown($candidate, $job);

    expect($breakdown['domain_compatible'])->toBeTrue()
        ->and($breakdown['overall_score'])->toBeGreaterThanOrEqual(85);
});

it('does not let a perfectly compatible personality rescue an unrelated domain', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(['desired_role' => 'Accountant'], ['Bookkeeping']);

    CandidatePersonalityProfile::create([
        'candidate_id' => $candidate->id,
        'temperament_type' => 'Analytical',
        'assessment_completed' => true,
    ]);

    $job = matchJob(matchingEmployer(), [
        'title' => 'Senior Software Engineer',
        'role' => 'Software Development',
        'temperament_preference' => 'analytical',
        'required_skills_json' => ['PHP', 'Laravel'],
    ]);

    $breakdown = $engine->calculateBreakdown($candidate->refresh(), $job);

    expect($breakdown['components']['personality']['score'])->toBe(100)
        ->and($breakdown['domain_compatible'])->toBeFalse()
        ->and($breakdown['overall_score'])->toBe(config('matching.domain_gate_cap'));
});

it('computes the overall score from applicable components only when data is partial', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(['salary_expectation' => null, 'work_preference' => null], ['PHP']);
    $job = matchJob(matchingEmployer(), [
        'salary_min' => null,
        'salary_max' => null,
        'required_skills_json' => ['PHP', 'Laravel', 'MySQL', 'Docker'],
    ]);

    $breakdown = $engine->calculateBreakdown($candidate, $job);

    $applicable = collect($breakdown['components'])->filter(fn ($c) => $c['applicable'])->keys()->all();

    expect($applicable)->toBe(['skills', 'role'])
        ->and($breakdown['components']['skills']['score'])->toBe(25)
        ->and($breakdown['components']['role']['score'])->toBe(100)
        ->and($breakdown['overall_score'])->toBe(55);
});

it('produces an overall score within bounds with a category label', function () {
    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $job = matchJob(matchingEmployer());

    $breakdown = $engine->calculateBreakdown($candidate, $job);

    expect($breakdown['overall_score'])->toBeInt()
        ->and($breakdown['overall_score'])->toBeGreaterThanOrEqual(80)
        ->and($breakdown['overall_score'])->toBeLessThanOrEqual(100)
        ->and($breakdown['category'])->toBeIn([
            config('matching.labels.excellent'),
            config('matching.labels.strong'),
        ])
        ->and($breakdown['components'])->toHaveCount(8);
});

it('buckets category labels by configured thresholds', function () {
    $engine = app(JobMatchingService::class);

    expect($engine->categoryFor(95))->toBe(config('matching.labels.excellent'))
        ->and($engine->categoryFor(83))->toBe(config('matching.labels.strong'))
        ->and($engine->categoryFor(74))->toBe(config('matching.labels.good'))
        ->and($engine->categoryFor(63))->toBe(config('matching.labels.potential'))
        ->and($engine->categoryFor(30))->toBe(config('matching.labels.low'));
});

it('respects configured weights when computing the overall score', function () {
    config(['matching.weights' => [
        'skills' => 100,
        'role' => 0,
        'experience' => 0,
        'personality' => 0,
        'work_preference' => 0,
        'salary' => 0,
        'education' => 0,
        'availability' => 0,
    ]]);

    $engine = app(JobMatchingService::class);
    $candidate = matchingCandidate(skills: ['PHP']);
    $job = matchJob(matchingEmployer(), ['required_skills_json' => ['PHP']]);

    expect($engine->overall($candidate, $job))
        ->toBe($engine->scoreSkills($candidate, $job)['score']);
});

it('ranks recommended jobs best-first and paginates', function () {
    $engine = app(JobMatchingService::class);
    $employer = matchingEmployer();

    $goodJob = matchJob($employer, ['required_skills_json' => ['PHP', 'Laravel']]);
    $weakJob = matchJob(matchingEmployer(), ['required_skills_json' => ['Accounting', 'Auditing']]);

    $candidate = matchingCandidate(skills: ['PHP', 'Laravel']);

    $recommendations = $engine->recommendJobsForCandidate($candidate, [], 1);

    $items = collect($recommendations->items());

    expect($recommendations->total())->toBeGreaterThanOrEqual(2)
        ->and($items->first()['job']->id)->toBe($goodJob->id)
        ->and($items->first()['overall_score'])
        ->toBeGreaterThanOrEqual($weakJob === $items->last()['job'] ? 0 : 0);
});

it('applies recommendation filters at query level', function () {
    $engine = app(JobMatchingService::class);

    matchJob(matchingEmployer(), ['work_preference' => 'remote']);
    matchJob(matchingEmployer(), ['title' => 'Onsite Warehouse Role', 'role' => 'Logistics', 'work_preference' => 'onsite']);

    $candidate = matchingCandidate();

    $filtered = $engine->recommendJobsForCandidate($candidate, ['work_preference' => 'remote'], 12);

    expect($filtered->total())->toBe(1)
        ->and(collect($filtered->items())->first()['job']->work_preference)->toBe('remote');
});

it('excludes min_score filtered recommendations', function () {
    $engine = app(JobMatchingService::class);

    matchJob(matchingEmployer(), ['required_skills_json' => ['Rocket Science']]);
    matchJob(matchingEmployer(), ['required_skills_json' => ['PHP']]);

    $candidate = matchingCandidate(skills: ['PHP']);

    $any = $engine->recommendJobsForCandidate($candidate, [], 20);
    $strict = $engine->recommendJobsForCandidate($candidate, ['min_score' => 90], 20);

    expect($any->total())->toBe(2)
        ->and($strict->total())->toBeLessThan($any->total());
});

it('ranks candidates for a job and exposes applied state separately', function () {
    $engine = app(JobMatchingService::class);
    $employer = matchingEmployer();

    $strong = matchingCandidate(skills: ['PHP', 'Laravel', 'MySQL', 'Docker']);
    $weak = matchingCandidate(['desired_role' => 'Accountant'], ['Bookkeeping']);

    $job = matchJob($employer);

    $ranked = collect($engine->rankCandidatesForJob($job, [], 12)->items());

    expect($ranked)->toHaveCount(2)
        ->and($ranked->first()['candidate']->id)->toBe($strong->id)
        ->and($ranked->first()['overall_score'])->toBeGreaterThan($ranked->last()['overall_score']);
});

it('paginates large candidate pools instead of loading everything into the view', function () {
    $engine = app(JobMatchingService::class);
    $job = matchJob(matchingEmployer());

    matchingCandidate();
    matchingCandidate();

    for ($i = 0; $i < 11; $i++) {
        matchingCandidate();
    }

    $page = $engine->rankCandidatesForJob($job, [], 12);

    expect($page->total())->toBe(13)
        ->and(count($page->items()))->toBe(12);
});

it('persists exactly one latest score row per candidate-job pair with the full breakdown', function () {
    Queue::fake();

    $service = app(MatchingEngineService::class);
    $candidate = matchingCandidate(skills: ['PHP', 'Laravel']);
    $job = matchJob(matchingEmployer());

    $service->saveMatch($candidate, $job);
    $service->saveMatch($candidate->refresh(), $job);

    $rows = JobMatchScore::where('candidate_id', $candidate->id)->where('job_id', $job->id)->get();

    expect($rows)->toHaveCount(1)
        ->and($rows->first()->is_latest)->toBeTrue()
        ->and($rows->first()->overall_match_score)->toBeGreaterThan(0)
        ->and($rows->first()->role_score)->toBeGreaterThan(0)
        ->and($rows->first()->matched_skills)->toContain('PHP')
        ->and($rows->first()->missing_skills)->toContain('Docker')
        ->and($rows->first()->scored_at)->not->toBeNull();
});

it('recalculates stored scores after a job changes', function () {
    Queue::fake();

    $service = app(MatchingEngineService::class);
    $candidate = matchingCandidate(['salary_expectation' => 800000]);
    $employer = matchingEmployer();

    $job = matchJob($employer, ['salary_min' => 200000, 'salary_max' => 400000]);

    $before = $service->saveMatch($candidate, $job)->overall_match_score;

    $job->update(['salary_min' => 600000, 'salary_max' => 1000000]);
    $after = $service->saveMatch($candidate->refresh(), $job->refresh())->overall_match_score;

    expect($after)->toBeGreaterThan($before);
});

it('queues recalculation when an employer edits a job', function () {
    Queue::fake();

    $employer = User::factory()->create(['role' => 'employer']);
    $job = matchJob($employer);

    $response = $this->actingAs($employer)
        ->patch(route('employer.jobs.update', ['job' => $job->id]), [
            'title' => $job->title,
            'role' => $job->role,
            'employment_type' => 'full_time',
            'work_preference' => 'hybrid',
            'description' => 'Updated description',
            'required_skills' => 'PHP, Laravel',
        ]);

    $response->assertRedirect();

    Queue::assertPushed(RecalculateJobMatches::class);
});

it('prevents other employers from viewing a job match page', function () {
    $owner = User::factory()->create(['role' => 'employer']);
    $intruder = User::factory()->create(['role' => 'employer']);

    $job = matchJob($owner);

    $this->actingAs($intruder)
        ->get(route('employer.jobs.candidates', ['job' => $job->id]))
        ->assertForbidden();
});

it('stops candidates from accessing employer match pages and vice versa', function () {
    $employer = User::factory()->create(['role' => 'employer']);
    $candidate = matchingCandidate();
    $job = matchJob($employer);

    $this->actingAs($candidate)
        ->get(route('employer.jobs.candidates', ['job' => $job->id]))
        ->assertForbidden();
});

it('hides private assessment content from employer match listings', function () {
    $employer = User::factory()->create(['role' => 'employer']);
    $candidate = matchingCandidate(skills: ['PHP']);

    CandidatePersonalityProfile::create([
        'candidate_id' => $candidate->id,
        'temperament_type' => 'Analytical',
        'assessment_completed' => true,
        'strengths_summary' => 'PRIVATE-PSYCH-MARKER-42',
    ]);

    matchJob($employer);

    $response = $this->actingAs($employer)
        ->get(route('employer.jobs.candidates', ['job' => $job = Job::where('employer_id', $employer->id)->first()]));

    $response->assertOk()
        ->assertDontSee('PRIVATE-PSYCH-MARKER-42');
});

it('requires authentication for recommendations', function () {
    $this->get(route('candidate.recommended-jobs'))->assertRedirect(route('login'));
});

it('shows ranked recommendations with why-this-match details to candidates', function () {
    $candidate = matchingCandidate(skills: ['PHP', 'Laravel', 'Docker']);
    matchJob(matchingEmployer(), ['required_skills_json' => ['PHP', 'Laravel']]);

    $response = $this->actingAs($candidate)
        ->get(route('candidate.recommended-jobs'));

    $response->assertOk()
        ->assertSee('Recommended Jobs')
        ->assertSee('Why this match?')
        ->assertSee('View Job');
});

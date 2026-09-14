<?php

use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\Company;
use App\Models\Interview;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\User;

function jobCardEmployer(): User
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

function jobCardJob(User $employer, array $attributes = []): Job
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
        'required_skills_json' => ['PHP', 'Laravel', 'MySQL'],
        ...$attributes,
    ]);
}

function jobCardCandidate(): User
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
    ]);

    foreach (['PHP', 'Laravel', 'MySQL'] as $skill) {
        CandidateSkill::create([
            'user_id' => $user->id,
            'skill_name' => $skill,
            'proficiency_level' => 3,
        ]);
    }

    return $user;
}

it('links every available job card to the job details page', function () {
    $candidate = jobCardCandidate();
    $job = jobCardJob(jobCardEmployer());

    $this->actingAs($candidate)
        ->get(route('candidate.jobs'))
        ->assertOk()
        ->assertSee(route('jobs.show', $job), false);
});

it('links recommended job cards to the job details page', function () {
    $candidate = jobCardCandidate();
    $job = jobCardJob(jobCardEmployer());

    $this->actingAs($candidate)
        ->get(route('candidate.recommended-jobs'))
        ->assertOk()
        ->assertSee(route('jobs.show', $job), false);
});

it('keeps the apply button separate from the job card link on the jobs page', function () {
    $candidate = jobCardCandidate();
    $job = jobCardJob(jobCardEmployer());

    $this->actingAs($candidate)
        ->get(route('candidate.jobs'))
        ->assertOk()
        ->assertSee(route('jobs.show', $job), false)
        ->assertSee(route('candidate.jobs.apply', $job), false);
});

it('links top matching jobs on the dashboard to the job details page', function () {
    $candidate = jobCardCandidate();
    $job = jobCardJob(jobCardEmployer());

    $this->actingAs($candidate)
        ->get(route('candidate.dashboard'))
        ->assertOk()
        ->assertSee(route('jobs.show', $job), false);
});

it('links application cards on the dashboard to the job details page', function () {
    $candidate = jobCardCandidate();
    $job = jobCardJob(jobCardEmployer());

    JobApplication::create([
        'job_id' => $job->id,
        'candidate_id' => $candidate->id,
        'status' => JobApplication::STATUS_APPLIED,
        'match_score' => 90,
        'applied_at' => now(),
    ]);

    $this->actingAs($candidate)
        ->get(route('candidate.dashboard'))
        ->assertOk()
        ->assertSee(route('jobs.show', $job), false);
});

it('links applications to the public job page instead of the employer page', function () {
    $candidate = jobCardCandidate();
    $job = jobCardJob(jobCardEmployer());

    JobApplication::create([
        'job_id' => $job->id,
        'candidate_id' => $candidate->id,
        'status' => JobApplication::STATUS_APPLIED,
        'match_score' => 90,
        'applied_at' => now(),
    ]);

    $this->actingAs($candidate)
        ->get(route('candidate.applications'))
        ->assertOk()
        ->assertSee(route('jobs.show', $job), false)
        ->assertDontSee(route('employer.jobs.show', $job), false);
});

it('links interview job titles to the job details page', function () {
    $employer = jobCardEmployer();
    $candidate = jobCardCandidate();
    $job = jobCardJob($employer);

    Interview::create([
        'employer_id' => $candidate->id,
        'candidate_id' => $candidate->id,
        'job_id' => $job->id,
        'interview_type' => Interview::TYPE_VIDEO,
        'scheduled_at' => now()->addDay(),
        'status' => Interview::STATUS_SCHEDULED,
    ]);

    $this->actingAs($candidate)
        ->get(route('candidate.interviews'))
        ->assertOk()
        ->assertSee(route('jobs.show', $job), false);
});

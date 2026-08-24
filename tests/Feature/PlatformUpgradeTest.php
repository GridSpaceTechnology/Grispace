<?php

use App\Models\Company;
use App\Models\Job;
use App\Models\User;

function employerWithCompany(array $companyAttributes = []): User
{
    $employer = User::factory()->create(['role' => 'employer']);

    Company::create(array_merge([
        'user_id' => $employer->id,
        'name' => 'Acme Ltd',
        'slug' => 'acme-'.uniqid(),
        'allow_candidate_messages' => true,
    ], $companyAttributes));

    return $employer;
}

function jobForEmployer(User $employer, array $attributes = []): Job
{
    return Job::create(array_merge([
        'employer_id' => $employer->id,
        'title' => 'Backend Engineer',
        'slug' => 'backend-engineer-'.uniqid(),
        'role' => 'Engineer',
        'description' => 'Build things.',
        'employment_type' => 'full_time',
        'work_preference' => 'remote',
        'status' => 'open',
    ], $attributes));
}

it('allows a candidate to start a conversation when the employer accepts messages', function () {
    $employer = employerWithCompany();
    $candidate = User::factory()->create(['role' => 'candidate']);

    $response = $this->actingAs($candidate)
        ->postJson(route('candidate.messages.create', ['employer' => $employer->id]));

    $response->assertOk();
    $conversationId = $response->json('conversation_id');
    expect($conversationId)->not->toBeNull();

    // Second request reuses the same conversation.
    $second = $this->actingAs($candidate)
        ->postJson(route('candidate.messages.create', ['employer' => $employer->id]));

    expect($second->json('conversation_id'))->toBe($conversationId);
});

it('rejects candidates when the employer disables messaging and never creates a conversation', function () {
    $employer = employerWithCompany(['allow_candidate_messages' => false]);
    $candidate = User::factory()->create(['role' => 'candidate']);

    $this->actingAs($candidate)
        ->postJson(route('candidate.messages.create', ['employer' => $employer->id]))
        ->assertForbidden()
        ->assertJson(['error' => 'This employer is currently not accepting messages from candidates.']);

    $this->assertDatabaseMissing('conversations', ['candidate_id' => $candidate->id]);
});

it('hides the messaging entry points on public pages when disabled but keeps apply available', function () {
    $employer = employerWithCompany(['allow_candidate_messages' => false]);
    $job = jobForEmployer($employer);
    $candidate = User::factory()->create(['role' => 'candidate']);

    $show = $this->actingAs($candidate)->get(route('jobs.show', $job));
    $show->assertOk()->assertDontSee('Message Employer');

    $profile = $this->actingAs($candidate)->get(route('employers.show', $employer->company));
    $profile->assertOk()->assertDontSee('Message Employer');

    $enabledJob = jobForEmployer(employerWithCompany());
    $this->actingAs($candidate)
        ->get(route('jobs.show', $enabledJob))
        ->assertOk()
        ->assertSee('Message Employer');
});

it('shows the message button on company profile only to eligible candidates', function () {
    $employer = employerWithCompany();

    $candidate = User::factory()->create(['role' => 'candidate']);
    $this->actingAs($candidate)
        ->get(route('employers.show', $employer->company))
        ->assertOk()
        ->assertSee('Message Employer');

    // Employers do not see the candidate-to-employer CTA.
    $otherEmployer = User::factory()->create(['role' => 'employer']);
    $this->actingAs($otherEmployer)
        ->get(route('employers.show', $employer->company))
        ->assertOk()
        ->assertDontSee('Message Employer');

    // Guests see the profile without the CTA.
    $this->get(route('employers.show', $employer->company))
        ->assertOk()
        ->assertDontSee('Message Employer');
});

it('prevents employers from editing other employers jobs', function () {
    $owner = User::factory()->create(['role' => 'employer']);
    $intruder = User::factory()->create(['role' => 'employer']);
    $job = jobForEmployer($owner);

    $this->actingAs($intruder)
        ->patch(route('employer.jobs.update', ['job' => $job->id]), [
            'title' => 'Hacked Title',
            'role' => 'Engineer',
            'employment_type' => 'full_time',
            'work_preference' => 'remote',
        ])
        ->assertForbidden();

    expect($job->fresh()->title)->toBe('Backend Engineer');
});

it('validates salary range and currency on job update', function () {
    $employer = User::factory()->create(['role' => 'employer']);
    $job = jobForEmployer($employer);

    $payload = fn (array $extra = []) => array_merge([
        'title' => 'Backend Engineer',
        'role' => 'Engineer',
        'employment_type' => 'full_time',
        'work_preference' => 'remote',
        'salary_currency' => 'NGN',
    ], $extra);

    $this->actingAs($employer)
        ->patch(route('employer.jobs.update', ['job' => $job->id]), $payload([
            'salary_min' => 500000,
            'salary_max' => 300000,
        ]))
        ->assertSessionHasErrors('salary_max');

    $this->actingAs($employer)
        ->patch(route('employer.jobs.update', ['job' => $job->id]), $payload([
            'salary_min' => 1000,
            'salary_max' => 2000,
            'salary_currency' => 'XXX',
        ]))
        ->assertSessionHasErrors('salary_currency');

    expect($job->fresh()->salary_min)->toBeNull();
});

it('persists salary period, content sections and company attribution on create', function () {
    $employer = employerWithCompany();
    $candidate = User::factory()->create(['role' => 'candidate']);

    $this->actingAs($employer)
        ->post(route('employer.jobs.store'), [
            'title' => 'Product Designer',
            'slug' => 'product-designer-'.uniqid(),
            'role' => 'Designer',
            'description' => 'Design products.',
            'employment_type' => 'full_time',
            'work_preference' => 'hybrid',
            'status' => 'open',
            'required_skills' => 'Figma, Prototyping, Design Systems',
            'salary_min' => 250000,
            'salary_max' => 400000,
            'salary_currency' => 'NGN',
            'salary_period' => 'monthly',
            'responsibilities' => "Own the design system\nShip polished UI",
            'requirements' => '4+ years product design experience',
            'benefits' => 'Remote stipend, HMO',
        ])
        ->assertRedirect();

    $job = Job::where('title', 'Product Designer')->firstOrFail();

    expect($job->company_id)->toBe($employer->company->id)
        ->and($job->salary_currency)->toBe('NGN')
        ->and($job->salary_period)->toBe('monthly')
        ->and($job->responsibilities)->toContain('design system')
        ->and($job->requirements)->toContain('4+ years')
        ->and($job->benefits)->toContain('HMO')
        ->and($job->required_skills_json)->toBeArray()
        ->and(count($job->required_skills_json))->toBe(3);

    // Public detail page renders the new sections and formatted salary.
    $this->actingAs($candidate)
        ->get(route('jobs.show', $job))
        ->assertOk()
        ->assertSee('Responsibilities')
        ->assertSee('Requirements')
        ->assertSee('Benefits')
        ->assertSee('₦250,000 – ₦400,000 / Month');
});

it('renders whichever currency the employer selected, not a fixed default', function () {
    $employer = employerWithCompany();
    $candidate = User::factory()->create(['role' => 'candidate']);

    $this->actingAs($employer)
        ->post(route('employer.jobs.store'), [
            'title' => 'Remote Analyst',
            'slug' => 'remote-analyst-'.uniqid(),
            'role' => 'Analyst',
            'description' => 'Analyse things.',
            'employment_type' => 'contract',
            'work_preference' => 'remote',
            'status' => 'open',
            'salary_min' => 3000,
            'salary_max' => 4500,
            'salary_currency' => 'GBP',
            'salary_period' => 'daily',
        ])
        ->assertRedirect();

    $job = Job::where('title', 'Remote Analyst')->firstOrFail();

    expect($job->salary_currency)->toBe('GBP');

    $this->get(route('jobs.show', $job))
        ->assertOk()
        ->assertSee('£3,000 – £4,500 / Day')
        ->assertDontSee('$3,000');
});

it('shows every job field on the public and employer detail pages', function () {
    $employer = User::factory()->create(['role' => 'employer']);
    $admin = User::factory()->create(['role' => 'admin']);
    $job = jobForEmployer($employer, [
        'industry' => 'Fintech',
        'location' => 'Lagos',
        'experience_level' => 'senior',
        'minimum_experience' => 5,
        'location_country' => 'Nigeria',
        'temperament_preference' => 'analytical',
        'salary_min' => 100000,
        'salary_max' => 200000,
        'salary_currency' => 'NGN',
        'salary_period' => 'monthly',
        'responsibilities' => 'Lead the platform team.',
        'requirements' => '5 years PHP experience.',
        'benefits' => 'HMO and remote stipend.',
    ]);

    // Public detail page renders the complete overview grid.
    $this->get(route('jobs.show', $job))
        ->assertOk()
        ->assertSee('Job Overview')
        ->assertSee('Fintech')
        ->assertSee('Senior')
        ->assertSee('5+ years')
        ->assertSee('Lagos, Nigeria')
        ->assertSee('Analytical')
        ->assertSee('₦100,000 – ₦200,000 / Month')
        ->assertSee('Full time');

    // Employer/admin detail page renders the full job content without a sidebar.
    $this->actingAs($admin)
        ->get(route('admin.jobs.show', ['job' => $job->id]))
        ->assertOk()
        ->assertSee('Backend Engineer')
        ->assertSee('Requirements')
        ->assertSee('Benefits')
        ->assertSee('Applications (')
        ->assertDontSee('Job Details');
});

it('keeps legacy jobs displaying a fallback company name and no crash on salary', function () {
    $employer = User::factory()->create(['role' => 'employer']);
    $job = jobForEmployer($employer); // No company_id set.

    $this->get(route('jobs.index'))
        ->assertOk()
        ->assertSee($employer->name)
        ->assertDontSee('NaN');
});

it('exposes phone number on candidate portfolio to employers but hides it from other candidates', function () {
    $candidate = User::factory()->create([
        'role' => 'candidate',
        'phone_number' => '+2348012345678',
    ]);

    $employer = User::factory()->create(['role' => 'employer']);
    $otherCandidate = User::factory()->create(['role' => 'candidate']);

    $this->actingAs($employer)
        ->get(route('candidates.show', $candidate))
        ->assertOk()
        ->assertSee('+2348012345678');

    $this->actingAs($otherCandidate)
        ->get(route('candidates.show', $candidate))
        ->assertOk()
        ->assertDontSee('+2348012345678');

    // Owner can always see their own number.
    $this->actingAs($candidate)
        ->get(route('candidates.show', $candidate))
        ->assertOk()
        ->assertSee('+2348012345678');

    // Guest sees neither.
    $this->post(route('logout'));

    $this->get(route('candidates.show', $candidate))
        ->assertOk() // guests can view portfolio
        ->assertDontSee('+2348012345678');
});

it('validates phone number format on profile update', function () {
    $user = User::factory()->create(['role' => 'candidate']);

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => 'abc!!def',
        ])
        ->assertSessionHasErrors('phone_number');

    $this->actingAs($user)
        ->patch(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'phone_number' => '+234 801 234 5678',
        ])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->phone_number)->toBe('+2348012345678');
});

it('lets an employer edit their own job when skills are submitted as a comma separated string', function () {
    $employer = User::factory()->create(['role' => 'employer']);
    $job = jobForEmployer($employer, ['required_skills_json' => ['Old Skill']]);

    $this->actingAs($employer)
        ->patch(route('employer.jobs.update', ['job' => $job->id]), [
            'title' => 'Backend Engineer II',
            'role' => 'Engineer',
            'employment_type' => 'full_time',
            'work_preference' => 'remote',
            'description' => 'Updated description.',
            'location' => 'Lagos',
            'salary_min' => 100000,
            'salary_max' => 200000,
            'salary_currency' => 'NGN',
            'salary_period' => 'monthly',
            'responsibilities' => 'Ship features',
            'requirements' => '5 years PHP',
            'benefits' => 'HMO',
            'required_skills' => 'PHP, Laravel, MySQL',
        ])
        ->assertRedirect(route('employer.jobs.show', ['job' => $job->id]))
        ->assertSessionHasNoErrors();

    $fresh = $job->fresh();

    expect($fresh->title)->toBe('Backend Engineer II')
        ->and($fresh->description)->toBe('Updated description.')
        ->and($fresh->required_skills_json)->toBe(['PHP', 'Laravel', 'MySQL'])
        ->and($fresh->salary_currency)->toBe('NGN')
        ->and($fresh->responsibilities)->toBe('Ship features')
        ->and($fresh->benefits)->toBe('HMO');
});

it('allows an admin to open the edit form and update any employers job', function () {
    $employer = User::factory()->create(['role' => 'employer']);
    $admin = User::factory()->create(['role' => 'admin']);
    $job = jobForEmployer($employer, ['status' => 'draft']);

    $this->actingAs($admin)
        ->get(route('admin.jobs.edit', $job))
        ->assertOk()
        ->assertSee('Backend Engineer');

    $this->actingAs($admin)
        ->patch(route('admin.jobs.update', ['job' => $job->id]), [
            'title' => 'Admin Edited Title',
            'role' => 'Engineer',
            'employment_type' => 'contract',
            'work_preference' => 'hybrid',
            'description' => 'Edited by admin.',
            'status' => 'open',
            'required_skills' => 'Vue, Inertia',
            'salary_currency' => 'NGN',
            'salary_period' => 'yearly',
        ])
        ->assertRedirect(route('admin.jobs'))
        ->assertSessionHasNoErrors();

    $fresh = $job->fresh();

    expect($fresh->title)->toBe('Admin Edited Title')
        ->and($fresh->employment_type)->toBe('contract')
        ->and($fresh->status)->toBe('open')
        ->and($fresh->required_skills_json)->toBe(['Vue', 'Inertia'])
        ->and($fresh->published_at)->not->toBeNull();
});

it('allows an admin to deactivate and reactivate a job without editing it', function () {
    $employer = User::factory()->create(['role' => 'employer']);
    $admin = User::factory()->create(['role' => 'admin']);
    $job = jobForEmployer($employer);

    expect($job->status)->toBe('open');

    $this->actingAs($admin)
        ->post(route('admin.jobs.toggle-status', ['job' => $job->id]))
        ->assertRedirect(route('admin.jobs'))
        ->assertSessionHas('success');

    expect($job->fresh()->status)->toBe('paused');

    $this->actingAs($admin)
        ->post(route('admin.jobs.toggle-status', ['job' => $job->id]))
        ->assertRedirect(route('admin.jobs'));

    $fresh = $job->fresh();

    expect($fresh->status)->toBe('open')
        ->and($fresh->published_at)->not->toBeNull();
});

it('allows an admin to delete any job and forbids candidates from admin job actions', function () {
    $employer = User::factory()->create(['role' => 'employer']);
    $admin = User::factory()->create(['role' => 'admin']);
    $candidate = User::factory()->create(['role' => 'candidate']);
    $job = jobForEmployer($employer);
    $jobId = $job->id;

    // Candidates cannot touch admin job endpoints.
    $this->actingAs($candidate)
        ->get(route('admin.jobs.edit', ['job' => $jobId]))
        ->assertForbidden();

    $this->actingAs($candidate)
        ->delete(route('admin.jobs.destroy', ['job' => $jobId]))
        ->assertForbidden();

    // Admin can delete.
    $this->actingAs($admin)
        ->delete(route('admin.jobs.destroy', ['job' => $jobId]))
        ->assertRedirect(route('admin.jobs'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('job_listings', ['id' => $jobId]);
});

it('lets an employer delete a job after a candidate has been hired, cleaning up applications but keeping conversations', function () {
    $employer = User::factory()->create(['role' => 'employer']);
    $candidate = User::factory()->create(['role' => 'candidate']);
    $job = jobForEmployer($employer, ['status' => 'filled']);

    DB::table('applications')->insert([
        'job_id' => $job->id,
        'candidate_id' => $candidate->id,
        'status' => 'hired',
        'applied_at' => now(),
        'hired_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $conversationId = DB::table('conversations')->insertGetId([
        'employer_id' => $employer->id,
        'candidate_id' => $candidate->id,
        'job_id' => $job->id,
        'last_message_at' => now(),
    ]);

    // The list shows a Delete action for hired/filled jobs.
    $this->actingAs($employer)
        ->get(route('employer.jobs.index'))
        ->assertOk()
        ->assertSee(route('employer.jobs.destroy', ['job' => $job->id]));

    $this->actingAs($employer)
        ->delete(route('employer.jobs.destroy', ['job' => $job->id]))
        ->assertRedirect(route('employer.jobs.index'))
        ->assertSessionHas('success');

    expect(Job::find($job->id))->toBeNull()
        ->and(DB::table('applications')->where('job_id', $job->id)->count())->toBe(0)
        ->and(DB::table('conversations')->find($conversationId)?->job_id)->toBeNull();
});

it('renders working action links on the admin jobs index', function () {
    $employer = User::factory()->create(['role' => 'employer']);
    $admin = User::factory()->create(['role' => 'admin']);
    $job = jobForEmployer($employer, [
        'salary_min' => 500000,
        'salary_max' => 800000,
        'salary_currency' => 'NGN',
        'salary_period' => 'monthly',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.jobs'))
        ->assertOk()
        ->assertSee(route('admin.jobs.edit', $job))
        ->assertSee(route('admin.jobs.show', $job))
        ->assertSee(route('admin.jobs.toggle-status', $job))
        ->assertSee(route('admin.jobs.destroy', $job))
        ->assertSee('₦500,000 – ₦800,000 / Month');
});

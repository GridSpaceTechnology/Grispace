<?php

use App\Models\CandidateMedia;
use App\Models\CandidateProfile;
use App\Models\User;
use App\Services\ProfileCompletionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function makeCandidate(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'role' => 'candidate',
        'onboarding_completed' => true,
    ], $attributes));
}

function seedRequiredSections(User $user, array $sections): void
{
    $user->candidateProfile()->create([
        'current_role' => 'Developer',
        'desired_role' => 'Senior Developer',
        'years_of_experience' => 4,
        'industry' => 'Tech',
    ]);

    if (in_array('skills', $sections, true)) {
        $user->candidateSkills()->create(['skill_name' => 'PHP', 'proficiency_level' => 5]);
    }

    if (in_array('experience', $sections, true)) {
        $user->candidateExperiences()->create(['company' => 'ACME', 'role' => 'Developer', 'duration' => '4 years']);
    }

    if (in_array('education', $sections, true)) {
        $user->candidateEducation()->create(['institution' => 'State Uni', 'qualification' => 'BSc', 'year_completed' => 2019]);
    }

    if (in_array('assessment', $sections, true)) {
        $user->candidateAssessment()->create(['skill_score' => 80]);
    }

    if (in_array('personality', $sections, true)) {
        $user->personalityProfile()->create(['assessment_completed' => true]);
    }

    if (in_array('preferences', $sections, true)) {
        $user->candidatePreferences()->create(['organizational_type' => 'Startup']);
    }
}

test('profile completion is low for a fresh candidate', function () {
    $user = makeCandidate();

    $percentage = app(ProfileCompletionService::class)->percentage($user);

    expect($percentage)->toBeLessThan(100);
});

test('completion reaches 100 from required onboarding sections without photo or resume', function () {
    $user = makeCandidate();
    seedRequiredSections($user, ['skills', 'experience', 'education', 'assessment', 'personality', 'preferences']);

    $service = app(ProfileCompletionService::class);

    expect($service->percentage($user))->toBe(100)
        ->and($service->firstIncompleteStep($user))->toBeNull()
        ->and($service->complete($user))->toBeTrue();
});

test('optional profile settings fields do not count toward completion', function () {
    $user = makeCandidate();
    $user->update(['profile_photo_path' => 'profile-photos/me.jpg']);
    $user->candidateMedia()->create([
        'cv_path' => 'cvs/'.$user->id.'/resume.pdf',
        'role_video_url' => 'https://youtube.com/watch?v=abc',
        'linkedin_url' => 'https://linkedin.com/in/me',
        'portfolio_links_json' => ['https://portfolio.example'],
    ]);

    expect(app(ProfileCompletionService::class)->percentage($user))->toBe(0);
});

test('candidate dashboard shows continue onboarding button when incomplete', function () {
    $user = makeCandidate();

    $response = $this->actingAs($user)->get(route('candidate.dashboard'));

    $response->assertOk()
        ->assertSee('Continue onboarding');
});

test('candidate dashboard hides continue onboarding button when profile is complete', function () {
    $user = makeCandidate();
    seedRequiredSections($user, ['skills', 'experience', 'education', 'assessment', 'personality', 'preferences']);

    $response = $this->actingAs($user)->get(route('candidate.dashboard'));

    $response->assertOk()
        ->assertSee('Your profile is complete!')
        ->assertDontSee('Continue onboarding');
});

test('dashboard continue onboarding points at the first incomplete step', function (array $sections, int $expectedStep) {
    $user = makeCandidate();
    seedRequiredSections($user, $sections);

    $this->actingAs($user)->get(route('candidate.dashboard'))
        ->assertOk()
        ->assertSee('Continue onboarding')
        ->assertSee(route('candidate.onboarding.step', ['step' => $expectedStep]));
})->with([
    'skills missing' => [[], 2],
    'experience missing' => [['skills'], 2],
    'education missing' => [['skills', 'experience'], 2],
    'assessment missing' => [['skills', 'experience', 'education'], 3],
    'personality missing' => [['skills', 'experience', 'education', 'assessment'], 4],
    'preferences missing' => [['skills', 'experience', 'education', 'assessment', 'personality'], 6],
]);

test('candidate who skipped onboarding can resume the flow', function () {
    $user = makeCandidate();

    $this->actingAs($user)->get(route('candidate.onboarding'))
        ->assertOk()
        ->assertSee('Step 1 of 8');

    $this->actingAs($user)->get(route('candidate.onboarding.step', ['step' => 1]))
        ->assertOk();
});

test('fully onboarded candidate cannot re-enter onboarding', function () {
    $user = makeCandidate();
    seedRequiredSections($user, ['skills', 'experience', 'education', 'assessment', 'personality', 'preferences']);

    $this->actingAs($user)->get(route('candidate.onboarding.step', ['step' => 1]))
        ->assertRedirect(route('candidate.dashboard'));
});

test('candidate can upload a resume from their settings', function () {
    Storage::fake('public');

    $user = makeCandidate();

    CandidateProfile::create(['user_id' => $user->id]);
    $user->candidateMedia()->create(['cv_path' => '', 'role_video_url' => '']);

    $response = $this->actingAs($user)->patch(route('candidate.profile.update'), [
        'resume' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
    ]);

    $response->assertRedirect(route('candidate.profile.edit'));

    $media = $user->candidateMedia->fresh();

    expect($media->cv_path)->not->toBeEmpty();
    Storage::disk('public')->assertExists($media->cv_path);
});

test('updating settings with optional fields does not change onboarding completion', function () {
    Storage::fake('public');

    $user = makeCandidate();
    $user->candidateProfile()->create([]);

    $before = app(ProfileCompletionService::class)->percentage($user);

    $this->actingAs($user)->patch(route('candidate.profile.update'), [
        'resume' => UploadedFile::fake()->create('resume.pdf', 100, 'application/pdf'),
        'linkedin_url' => 'https://linkedin.com/in/me',
        'role_video_url' => 'https://youtube.com/watch?v=abc',
        'portfolio_url' => 'https://portfolio.example',
    ])->assertRedirect(route('candidate.profile.edit'));

    expect(app(ProfileCompletionService::class)->percentage($user))->toBe($before);
});

test('candidate profile settings does not show the onboarding completion panel', function () {
    $user = makeCandidate();

    $this->actingAs($user)->get(route('candidate.profile.edit'))
        ->assertOk()
        ->assertDontSee('Profile Completion');
});

test('employer can view and download a candidate resume', function () {
    Storage::fake('public');

    $candidate = makeCandidate();
    Storage::disk('public')->put('cvs/'.$candidate->id.'/resume.pdf', 'fake resume');
    CandidateMedia::create([
        'user_id' => $candidate->id,
        'cv_path' => 'cvs/'.$candidate->id.'/resume.pdf',
    ]);

    $employer = User::factory()->create(['role' => 'employer']);

    $expectedName = Str::slug($candidate->name).'-resume.pdf';

    $this->actingAs($employer)->get(route('employer.resume.view', $candidate))
        ->assertOk()
        ->assertHeader('Content-Disposition', 'inline; filename="'.$expectedName.'"');
    $this->actingAs($employer)->get(route('employer.resume.download', $candidate))
        ->assertOk()
        ->assertHeader('Content-Disposition', 'attachment; filename='.$expectedName);
});

test('candidate can view and download their own resume', function () {
    Storage::fake('public');

    $candidate = makeCandidate();
    Storage::disk('public')->put('cvs/'.$candidate->id.'/resume.pdf', 'fake resume');
    CandidateMedia::create([
        'user_id' => $candidate->id,
        'cv_path' => 'cvs/'.$candidate->id.'/resume.pdf',
    ]);

    $expectedName = Str::slug($candidate->name).'-resume.pdf';

    $this->actingAs($candidate)->get(route('candidate.resume.view'))
        ->assertOk()
        ->assertHeader('Content-Disposition', 'inline; filename="'.$expectedName.'"');
    $this->actingAs($candidate)->get(route('candidate.resume.download'))
        ->assertOk()
        ->assertHeader('Content-Disposition', 'attachment; filename='.$expectedName);
});

test('candidate resume view 404s when no resume is uploaded', function () {
    $candidate = makeCandidate();

    $this->actingAs($candidate)->get(route('candidate.resume.view'))->assertNotFound();
});

test('employer cannot view a resume that does not exist', function () {
    $candidate = makeCandidate();
    $employer = User::factory()->create(['role' => 'employer']);

    $this->actingAs($employer)->get(route('employer.resume.view', $candidate))
        ->assertNotFound();
});

test('candidate can add a portfolio website during onboarding step 8', function () {
    Storage::fake('public');

    $user = User::factory()->create(['role' => 'candidate', 'onboarding_completed' => false]);

    $this->actingAs($user)->post(route('candidate.onboarding.store', ['step' => 8]), [
        'cv_path' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        'portfolio_url' => 'https://myportfolio.example',
        'linkedin_url' => 'https://linkedin.com/in/me',
        'github_url' => 'https://github.com/me',
    ])->assertRedirect();

    $media = $user->candidateMedia()->first();

    expect($media)->not->toBeNull();
    expect($media->portfolio_primary_url)->toBe('https://myportfolio.example');
});

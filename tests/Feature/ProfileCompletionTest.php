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

test('profile completion is low for a fresh candidate', function () {
    $user = makeCandidate();

    $percentage = app(ProfileCompletionService::class)->percentage($user);

    expect($percentage)->toBeLessThan(100);
});

test('profile completion reaches 100 only when photo and resume are present', function () {
    Storage::fake('public');

    $user = makeCandidate();

    $user->update(['profile_photo_path' => 'profile-photos/me.jpg']);

    CandidateProfile::create([
        'user_id' => $user->id,
        'current_role' => 'Developer',
        'desired_role' => 'Senior Developer',
        'years_of_experience' => 4,
        'industry' => 'Tech',
    ]);

    $user->candidateSkills()->create([
        'skill_name' => 'PHP',
        'proficiency_level' => 5,
    ]);
    $user->candidateExperiences()->create([
        'company' => 'ACME',
        'role' => 'Developer',
        'duration' => '4 years',
    ]);
    $user->candidateEducation()->create([
        'institution' => 'State Uni',
        'qualification' => 'BSc',
        'year_completed' => 2019,
    ]);
    $user->candidatePreferences()->create([
        'organizational_type' => 'Startup',
    ]);
    $user->candidateAssessment()->create([
        'skill_score' => 80,
    ]);

    $user->personalityProfile()->create(['assessment_completed' => true]);

    $user->candidateMedia()->create([
        'cv_path' => 'cvs/'.$user->id.'/resume.pdf',
        'linkedin_url' => 'https://linkedin.com/in/me',
    ]);

    $service = app(ProfileCompletionService::class);
    $percentage = $service->percentage($user);

    expect($percentage)->toBe(100)
        ->and($service->complete($user))->toBeTrue();
});

test('candidate dashboard shows complete profile button when incomplete', function () {
    $user = makeCandidate();

    $response = $this->actingAs($user)->get(route('candidate.dashboard'));

    $response->assertOk()
        ->assertSee('Complete your profile');
});

test('candidate dashboard hides complete button when profile is complete', function () {
    Storage::fake('public');

    $user = makeCandidate();

    $user->update(['profile_photo_path' => 'profile-photos/me.jpg']);

    CandidateProfile::create([
        'user_id' => $user->id,
        'current_role' => 'Developer',
        'desired_role' => 'Senior Developer',
        'years_of_experience' => 4,
        'industry' => 'Tech',
    ]);
    $user->candidateSkills()->create(['skill_name' => 'PHP', 'proficiency_level' => 5]);
    $user->candidateExperiences()->create(['company' => 'ACME', 'role' => 'Developer', 'duration' => '4 years']);
    $user->candidateEducation()->create(['institution' => 'State Uni', 'qualification' => 'BSc', 'year_completed' => 2019]);
    $user->candidatePreferences()->create(['organizational_type' => 'Startup']);
    $user->candidateAssessment()->create(['skill_score' => 80]);
    $user->personalityProfile()->create(['assessment_completed' => true]);
    $user->candidateMedia()->create(['cv_path' => 'cvs/x.pdf', 'linkedin_url' => 'https://linkedin.com/in/me']);

    $response = $this->actingAs($user)->get(route('candidate.dashboard'));

    $response->assertOk()
        ->assertSee('Your profile is complete!')
        ->assertDontSee('Complete your profile');
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

test('employer cannot view a resume that does not exist', function () {
    $candidate = makeCandidate();
    $employer = User::factory()->create(['role' => 'employer']);

    $this->actingAs($employer)->get(route('employer.resume.view', $candidate))
        ->assertNotFound();
});

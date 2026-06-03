<?php

use App\Models\CandidateVerification;
use App\Models\User;
use App\Models\VerificationType;
use Database\Seeders\VerificationTypeSeeder;

beforeEach(function () {
    Notification::fake();
    $this->seed(VerificationTypeSeeder::class);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->candidate = User::factory()->create(['role' => 'candidate', 'email_verified_at' => null]);
    $this->identityType = VerificationType::where('slug', 'identity')->first();
});

it('admin can view verification list', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.verifications.index'))
        ->assertOk();
});

it('admin can view pending verifications', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.verifications.pending'))
        ->assertOk();
});

it('admin can view verification details', function () {
    $verification = CandidateVerification::factory()->create([
        'candidate_id' => $this->candidate->id,
        'verification_type_id' => $this->identityType->id,
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.verifications.show', $verification))
        ->assertOk();
});

it('admin can approve a verification', function () {
    $verification = CandidateVerification::factory()->create([
        'candidate_id' => $this->candidate->id,
        'verification_type_id' => $this->identityType->id,
        'status' => 'pending',
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.verifications.approve', $verification))
        ->assertRedirect();

    expect($verification->fresh()->status)->toBe('approved');
    expect($verification->fresh()->reviewed_by)->toBe($this->admin->id);
});

it('admin can reject a verification', function () {
    $verification = CandidateVerification::factory()->create([
        'candidate_id' => $this->candidate->id,
        'verification_type_id' => $this->identityType->id,
        'status' => 'pending',
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.verifications.reject', $verification), [
            'notes' => 'Document is illegible',
        ])
        ->assertRedirect();

    expect($verification->fresh()->status)->toBe('rejected');
    expect($verification->fresh()->notes)->toBe('Document is illegible');
});

it('admin can request more info on verification', function () {
    $verification = CandidateVerification::factory()->create([
        'candidate_id' => $this->candidate->id,
        'verification_type_id' => $this->identityType->id,
        'status' => 'pending',
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.verifications.request-info', $verification), [
            'notes' => 'Please upload a clearer photo',
        ])
        ->assertRedirect();

    expect($verification->fresh()->status)->toBe('info_requested');
});

it('non-admin cannot access admin verification pages', function () {
    $candidate = User::factory()->create(['role' => 'candidate']);

    $this->actingAs($candidate)
        ->get(route('admin.verifications.index'))
        ->assertForbidden();
});

it('admin can view verification stats', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.verifications.stats'))
        ->assertOk();
});

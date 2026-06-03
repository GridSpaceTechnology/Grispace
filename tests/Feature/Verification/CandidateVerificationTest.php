<?php

use App\Models\CandidateVerification;
use App\Models\User;
use App\Models\VerificationType;
use Database\Seeders\VerificationTypeSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Notification::fake();
    $this->seed(VerificationTypeSeeder::class);
    $this->candidate = User::factory()->create([
        'role' => 'candidate',
        'email_verified_at' => null,
    ]);
    $this->identityType = VerificationType::where('slug', 'identity')->first();
});

it('candidate can view their verification dashboard', function () {
    $this->actingAs($this->candidate)
        ->get(route('candidate.verification'))
        ->assertOk();
});

it('candidate can submit a verification with documents', function () {
    Storage::fake('public');

    $this->actingAs($this->candidate)
        ->post(route('candidate.verification.submit', $this->identityType), [
            'documents' => [UploadedFile::fake()->image('id.jpg')],
            'document_names' => ['Government ID'],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('candidate_verifications', [
        'candidate_id' => $this->candidate->id,
        'verification_type_id' => $this->identityType->id,
        'status' => 'pending',
    ]);
});

it('candidate cannot submit duplicate verification', function () {
    CandidateVerification::factory()->create([
        'candidate_id' => $this->candidate->id,
        'verification_type_id' => $this->identityType->id,
        'status' => 'pending',
    ]);

    Storage::fake('public');

    $this->actingAs($this->candidate)
        ->post(route('candidate.verification.submit', $this->identityType), [
            'documents' => [UploadedFile::fake()->image('id.jpg')],
            'document_names' => ['Government ID'],
        ])
        ->assertRedirect()
        ->assertSessionHas('error');
});

it('requires authentication for verification', function () {
    $this->get(route('candidate.verification'))
        ->assertRedirect(route('login'));
});

it('candidate can initiate phone verification', function () {
    $this->actingAs($this->candidate)
        ->post(route('candidate.verification.phone'), [
            'phone_number' => '+1234567890',
        ])
        ->assertRedirect();
});

it('candidate cannot verify phone without number', function () {
    $this->actingAs($this->candidate)
        ->post(route('candidate.verification.phone'))
        ->assertSessionHasErrors('phone_number');
});

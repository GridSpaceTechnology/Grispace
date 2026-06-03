<?php

use App\Models\CandidateVerification;
use App\Models\User;
use App\Models\VerificationType;
use App\Services\TrustScoreService;
use Database\Seeders\VerificationTypeSeeder;

beforeEach(function () {
    $this->seed(VerificationTypeSeeder::class);
    $this->employer = User::factory()->create(['role' => 'employer']);
    $this->candidate = User::factory()->create([
        'role' => 'candidate',
        'onboarding_completed' => true,
        'email_verified_at' => now(),
    ]);
});

it('employer marketplace shows trust score on candidate cards', function () {
    app(TrustScoreService::class)->getOrCreate($this->candidate);

    $this->actingAs($this->employer)
        ->get(route('employer.marketplace.index'))
        ->assertOk();
});

it('employer marketplace filters by identity verified', function () {
    $type = VerificationType::where('slug', 'identity')->first();
    CandidateVerification::factory()->create([
        'candidate_id' => $this->candidate->id,
        'verification_type_id' => $type->id,
        'status' => 'approved',
    ]);

    $response = $this->actingAs($this->employer)
        ->get(route('employer.marketplace.index', ['verified_identity' => '1']))
        ->assertOk();

    $response->assertSee($this->candidate->name);
});

it('employer marketplace filters by trust score minimum', function () {
    app(TrustScoreService::class)->getOrCreate($this->candidate);
    $lowScoreCandidate = User::factory()->create([
        'role' => 'candidate',
        'onboarding_completed' => true,
    ]);
    app(TrustScoreService::class)->getOrCreate($lowScoreCandidate);

    $response = $this->actingAs($this->employer)
        ->get(route('employer.marketplace.index', ['trust_score_min' => 10]))
        ->assertOk();

    $response->assertSee($this->candidate->name);
});

it('employer marketplace filters exclude candidates below trust score', function () {
    $lowScoreCandidate = User::factory()->create([
        'role' => 'candidate',
        'onboarding_completed' => true,
    ]);

    $response = $this->actingAs($this->employer)
        ->get(route('employer.marketplace.index', ['trust_score_min' => 50]))
        ->assertOk();
});

it('candidate detail page shows trust score to employer', function () {
    app(TrustScoreService::class)->getOrCreate($this->candidate);
    $type = VerificationType::where('slug', 'education')->first();
    CandidateVerification::factory()->create([
        'candidate_id' => $this->candidate->id,
        'verification_type_id' => $type->id,
        'status' => 'approved',
    ]);

    $this->actingAs($this->employer)
        ->get(route('employer.marketplace.candidate', $this->candidate))
        ->assertOk()
        ->assertSee('Trust Score');
});

it('public marketplace shows trust badges for candidates', function () {
    app(TrustScoreService::class)->getOrCreate($this->candidate);

    $this->get(route('marketplace.index'))
        ->assertOk();
});

it('public candidate detail shows verification badges', function () {
    $type = VerificationType::where('slug', 'identity')->first();
    CandidateVerification::factory()->create([
        'candidate_id' => $this->candidate->id,
        'verification_type_id' => $type->id,
        'status' => 'approved',
    ]);

    $this->get(route('marketplace.candidate', $this->candidate))
        ->assertOk();
});

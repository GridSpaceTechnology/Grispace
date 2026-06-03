<?php

use App\Models\CandidateVerification;
use App\Models\User;
use App\Models\VerificationType;
use App\Services\TrustScoreService;
use Database\Seeders\VerificationTypeSeeder;

beforeEach(function () {
    $this->seed(VerificationTypeSeeder::class);
});

it('calculates zero score for candidate with no verifications', function () {
    $candidate = User::factory()->create([
        'role' => 'candidate',
        'email_verified_at' => null,
    ]);

    $service = app(TrustScoreService::class);
    $score = $service->calculate($candidate);

    expect($score)->toBe(0);
});

it('calculates email verification score', function () {
    $candidate = User::factory()->create([
        'role' => 'candidate',
        'email_verified_at' => now(),
    ]);

    $service = app(TrustScoreService::class);
    $score = $service->calculate($candidate);

    expect($score)->toBe(TrustScoreService::SCORE_EMAIL);
});

it('calculates phone verification score', function () {
    $candidate = User::factory()->create([
        'role' => 'candidate',
        'email_verified_at' => null,
        'phone_verified_at' => now(),
    ]);

    $service = app(TrustScoreService::class);
    $score = $service->calculate($candidate);

    expect($score)->toBe(TrustScoreService::SCORE_PHONE);
});

it('calculates identity verification score', function () {
    $candidate = User::factory()->create([
        'role' => 'candidate',
        'email_verified_at' => null,
    ]);
    $type = VerificationType::where('slug', 'identity')->first();
    CandidateVerification::factory()->create([
        'candidate_id' => $candidate->id,
        'verification_type_id' => $type->id,
        'status' => 'approved',
    ]);

    $service = app(TrustScoreService::class);
    $score = $service->calculate($candidate);

    expect($score)->toBe(TrustScoreService::SCORE_IDENTITY);
});

it('calculates full score with all verifications', function () {
    $candidate = User::factory()->create([
        'role' => 'candidate',
        'email_verified_at' => now(),
        'phone_verified_at' => now(),
    ]);

    foreach (['identity', 'education', 'employment', 'certification'] as $slug) {
        $type = VerificationType::where('slug', $slug)->first();
        CandidateVerification::factory()->create([
            'candidate_id' => $candidate->id,
            'verification_type_id' => $type->id,
            'status' => 'approved',
        ]);
    }

    $service = app(TrustScoreService::class);
    $score = $service->calculate($candidate);

    expect($score)->toBe(TrustScoreService::MAX_SCORE);
});

it('ignores pending verifications in score', function () {
    $candidate = User::factory()->create([
        'role' => 'candidate',
        'email_verified_at' => null,
    ]);
    $type = VerificationType::where('slug', 'identity')->first();
    CandidateVerification::factory()->create([
        'candidate_id' => $candidate->id,
        'verification_type_id' => $type->id,
        'status' => 'pending',
    ]);

    $service = app(TrustScoreService::class);
    $score = $service->calculate($candidate);

    expect($score)->toBe(0);
});

it('returns correct level for score ranges', function () {
    $service = app(TrustScoreService::class);

    expect($service->getLevel(0))->toBe('Beginner');
    expect($service->getLevel(25))->toBe('Beginner');
    expect($service->getLevel(26))->toBe('Trusted');
    expect($service->getLevel(50))->toBe('Trusted');
    expect($service->getLevel(51))->toBe('Highly Trusted');
    expect($service->getLevel(75))->toBe('Highly Trusted');
    expect($service->getLevel(76))->toBe('Verified Professional');
    expect($service->getLevel(100))->toBe('Verified Professional');
});

it('gets or creates a trust score for candidate', function () {
    $candidate = User::factory()->create([
        'role' => 'candidate',
        'email_verified_at' => now(),
    ]);

    $trustScore = app(TrustScoreService::class)->getOrCreate($candidate);

    expect($trustScore->candidate_id)->toBe($candidate->id);
    expect($trustScore->score)->toBe(TrustScoreService::SCORE_EMAIL);
    expect($trustScore->level)->toBe('Beginner');

    $this->assertDatabaseHas('trust_scores', [
        'candidate_id' => $candidate->id,
        'score' => TrustScoreService::SCORE_EMAIL,
    ]);
});

it('recalculates existing trust score', function () {
    $candidate = User::factory()->create([
        'role' => 'candidate',
        'email_verified_at' => null,
    ]);

    $service = app(TrustScoreService::class);
    $trustScore = $service->getOrCreate($candidate);

    expect($trustScore->score)->toBe(0);

    $candidate->forceFill(['email_verified_at' => now(), 'phone_verified_at' => now()])->save();
    $candidate->refresh();

    $trustScore = $service->recalculateForCandidate($candidate);

    expect($trustScore->score)->toBe(
        TrustScoreService::SCORE_EMAIL + TrustScoreService::SCORE_PHONE
    );
});

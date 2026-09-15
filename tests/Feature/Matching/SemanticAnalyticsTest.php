<?php

use App\Models\CandidateProfile;
use App\Models\SemanticEmbedding;
use App\Models\SemanticHealthEvent;
use App\Models\User;
use App\Services\MatchAnalyticsService;
use App\Services\Semantic\SemanticEmbeddingService;
use Illuminate\Support\Facades\Http;

/**
 * Admin-facing semantic analytics. Aggregate-only: counts and health, never
 * candidate/job identifiers, mirroring the outcome analytics contract.
 */
function phase5AnalyticsUser(): User
{
    return User::factory()->create([
        'role' => 'candidate',
        'name' => 'Anonymous Analyst',
        'email' => 'analyst@'.str()->random(6).'.test',
        'onboarding_completed' => true,
    ]);
}

beforeEach(function () {
    $this->semanticAnalyticsConfig = [
        'matching.semantic.enabled' => config('matching.semantic.enabled'),
        'matching.semantic.provider' => config('matching.semantic.provider'),
        'matching.embedding.model' => config('matching.embedding.model'),
    ];

    config([
        'matching.semantic.enabled' => true,
        'matching.semantic.provider' => 'embeddings',
        'matching.embedding.model' => 'test-embedder',
    ]);
});

afterEach(function () {
    config($this->semanticAnalyticsConfig);
});

it('reports the semantic layer as disabled when it is off', function () {
    config(['matching.semantic.enabled' => false]);

    $report = app(MatchAnalyticsService::class)->semanticReport();

    expect($report['enabled'])->toBeFalse()
        ->and($report['vectors'])->toBe([])
        ->and($report['health'])->toBe([])
        ->and($report['recent_failures'])->toBe(0);
});

it('reports stored vectors and health grouped and aggregate-only', function () {
    Http::fake([
        'embeddings.test/*' => Http::response(['data' => [['embedding' => [0.1]]]], 200),
    ]);

    config([
        'matching.semantic.provider' => 'embeddings',
        'matching.embedding.api_key' => 'secret',
        'matching.embedding.api_url' => 'https://embeddings.test/v1/embeddings',
    ]);

    $candidate = phase5AnalyticsUser();

    CandidateProfile::create([
        'user_id' => $candidate->id,
        'desired_role' => 'Backend Developer',
    ]);

    app(SemanticEmbeddingService::class)->generateForCandidate($candidate);

    $report = app(MatchAnalyticsService::class)->semanticReport();

    expect($report['enabled'])->toBeTrue()
        ->and($report['provider'])->toBe('embeddings')
        ->and(count($report['vectors']))->toBe(1)
        ->and($report['vectors'][0]['entity_type'])->toBe('candidate')
        ->and($report['vectors'][0]['provider'])->toBe('embeddings')
        ->and($report['vectors'][0]['embedding_version'])->toBe(config('matching.embedding.version'))
        ->and($report['vectors'][0]['total'])->toBe(1)
        ->and($report['health'])->toHaveKey('generate:success')
        ->and(array_key_exists('entity_id', $report['vectors'][0]))->toBeFalse()
        ->and(array_key_exists('entity_type', $report['vectors'][0]))->toBeTrue()
        ->and(json_encode($report))->not->toContain($candidate->email)
        ->and(json_encode($report))->not->toContain('Anonymous Analyst');
});

it('surfaces recent generation failures for operators', function () {
    Http::fake([
        'embeddings.test/*' => Http::response(['error' => 'down'], 503),
    ]);

    config([
        'matching.semantic.provider' => 'embeddings',
        'matching.embedding.api_key' => 'secret',
        'matching.embedding.api_url' => 'https://embeddings.test/v1/embeddings',
    ]);

    $candidate = phase5AnalyticsUser();

    CandidateProfile::create([
        'user_id' => $candidate->id,
        'desired_role' => 'Backend Developer',
    ]);

    app(SemanticEmbeddingService::class)->generateForCandidate($candidate);

    $report = app(MatchAnalyticsService::class)->semanticReport();

    expect($report['health'])->toHaveKey('generate:failure')
        ->and($report['health']['generate:failure'])->toBeGreaterThanOrEqual(1)
        ->and($report['recent_failures'])->toBeGreaterThanOrEqual(1)
        ->and(SemanticHealthEvent::where('status', 'failure')->count())->toBeGreaterThanOrEqual(1)
        ->and(SemanticEmbedding::where('entity_type', 'candidate')->count())->toBe(0);
});

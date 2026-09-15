<?php

use App\Models\CandidateProfile;
use App\Models\CandidateSkill;
use App\Models\Company;
use App\Models\Job;
use App\Models\SemanticEmbedding;
use App\Models\SemanticHealthEvent;
use App\Models\User;
use App\Services\Semantic\SemanticEmbeddingService;
use App\Services\Semantic\SemanticRepresentation;
use Illuminate\Support\Facades\Http;

/**
 * Phase 5 embedding lifecycle. Generation is idempotent, versioned, deferred
 * under rate limits, privacy-filtered, and health-logged. It must never run
 * on a provider that is not explicitly configured and enabled.
 */
function phase5EmbedEmployer(string $company = 'Embedix Ltd'): User
{
    $user = User::factory()->create(['role' => 'employer']);

    Company::create([
        'user_id' => $user->id,
        'name' => $company,
        'slug' => str()->slug($company).'-'.str()->random(5),
        'allow_candidate_messages' => true,
    ]);

    return $user;
}

function phase5EmbedJob(User $employer, array $attributes = []): Job
{
    return Job::create(array_merge([
        'employer_id' => $employer->id,
        'title' => 'Backend Engineer',
        'role' => 'Backend Developer',
        'slug' => str()->random(10),
        'employment_type' => 'full_time',
        'work_preference' => 'remote',
        'salary_min' => 500000,
        'salary_max' => 900000,
        'salary_currency' => 'NGN',
        'status' => 'open',
        'description' => 'Build and scale Laravel payment services.',
        'required_skills_json' => ['PHP', 'Laravel', 'MySQL'],
    ], $attributes));
}

function phase5EmbedCandidate(array $profileAttributes = [], array $skills = []): User
{
    $user = User::factory()->create([
        'role' => 'candidate',
        'name' => 'Ada Lovelace',
        'email' => 'ada@lovelace.test',
        'onboarding_completed' => true,
    ]);

    CandidateProfile::create(array_merge([
        'user_id' => $user->id,
        'desired_role' => 'Backend Developer',
        'years_of_experience' => 4,
        'salary_expectation' => 700000,
        'work_preference' => 'remote',
        'location_country' => 'Nigeria',
        'greatest_achievement' => 'Led teams shipping fintech APIs used by thousands.',
    ], $profileAttributes));

    foreach ($skills as $skill) {
        CandidateSkill::create([
            'user_id' => $user->id,
            'skill_name' => $skill,
            'proficiency_level' => 3,
        ]);
    }

    return $user;
}

beforeEach(function () {
    $this->embeddingConfig = [
        'matching.semantic.enabled' => config('matching.semantic.enabled'),
        'matching.semantic.provider' => config('matching.semantic.provider'),
        'matching.embedding.api_key' => config('matching.embedding.api_key'),
        'matching.embedding.model' => config('matching.embedding.model'),
        'matching.embedding.api_url' => config('matching.embedding.api_url'),
        'matching.embedding.version' => config('matching.embedding.version'),
        'matching.embedding.min_interval_ms' => config('matching.embedding.min_interval_ms'),
    ];

    config([
        'matching.semantic.enabled' => true,
        'matching.semantic.provider' => 'embeddings',
        'matching.embedding.api_key' => 'secret-api-key',
        'matching.embedding.model' => 'test-embedder',
        'matching.embedding.api_url' => 'https://embeddings.test/v1/embeddings',
        'matching.embedding.version' => '2026-09-15',
        'matching.embedding.min_interval_ms' => 0,
    ]);
});

afterEach(function () {
    config($this->embeddingConfig);
});

it('stores an embedding for a candidate and sends only the public representation', function () {
    Http::fake([
        'embeddings.test/*' => Http::response(['data' => [['embedding' => [0.1, 0.2, 0.3]]]], 200),
    ]);

    $candidate = phase5EmbedCandidate(skills: ['PHP', 'Laravel']);

    $result = app(SemanticEmbeddingService::class)->generateForCandidate($candidate);

    expect($result)->toBe('generated');

    $row = SemanticEmbedding::where('entity_type', 'candidate')->where('entity_id', $candidate->id)->first();

    expect($row)->not->toBeNull()
        ->and($row->embedding)->toBe([0.1, 0.2, 0.3])
        ->and($row->dimensions)->toBe(3)
        ->and($row->provider)->toBe('embeddings')
        ->and($row->model)->toBe('test-embedder')
        ->and($row->embedding_version)->toBe('2026-09-15')
        ->and(strlen((string) $row->content_hash))->toBe(64);

    Http::assertSentCount(1);

    $sent = Http::recorded()[0][0];

    expect($sent->body())
        ->toContain('backend developer')
        ->toContain('php')
        ->not->toContain('Ada')
        ->not->toContain('ada@lovelace.test');
});

it('generation is idempotent and never re-sends for an unchanged representation', function () {
    Http::fake([
        'embeddings.test/*' => Http::response(['data' => [['embedding' => [0.1, 0.2, 0.3]]]], 200),
    ]);

    $service = app(SemanticEmbeddingService::class);
    $candidate = phase5EmbedCandidate(skills: ['PHP', 'Laravel']);

    expect($service->generateForCandidate($candidate))->toBe('generated');
    expect($service->generateForCandidate($candidate))->toBe('current');
    expect($service->generateForCandidate($candidate))->toBe('current');

    Http::assertSentCount(1);
});

it('regenerates exactly one stored vector when the representation changes', function () {
    Http::fake([
        'embeddings.test/*' => Http::response(['data' => [['embedding' => [0.5, 0.6, 0.7]]]], 200),
    ]);

    $service = app(SemanticEmbeddingService::class);
    $candidate = phase5EmbedCandidate();
    $hashBuilder = fn (User $user) => app(SemanticRepresentation::class)->forCandidate($user)['content_hash'];

    $oldHash = $hashBuilder($candidate);

    $service->generateForCandidate($candidate);

    $candidate->candidateProfile->update(['greatest_achievement' => 'Now I build Go services instead.']);
    $newHash = $hashBuilder($candidate);

    expect($newHash)->not->toBe($oldHash)
        ->and($service->generateForCandidate($candidate))->toBe('generated');

    $rows = SemanticEmbedding::where('entity_type', 'candidate')->where('entity_id', $candidate->id)->get();

    expect($rows)->toHaveCount(1)
        ->and($rows[0]->content_hash)->toBe($newHash);

    Http::assertSentCount(2);
});

it('bumps the embedding version by storing a new row instead of overwriting history', function () {
    Http::fake([
        'embeddings.test/*' => Http::response(['data' => [['embedding' => [0.1]]]], 200),
    ]);

    $service = app(SemanticEmbeddingService::class);
    $candidate = phase5EmbedCandidate();

    expect($service->generateForCandidate($candidate))->toBe('generated');

    config(['matching.embedding.version' => '2026-10-01']);

    expect($service->generateForCandidate($candidate))->toBe('generated');

    $rows = SemanticEmbedding::where('entity_type', 'candidate')->where('entity_id', $candidate->id)->get('embedding_version');

    expect($rows->pluck('embedding_version')->sort()->values()->all())->toBe(['2026-09-15', '2026-10-01']);

    Http::assertSentCount(2);
});

it('defers generation when the provider rate-limit interval has not elapsed', function () {
    config(['matching.embedding.min_interval_ms' => 10000]);

    Http::fake([
        'embeddings.test/*' => Http::response(['data' => [['embedding' => [0.1]]]], 200),
    ]);

    $service = app(SemanticEmbeddingService::class);
    $candidate = phase5EmbedCandidate();

    expect($service->generateForCandidate($candidate))->toBe('generated');

    $candidate->candidateProfile->update(['greatest_achievement' => 'Completely different achievement text now.']);

    expect($service->generateForCandidate($candidate))->toBe('deferred');

    Http::assertSentCount(1);
});

it('logs a health event and refuses to store on provider failure', function () {
    Http::fake([
        'embeddings.test/*' => Http::response(['error' => 'boom'], 500),
    ]);

    $service = app(SemanticEmbeddingService::class);
    $candidate = phase5EmbedCandidate();

    expect($service->generateForCandidate($candidate))->toBe('failed');

    expect(SemanticEmbedding::where('entity_type', 'candidate')->count())->toBe(0);

    $event = SemanticHealthEvent::where('action', 'generate')->first();

    expect($event)->not->toBeNull()
        ->and($event->status)->toBe('failure')
        ->and($event->provider)->toBe('embeddings')
        ->and($event->error)->toContain('boom');
});

it('never generates when the embedding provider is not enabled or configured', function () {
    Http::fake();

    $candidate = phase5EmbedCandidate();
    $service = app(SemanticEmbeddingService::class);

    config(['matching.semantic.provider' => 'lexical']);
    expect($service->generateForCandidate($candidate))->toBe('disabled');

    config(['matching.semantic.provider' => 'embeddings', 'matching.embedding.api_key' => '']);
    expect($service->generateForCandidate($candidate))->toBe('disabled');

    config(['matching.embedding.api_key' => 'secret', 'matching.semantic.enabled' => false]);
    expect($service->generateForCandidate($candidate))->toBe('disabled');

    Http::assertNothingSent();
});

it('computes cosine similarity correctly for equal and orthogonal vectors', function () {
    $service = app(SemanticEmbeddingService::class);

    expect($service->cosine([1.0, 0.0], [1.0, 0.0]))->toBe(1.0)
        ->and($service->cosine([1.0, 0.0], [0.0, 1.0]))->toBe(0.0)
        ->and($service->cosine([1.0, 1.0], [1.0, 0.0]))->toBeGreaterThan(0.7)
        ->and($service->cosine([1.0, 1.0], [1.0, 0.0]))->toBeLessThan(0.72)
        ->and($service->cosine([], [1.0]))->toBe(0.0)
        ->and($service->cosine([1.0], [1.0, 0.0]))->toBe(0.0);
});

it('stores job embeddings and finds the active vector per entity', function () {
    Http::fake([
        'embeddings.test/*' => Http::response(['data' => [['embedding' => [0.9, 0.1]]]], 200),
    ]);

    $service = app(SemanticEmbeddingService::class);
    $job = phase5EmbedJob(phase5EmbedEmployer());

    expect($service->generateForJob($job))->toBe('generated')
        ->and($service->vectorFor('job', $job->id))->toBe([0.9, 0.1])
        ->and($service->vectorFor('candidate', $job->id))->toBeNull();
});

<?php

use App\Services\Semantic\LexicalSemanticProvider;

/**
 * Provider-level truths for the deterministic semantic layer. These lock in
 * that different wording of the SAME professional facts scores higher, that
 * distinct skills sharing a prefix stay distinct, that cross-domain pairs are
 * pulled down, and that explanations stay evidence-based and personal-data-free.
 */
function phase5Payload(array $overrides = []): array
{
    return array_merge([
        'id' => 1,
        'entity_type' => 'candidate',
        'role' => '',
        'skills' => [],
        'description' => '',
        'domain' => null,
        'content_hash' => 'test',
    ], $overrides);
}

function phase5Context(array $overrides = []): array
{
    return array_merge([
        'candidate_domain' => null,
        'job_domain' => null,
        'profile_match_score' => 70,
        'job_title' => 'Backend Engineer',
        'perspective' => 'candidate',
    ], $overrides);
}

beforeEach(function () {
    $this->provider = app(LexicalSemanticProvider::class);
});

it('treats Backend Developer and Backend Engineer as the same role', function () {
    $candidate = phase5Payload(['role' => 'Backend Developer']);
    $job = phase5Payload(['role' => 'Backend Engineer', 'entity_type' => 'job']);

    $result = $this->provider->similarities($candidate, $job, phase5Context());

    expect($result['role_score'])->toBe(100)
        ->and($result['score'])->toBeGreaterThan(60);
});

it('treats server-side engineer as a semantic sibling of backend developer', function () {
    $candidate = phase5Payload(['role' => 'Backend Developer']);
    $job = phase5Payload(['role' => 'Server-side Engineer', 'entity_type' => 'job']);

    $result = $this->provider->similarities($candidate, $job, phase5Context());

    expect($result['role_score'])->toBeGreaterThanOrEqual(80);
});

it('aliases PostgreSQL and Postgres into one skill', function () {
    $candidate = phase5Payload(['skills' => ['Postgres']]);
    $job = phase5Payload(['skills' => ['PostgreSQL'], 'entity_type' => 'job']);

    $result = $this->provider->similarities($candidate, $job, phase5Context());

    expect($result['skill_score'])->toBe(100)
        ->and(collect($result['details']['skill_matches'])->first()['alias'])->toBeTrue()
        ->and(collect($result['reasons'])->first())->toContain('Postgres');
});

it('aliases RESTful APIs with REST APIs', function () {
    $candidate = phase5Payload(['skills' => ['REST APIs']]);
    $job = phase5Payload(['skills' => ['RESTful'], 'entity_type' => 'job']);

    $result = $this->provider->similarities($candidate, $job, phase5Context());

    expect($result['skill_score'])->toBe(100);
});

it('never equates Java with JavaScript', function () {
    $javaCandidate = phase5Payload(['skills' => ['Java']]);
    $javascriptJob = phase5Payload(['skills' => ['JavaScript'], 'entity_type' => 'job']);
    $javaJob = phase5Payload(['skills' => ['Java'], 'entity_type' => 'job']);

    $cross = $this->provider->similarities($javaCandidate, $javascriptJob, phase5Context());
    $same = $this->provider->similarities($javaCandidate, $javaJob, phase5Context());

    expect($cross['skill_score'])->toBeLessThan(50)
        ->and($cross['skill_score'])->toBeLessThan($same['skill_score'])
        ->and($same['skill_score'])->toBe(100);
});

it('pulls down scores for pairs from different professional domains', function () {
    $candidate = phase5Payload(['role' => 'Software Engineer', 'skills' => ['PHP'], 'domain' => 'technology']);
    $job = phase5Payload(['role' => 'Accountant', 'skills' => ['PHP'], 'entity_type' => 'job', 'domain' => 'finance']);

    $sameDomain = $this->provider->similarities($candidate, phase5Payload(['role' => 'Backend Engineer', 'domain' => 'technology', 'entity_type' => 'job']), phase5Context());
    $crossDomain = $this->provider->similarities($candidate, $job, phase5Context());

    expect($crossDomain['domain_score'])->toBe(0)
        ->and($crossDomain['score'])->toBeLessThan($sameDomain['score']);
});

it('is neutral rather than broken on empty inputs', function () {
    $candidate = phase5Payload();
    $job = phase5Payload(['entity_type' => 'job']);

    $result = $this->provider->similarities($candidate, $job, phase5Context());

    expect($result)->not->toBeNull()
        ->and($result['role_score'])->toBe(50)
        ->and($result['skill_score'])->toBe(50);
});

it('returns a bounded number of evidence-based reasons with no AI phrasing', function () {
    $candidate = phase5Payload([
        'role' => 'Backend Developer',
        'skills' => ['Laravel', 'MySQL'],
        'description' => 'Built distributed APIs and scaled Laravel services for high-traffic fintech products.',
        'domain' => 'technology',
    ]);
    $job = phase5Payload([
        'role' => 'Backend Engineer',
        'skills' => ['Laravel', 'PostgreSQL'],
        'description' => 'We are hiring a backend engineer to build resilient payment services using Laravel, MySQL and Docker.',
        'entity_type' => 'job',
        'domain' => 'technology',
    ]);

    $result = $this->provider->similarities($candidate, $job, phase5Context(['job_title' => 'Backend Engineer']));

    expect($result['reasons'])->not->toBeEmpty()
        ->and(count($result['reasons']))->toBeLessThanOrEqual((int) config('matching.semantic.reasons_max', 3))
        ->and(implode(' ', $result['reasons']))->not->toContain('AI')
        ->and(implode(' ', $result['reasons']))->not->toContain('%')
        ->and(implode(' ', $result['reasons']))->not->toContain('we recommend');
});

it('keeps reasons free of any personal data', function () {
    $candidate = phase5Payload([
        'id' => 99,
        'role' => 'Backend Developer',
        'skills' => ['Postgres'],
        'description' => '',
        'domain' => 'technology',
    ]);
    $job = phase5Payload([
        'id' => 77,
        'role' => 'Backend Engineer',
        'skills' => ['PostgreSQL'],
        'description' => 'Hiring a backend engineer skilled in PostgreSQL.',
        'entity_type' => 'job',
        'domain' => 'technology',
    ]);

    $result = $this->provider->similarities($candidate, $job, phase5Context());

    expect(implode(' ', $result['reasons']))
        ->not->toContain('Ada')
        ->not->toContain('ada@gridspace')
        ->not->toContain('+234');
});

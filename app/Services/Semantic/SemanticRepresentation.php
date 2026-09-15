<?php

namespace App\Services\Semantic;

use App\Models\Job;
use App\Models\User;
use App\Services\SearchIntentParser;

/**
 * Builds the privacy-filtered, canonical representation used for semantic
 * similarity (both the deterministic lexical provider and the optional
 * embedding provider).
 *
 * Only public professional attributes are included: roles, skills, industry
 * and free-text achievements/descriptions. Names, emails, phone numbers and
 * private notes are never part of a representation, so no provider input or
 * stored vector can ever leak them.
 */
class SemanticRepresentation
{
    public function __construct(protected SearchIntentParser $parser) {}

    /**
     * @return array{id: int, entity_type: string, role: string, skills: array, description: string, domain: ?string, content_hash: string}
     */
    public function forCandidate(User $candidate): array
    {
        $profile = $candidate->candidateProfile;

        $role = trim(($profile?->current_role ?? '').' '.($profile?->desired_role ?? ''));
        $skills = $candidate->candidateSkills()
            ->pluck('skill_name')
            ->filter()
            ->map(fn (string $skill) => mb_strtolower(trim($skill)))
            ->filter(fn (string $skill) => $skill !== '')
            ->unique()
            ->values()
            ->all();

        $description = trim(($profile?->greatest_achievement ?? ''));
        $industry = mb_strtolower(trim((string) ($profile?->industry ?? '')));

        if ($industry !== '') {
            $description = trim($description.' '.$industry);
        }

        return $this->payload('candidate', $candidate->id, [
            'role' => mb_strtolower($role),
            'skills' => $skills,
            'description' => mb_strtolower($description),
            'domain' => $this->parser->detectDomain(trim((string) ($profile?->desired_role ?? ''))),
        ]);
    }

    /**
     * @return array{id: int, entity_type: string, role: string, skills: array, description: string, domain: ?string, content_hash: string}
     */
    public function forJob(Job $job): array
    {
        $skills = array_values(array_unique(array_filter(
            array_merge(
                array_map(fn (string $skill) => mb_strtolower(trim($skill)), array_filter(array_map('trim', $job->getRequiredSkills()))),
                $job->jobSkills->map(fn ($jobSkill) => mb_strtolower(trim((string) ($jobSkill->skill?->name ?? $jobSkill->skill_id))))->all(),
            ),
            fn (string $skill) => $skill !== '',
        )));

        $description = trim(
            ($job->description ?? '').' '
            .($job->responsibilities ?? '').' '
            .($job->requirements ?? '')
        );

        if (config('matching.semantic.include_company', true) && $job->company !== null) {
            $company = trim(($job->company->name ?? '').' '.($job->company->description ?? ''));
            $description = trim($description.' '.$company);
        }

        $roleText = trim(($job->title ?? '').' '.($job->role ?? ''));

        return $this->payload('job', $job->id, [
            'role' => mb_strtolower($roleText),
            'skills' => $skills,
            'description' => mb_strtolower($description),
            'domain' => $this->parser->detectDomain($roleText),
        ]);
    }

    /**
     * Flatten a representation into the single text payload sent to a hosted
     * embedding model. Contains only the public representation fields.
     */
    public function toText(array $payload): string
    {
        $role = (string) ($payload['role'] ?? '');
        $skills = implode(', ', (array) ($payload['skills'] ?? []));
        $description = (string) ($payload['description'] ?? '');

        return trim('Role: '.$role.' Skills: '.$skills.' Background: '.$description);
    }

    private function payload(string $entityType, int $entityId, array $data): array
    {
        $data['entity_type'] = $entityType;
        $data['id'] = $entityId;
        $data['content_hash'] = hash('sha256', json_encode(['entity' => $entityType, 'id' => $entityId, 'data' => $data]));

        return $data;
    }
}

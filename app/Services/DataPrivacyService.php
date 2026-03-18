<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Consent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DataPrivacyService
{
    public function exportUserData(User $user): array
    {
        return [
            'profile' => $user->toArray(),
            'candidate_profile' => $user->candidateProfile?->toArray(),
            'skills' => $user->candidateSkills->toArray(),
            'experiences' => $user->candidateExperiences->toArray(),
            'education' => $user->candidateEducation->toArray(),
            'signals' => $user->candidateSignals->toArray(),
            'preferences' => $user->candidatePreferences?->toArray(),
            'assessment' => $user->candidateAssessment?->toArray(),
            'applications' => $user->jobApplications->toArray(),
            'consents' => $user->consents->toArray(),
            'exported_at' => now()->toIso8601String(),
        ];
    }

    public function softDeleteUser(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->update([
                'email' => 'deleted_'.$user->id.'_'.time().'@deleted.local',
                'name' => 'Deleted User',
                'password' => Hash::make(bin2hex(random_bytes(16))),
                'is_suspended' => true,
                'onboarding_completed' => false,
            ]);

            $user->candidateProfile?->delete();
            $user->candidateSkills()->delete();
            $user->candidateExperiences()->delete();
            $user->candidateEducation()->delete();
            $user->candidateSignals()->delete();
            $user->candidatePreferences()?->delete();
            $user->candidateAssessment()?->delete();
            $user->candidateMedia()?->delete();

            AuditLog::log(
                $user->id,
                AuditLog::ACTION_DELETE,
                'user',
                (string) $user->id,
                [],
                ['action' => 'soft_delete', 'reason' => 'user_request']
            );
        });
    }

    public function anonymizeData(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->update([
                'name' => 'Anonymous User',
                'email' => 'anonymized_'.$user->id.'@anonymous.local',
            ]);

            $user->candidateProfile?->update([
                'current_role' => null,
                'greatest_achievement' => null,
            ]);

            $user->candidateSkills()->delete();
            $user->candidateExperiences()->delete();
            $user->candidateEducation()->delete();

            AuditLog::log(
                $user->id,
                AuditLog::ACTION_UPDATE,
                'user',
                (string) $user->id,
                [],
                ['action' => 'anonymize']
            );
        });
    }

    public function getDataProcessingConsent(User $user): array
    {
        $requiredConsents = [
            Consent::CONSENT_DATA_PROCESSING => 'Data Processing',
            Consent::CONSENT_PROFILE_VISIBILITY => 'Profile Visibility',
        ];

        $optionalConsents = [
            Consent::CONSENT_MARKETING => 'Marketing Communications',
            Consent::CONSENT_THIRD_PARTY => 'Third Party Sharing',
            Consent::CONSENT_EMAIL_NOTIFICATIONS => 'Email Notifications',
        ];

        $userConsents = $user->consents()
            ->pluck('consent_given', 'consent_type')
            ->toArray();

        return [
            'required' => collect($requiredConsents)->mapWithKeys(fn ($label, $type) => [
                $type => [
                    'label' => $label,
                    'granted' => $userConsents[$type] ?? false,
                    'required' => true,
                ],
            ]),
            'optional' => collect($optionalConsents)->mapWithKeys(fn ($label, $type) => [
                $type => [
                    'label' => $label,
                    'granted' => $userConsents[$type] ?? false,
                    'required' => false,
                ],
            ]),
        ];
    }

    public function canDelete(User $user): bool
    {
        $activeApplications = $user->jobApplications()
            ->whereIn('status', ['applied', 'viewed', 'shortlisted', 'interview'])
            ->exists();

        return ! $activeApplications;
    }

    public function getDeletionImpact(User $user): array
    {
        return [
            'can_delete' => $this->canDelete($user),
            'affected_items' => [
                'candidate_profile' => (bool) $user->candidateProfile,
                'skills_count' => $user->candidateSkills()->count(),
                'experiences_count' => $user->candidateExperiences()->count(),
                'applications_count' => $user->jobApplications()->count(),
                'active_applications' => $user->jobApplications()
                    ->whereIn('status', ['applied', 'viewed', 'shortlisted', 'interview'])
                    ->count(),
            ],
        ];
    }
}

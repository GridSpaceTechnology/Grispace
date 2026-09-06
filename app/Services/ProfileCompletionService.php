<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class ProfileCompletionService
{
    /**
     * Required onboarding sections that make up 100% of a candidate profile.
     *
     * Completion reflects only the required onboarding sections. Optional
     * profile-settings fields (profile photo, resume, intro video, social
     * links) are deliberately excluded and never reduce the percentage.
     *
     * @return array<int, array{key: string, label: string, weight: int, earned: bool}>
     */
    public function items(User $candidate): array
    {
        $profile = $candidate->candidateProfile;

        $basicProfile = $profile
            && $profile->current_role
            && $profile->desired_role
            && $profile->years_of_experience
            && $profile->industry;

        $hasSkills = $candidate->candidateSkills()->count() > 0;
        $hasExperience = $candidate->candidateExperiences()->count() > 0;
        $hasEducation = $candidate->candidateEducation()->count() > 0;

        $preferences = $candidate->candidatePreferences;
        $hasPreferences = (bool) ($preferences?->organizational_type
            || $preferences?->motivation_drivers_json);

        $assessment = $candidate->candidateAssessment;
        $hasAssessment = (bool) ($assessment && (
            $assessment->skill_score > 0
            || $assessment->temperament_type
            || ! empty($assessment->personality_scores_json)
        ));

        $personalityAnswered = $candidate->personalityProfile()
            ->where('assessment_completed', true)
            ->exists();

        return [
            [
                'key' => 'basic_profile',
                'label' => 'Basic profile information',
                'weight' => 18,
                'earned' => $basicProfile,
            ],
            [
                'key' => 'skills',
                'label' => 'Skills',
                'weight' => 16,
                'earned' => $hasSkills,
            ],
            [
                'key' => 'experience',
                'label' => 'Work experience',
                'weight' => 16,
                'earned' => $hasExperience,
            ],
            [
                'key' => 'education',
                'label' => 'Education',
                'weight' => 11,
                'earned' => $hasEducation,
            ],
            [
                'key' => 'preferences',
                'label' => 'Job preferences',
                'weight' => 8,
                'earned' => $hasPreferences,
            ],
            [
                'key' => 'assessment',
                'label' => 'Skills assessment',
                'weight' => 21,
                'earned' => $hasAssessment,
            ],
            [
                'key' => 'personality_answers',
                'label' => 'Personality assessment',
                'weight' => 10,
                'earned' => $personalityAnswered,
            ],
        ];
    }

    public function percentage(User $candidate): int
    {
        return (int) collect($this->items($candidate))
            ->filter(fn (array $item) => $item['earned'])
            ->sum('weight');
    }

    public function complete(User $candidate): bool
    {
        return $this->percentage($candidate) >= 100;
    }

    /**
     * The first required onboarding step a candidate still needs to finish,
     * mapping incomplete sections back to the 8-step onboarding flow. Returns
     * null when every required section is complete.
     */
    public function firstIncompleteStep(User $candidate): ?int
    {
        $earned = collect($this->items($candidate))
            ->pluck('earned', 'key');

        if (! $earned['basic_profile']) {
            return 1;
        }

        if (! $earned['skills'] || ! $earned['experience'] || ! $earned['education']) {
            return 2;
        }

        if (! $earned['assessment']) {
            return 3;
        }

        if (! $earned['personality_answers']) {
            return 4;
        }

        if (! $earned['preferences']) {
            return 6;
        }

        return null;
    }

    public function sync(User $candidate): int
    {
        $percentage = $this->percentage($candidate);

        $profile = $candidate->candidateProfile()->firstOrNew([]);
        $profile->profile_completion_percentage = $percentage;
        $profile->save();

        return $percentage;
    }

    /**
     * Human-readable slug for an item key (used for test/assertion helpers).
     */
    public function labelFor(string $key): string
    {
        return Str::slug(Str::headline($key));
    }
}

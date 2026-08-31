<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class ProfileCompletionService
{
    /**
     * Completion items that make up 100% of a candidate profile.
     *
     * Each entry has a weight, the earned flag and an optional helper label.
     * A candidate only reaches 100% once all sections are filled, including a
     * profile photo and an uploaded resume.
     *
     * @return array<int, array{key: string, label: string, weight: int, earned: bool}>
     */
    public function items(User $candidate): array
    {
        $profile = $candidate->candidateProfile;
        $media = $candidate->candidateMedia;

        $basicProfile = $profile
            && $profile->current_role
            && $profile->desired_role
            && $profile->years_of_experience
            && $profile->industry;

        $hasPhoto = (bool) $candidate->profile_photo_path;
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

        $hasResume = (bool) ($media?->cv_path);
        $hasOnlinePresence = (bool) ($media?->linkedin_url || $media?->github_url || $media?->role_video_url || ! empty($media?->portfolio_links_json));

        return [
            [
                'key' => 'basic_profile',
                'label' => 'Basic profile information',
                'weight' => 14,
                'earned' => $basicProfile,
            ],
            [
                'key' => 'profile_photo',
                'label' => 'Profile photo',
                'weight' => 8,
                'earned' => $hasPhoto,
            ],
            [
                'key' => 'skills',
                'label' => 'Skills',
                'weight' => 12,
                'earned' => $hasSkills,
            ],
            [
                'key' => 'experience',
                'label' => 'Work experience',
                'weight' => 12,
                'earned' => $hasExperience,
            ],
            [
                'key' => 'education',
                'label' => 'Education',
                'weight' => 8,
                'earned' => $hasEducation,
            ],
            [
                'key' => 'preferences',
                'label' => 'Job preferences',
                'weight' => 6,
                'earned' => $hasPreferences,
            ],
            [
                'key' => 'assessment',
                'label' => 'Skills assessment',
                'weight' => 16,
                'earned' => $hasAssessment,
            ],
            [
                'key' => 'personality_answers',
                'label' => 'Personality assessment',
                'weight' => 8,
                'earned' => $personalityAnswered,
            ],
            [
                'key' => 'resume',
                'label' => 'Resume',
                'weight' => 12,
                'earned' => $hasResume,
            ],
            [
                'key' => 'online_presence',
                'label' => 'Online presence',
                'weight' => 4,
                'earned' => $hasOnlinePresence,
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

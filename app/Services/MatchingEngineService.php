<?php

namespace App\Services;

use App\Models\Job;
use App\Models\JobMatchScore;
use App\Models\User;
use Illuminate\Support\Collection;

class MatchingEngineService
{
    public const WEIGHT_SKILLS = 0.40;

    public const WEIGHT_PERSONALITY = 0.25;

    public const WEIGHT_CULTURE = 0.20;

    public const WEIGHT_TEMPERAMENT = 0.15;

    public function calculateMatch(User $candidate, Job $job): array
    {
        $skillsFit = $this->calculateSkillsFit($candidate, $job);
        $personalityFit = $this->calculatePersonalityFit($candidate, $job);
        $cultureFit = $this->calculateCultureFit($candidate, $job);
        $temperamentFit = $this->calculateTemperamentFit($candidate, $job);

        $overall = (int) round(
            ($skillsFit * self::WEIGHT_SKILLS) +
            ($personalityFit * self::WEIGHT_PERSONALITY) +
            ($cultureFit * self::WEIGHT_CULTURE) +
            ($temperamentFit * self::WEIGHT_TEMPERAMENT)
        );

        return [
            'skills_fit_score' => $skillsFit,
            'personality_fit_score' => $personalityFit,
            'culture_fit_score' => $cultureFit,
            'temperament_fit_score' => $temperamentFit,
            'overall_match_score' => $overall,
        ];
    }

    public function saveMatch(User $candidate, Job $job): JobMatchScore
    {
        $scores = $this->calculateMatch($candidate, $job);

        return JobMatchScore::updateOrCreate(
            [
                'candidate_id' => $candidate->id,
                'job_id' => $job->id,
            ],
            $scores
        );
    }

    public function getTopMatchingJobs(User $candidate, int $limit = 10): Collection
    {
        $jobs = Job::where('status', 'open')
            ->with(['company', 'jobSkills.skill'])
            ->get();

        $scored = $jobs->map(function ($job) use ($candidate) {
            $scores = $this->calculateMatch($candidate, $job);
            $scores['job'] = $job;

            return $scores;
        });

        return $scored->sortByDesc('overall_match_score')->take($limit);
    }

    public function getTopMatchingCandidates(Job $job, int $limit = 10): Collection
    {
        $candidates = User::where('role', 'candidate')
            ->where('onboarding_completed', true)
            ->whereHas('personalityProfile', fn ($q) => $q->where('assessment_completed', true))
            ->with(['candidateProfile', 'candidateSkills', 'personalityProfile'])
            ->get();

        $scored = $candidates->map(function ($candidate) use ($job) {
            $scores = $this->calculateMatch($candidate, $job);
            $scores['candidate'] = $candidate;

            return $scores;
        });

        return $scored->sortByDesc('overall_match_score')->take($limit);
    }

    public function recalculateForCandidate(User $candidate): void
    {
        $jobs = Job::where('status', 'open')->get();

        foreach ($jobs as $job) {
            $this->saveMatch($candidate, $job);
        }
    }

    public function recalculateForJob(Job $job): void
    {
        $candidates = User::where('role', 'candidate')
            ->where('onboarding_completed', true)
            ->whereHas('personalityProfile', fn ($q) => $q->where('assessment_completed', true))
            ->get();

        foreach ($candidates as $candidate) {
            $this->saveMatch($candidate, $job);
        }
    }

    private function calculateSkillsFit(User $candidate, Job $job): int
    {
        $candidateSkills = $candidate->candidateSkills()
            ->whereNotNull('skill_id')
            ->get()
            ->keyBy('skill_id');

        $jobSkills = $job->jobSkills()->with('skill')->get();

        if ($jobSkills->isEmpty()) {
            return 50;
        }

        $requiredSkills = $jobSkills->where('is_required', true);
        $optionalSkills = $jobSkills->where('is_required', false);

        $matchedRequired = 0;
        $totalRequired = $requiredSkills->count();
        $matchedOptional = 0;
        $totalOptional = $optionalSkills->count();

        foreach ($requiredSkills as $jobSkill) {
            if ($candidateSkills->has($jobSkill->skill_id)) {
                $candidateSkill = $candidateSkills->get($jobSkill->skill_id);
                $requiredProf = $jobSkill->min_proficiency ?? 1;
                if (($candidateSkill->proficiency_level ?? 0) >= $requiredProf) {
                    $matchedRequired++;
                }
            }
        }

        foreach ($optionalSkills as $jobSkill) {
            if ($candidateSkills->has($jobSkill->skill_id)) {
                $matchedOptional++;
            }
        }

        $requiredScore = $totalRequired > 0 ? ($matchedRequired / $totalRequired) * 100 : 100;
        $optionalScore = $totalOptional > 0 ? ($matchedOptional / $totalOptional) * 100 : 100;

        return (int) round(($requiredScore * 0.7) + ($optionalScore * 0.3));
    }

    private function calculatePersonalityFit(User $candidate, Job $job): int
    {
        $profile = $candidate->personalityProfile;

        if (! $profile || ! $profile->assessment_completed) {
            return 50;
        }

        $personalityPrefs = $job->personality_preferences_json ?? [];

        if (empty($personalityPrefs)) {
            return 50;
        }

        $score = 0;
        $count = 0;

        $mapping = [
            'work_style' => $profile->work_style,
            'communication_style' => $profile->communication_style,
            'collaboration_style' => $profile->collaboration_style,
            'leadership_style' => $profile->leadership_style,
        ];

        foreach ($personalityPrefs as $trait => $preferred) {
            $candidateValue = $mapping[$trait] ?? null;

            if (! $candidateValue) {
                continue;
            }

            $min = $preferred['min'] ?? 0;
            $max = $preferred['max'] ?? 100;

            $normalized = crc32($candidateValue) % 101;

            if ($normalized >= $min && $normalized <= $max) {
                $score += 100;
            } elseif ($normalized < $min) {
                $score += max(0, 100 - ($min - $normalized) * 2);
            } else {
                $score += max(0, 100 - ($normalized - $max) * 2);
            }

            $count++;
        }

        if ($count === 0) {
            return 50;
        }

        return (int) round($score / $count);
    }

    private function calculateCultureFit(User $candidate, Job $job): int
    {
        $profile = $candidate->personalityProfile;

        if (! $profile || ! $profile->assessment_completed) {
            return 50;
        }

        $employer = $job->employer;
        $cultureProfile = $employer?->employerCultureProfile;

        if (! $cultureProfile) {
            return 50;
        }

        $score = 0;
        $count = 0;

        if ($cultureProfile->work_environment && $profile->organizational_fit) {
            $envMatch = $this->matchOrganizationalFit($profile->organizational_fit, $cultureProfile->work_environment);
            $score += $envMatch;
            $count++;
        }

        if ($cultureProfile->communication_style && $profile->communication_style) {
            $commMatch = $cultureProfile->communication_style === $profile->communication_style ? 100 : 60;
            $score += $commMatch;
            $count++;
        }

        if ($cultureProfile->leadership_style && $profile->leadership_style) {
            $leadMatch = $this->matchLeadershipStyles($profile->leadership_style, $cultureProfile->leadership_style);
            $score += $leadMatch;
            $count++;
        }

        if ($cultureProfile->company_pace && $profile->work_style) {
            $paceMatch = $this->matchPace($profile->work_style, $cultureProfile->company_pace);
            $score += $paceMatch;
            $count++;
        }

        if ($count === 0) {
            return 50;
        }

        return (int) round($score / $count);
    }

    private function calculateTemperamentFit(User $candidate, Job $job): int
    {
        $profile = $candidate->personalityProfile;

        if (! $profile || ! $profile->assessment_completed) {
            return 50;
        }

        $preferredTemperament = $job->temperament_preference;

        if (empty($preferredTemperament)) {
            return 50;
        }

        $candidateTemp = strtolower($profile->temperament_type ?? '');
        $preferredTemp = strtolower($preferredTemperament);

        if ($candidateTemp === $preferredTemp) {
            return 100;
        }

        $compatibility = [
            'analytical' => ['analytical' => 100, 'energetic' => 60, 'calm' => 70, 'decisive' => 60],
            'energetic' => ['analytical' => 60, 'energetic' => 100, 'calm' => 50, 'decisive' => 70],
            'calm' => ['analytical' => 70, 'energetic' => 50, 'calm' => 100, 'decisive' => 60],
            'decisive' => ['analytical' => 60, 'energetic' => 70, 'calm' => 60, 'decisive' => 100],
        ];

        return $compatibility[$candidateTemp][$preferredTemp] ?? 50;
    }

    private function matchOrganizationalFit(string $candidateFit, string $workEnvironment): int
    {
        $mapping = [
            'Startup or Dynamic Environment' => ['startup', 'dynamic', 'fast-paced', 'agile'],
            'Structured Corporate Environment' => ['corporate', 'structured', 'formal', 'established'],
            'Mission-Driven Organization' => ['nonprofit', 'mission', 'purpose', 'social'],
            'Remote-First Organization' => ['remote', 'distributed', 'virtual', 'flexible'],
            'Adaptable to Various Environments' => ['any', 'various', 'adaptable', 'flexible'],
        ];

        $candidateKeywords = $mapping[$candidateFit] ?? [];
        $environmentLower = strtolower($workEnvironment);

        foreach ($candidateKeywords as $keyword) {
            if (str_contains($environmentLower, $keyword)) {
                return 100;
            }
        }

        return 50;
    }

    private function matchLeadershipStyles(string $candidateStyle, string $companyStyle): int
    {
        $compatibility = [
            'Takes Initiative and Leads' => ['leadership', 'management', 'initiative', 'directive'],
            'Emerging Leader' => ['growth', 'development', 'mentorship', 'supportive'],
            'Supportive Contributor' => ['collaborative', 'flat', 'team', 'supportive'],
            'Collaborative Participant' => ['collaborative', 'flat', 'democratic', 'participative'],
        ];

        $keywords = $compatibility[$candidateStyle] ?? [];
        $styleLower = strtolower($companyStyle);

        foreach ($keywords as $keyword) {
            if (str_contains($styleLower, $keyword)) {
                return 100;
            }
        }

        return 50;
    }

    private function matchPace(string $workStyle, string $companyPace): int
    {
        $fastStyles = ['Structured and Fast-Paced', 'Energetic and Fast-Paced', 'Flexible and Adaptive'];
        $steadyStyles = ['Structured and Methodical', 'Steady and Consistent', 'Balanced and Versatile'];

        $isFast = in_array($workStyle, $fastStyles);
        $companyIsFast = in_array(strtolower($companyPace), ['fast', 'fast-paced', 'rapid', 'dynamic']);

        if ($isFast && $companyIsFast) {
            return 100;
        }

        if (! $isFast && ! $companyIsFast) {
            return 100;
        }

        return 40;
    }
}

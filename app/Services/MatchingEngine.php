<?php

namespace App\Services;

use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Collection;

class MatchingEngine
{
    public const WEIGHT_SKILL = 0.40;

    public const WEIGHT_EXPERIENCE = 0.20;

    public const WEIGHT_LOCATION = 0.10;

    public const WEIGHT_SALARY = 0.10;

    public const WEIGHT_PERSONALITY = 0.10;

    public const WEIGHT_TEMPERAMENT = 0.10;

    public function calculateMatch(User $candidate, Job $job): array
    {
        $skillScore = $this->calculateSkillMatch($candidate, $job);
        $experienceScore = $this->calculateExperienceMatch($candidate, $job);
        $locationScore = $this->calculateLocationMatch($candidate, $job);
        $salaryScore = $this->calculateSalaryMatch($candidate, $job);
        $personalityScore = $this->calculatePersonalityMatch($candidate, $job);
        $temperamentScore = $this->calculateTemperamentMatch($candidate, $job);

        $totalScore = (int) round(
            ($skillScore * self::WEIGHT_SKILL) +
            ($experienceScore * self::WEIGHT_EXPERIENCE) +
            ($locationScore * self::WEIGHT_LOCATION) +
            ($salaryScore * self::WEIGHT_SALARY) +
            ($personalityScore * self::WEIGHT_PERSONALITY) +
            ($temperamentScore * self::WEIGHT_TEMPERAMENT)
        );

        return [
            'total_score' => $totalScore,
            'breakdown' => [
                'skills' => $skillScore,
                'experience' => $experienceScore,
                'location' => $locationScore,
                'salary' => $salaryScore,
                'personality' => $personalityScore,
                'temperament' => $temperamentScore,
            ],
        ];
    }

    public function calculateSkillMatch(User $candidate, Job $job): int
    {
        $candidateSkills = $candidate->candidateSkills()->get()->pluck('skill_name')->map(fn ($s) => strtolower($s))->toArray();
        $requiredSkills = array_map('strtolower', $job->getRequiredSkills());

        if (empty($requiredSkills)) {
            return 50;
        }

        $matchedSkills = array_intersect($candidateSkills, $requiredSkills);
        $matchCount = count($matchedSkills);
        $requiredCount = count($requiredSkills);

        $baseScore = ($matchCount / $requiredCount) * 100;

        $proficiencyBonus = 0;
        foreach ($matchedSkills as $skill) {
            $candidateSkill = $candidate->candidateSkills()->whereRaw('LOWER(skill_name) = ?', [$skill])->first();
            if ($candidateSkill) {
                $proficiencyBonus += ($candidateSkill->proficiency_level - 1) * 2;
            }
        }
        $proficiencyBonus = min($proficiencyBonus, 20);

        return min(100, (int) round($baseScore + $proficiencyBonus));
    }

    public function calculateExperienceMatch(User $candidate, Job $job): int
    {
        $candidateYears = $candidate->candidateProfile?->years_of_experience ?? 0;
        $requiredYears = $job->minimum_experience;

        if ($requiredYears === 0) {
            return 80;
        }

        if ($candidateYears >= $requiredYears) {
            $excess = $candidateYears - $requiredYears;
            $bonus = min($excess * 2, 20);

            return min(100, 80 + $bonus);
        }

        $ratio = $candidateYears / $requiredYears;

        return (int) round($ratio * 80);
    }

    public function calculateLocationMatch(User $candidate, Job $job): int
    {
        $candidateLocation = $candidate->candidateProfile?->location_country ?? $candidate->candidateProfile?->location ?? '';
        $jobLocation = $job->location_country ?? $job->location ?? '';
        $jobWorkPreference = $job->work_preference ?? 'onsite';
        $candidateWorkPreference = $candidate->candidateProfile?->work_preference ?? 'onsite';

        if (empty($candidateLocation) && empty($jobLocation)) {
            return 70;
        }

        if ($jobWorkPreference === 'remote') {
            return 100;
        }

        if ($jobWorkPreference === 'hybrid' && $candidateWorkPreference === 'remote') {
            return 90;
        }

        if (empty($candidateLocation) || empty($jobLocation)) {
            return 50;
        }

        $candidateLocationLower = strtolower($candidateLocation);
        $jobLocationLower = strtolower($jobLocation);

        if ($candidateLocationLower === $jobLocationLower) {
            return 100;
        }

        if (str_contains($candidateLocationLower, $jobLocationLower) || str_contains($jobLocationLower, $candidateLocationLower)) {
            return 80;
        }

        return 30;
    }

    public function calculateSalaryMatch(User $candidate, Job $job): int
    {
        $candidateSalary = $candidate->candidateProfile?->salary_expectation ?? 0;
        $jobSalaryMin = $job->salary_min ?? 0;
        $jobSalaryMax = $job->salary_max ?? 0;

        if ($candidateSalary === 0 || ($jobSalaryMin === 0 && $jobSalaryMax === 0)) {
            return 70;
        }

        if ($jobSalaryMax > 0 && $candidateSalary <= $jobSalaryMax) {
            if ($jobSalaryMin > 0 && $candidateSalary >= $jobSalaryMin) {
                return 100;
            }

            $range = $jobSalaryMax - $jobSalaryMin;
            $position = $candidateSalary - $jobSalaryMin;

            return (int) round(70 + ($position / $range) * 30);
        }

        if ($jobSalaryMax === 0 && $jobSalaryMin > 0) {
            if ($candidateSalary <= $jobSalaryMin) {
                return 100;
            }

            $excess = $candidateSalary - $jobSalaryMin;
            $threshold = $jobSalaryMin * 0.3;

            return $excess > $threshold ? 40 : 80;
        }

        return 50;
    }

    public function calculatePersonalityMatch(User $candidate, Job $job): int
    {
        $personalityPreferences = $job->personality_preferences_json ?? [];
        $candidatePersonality = $candidate->candidateAssessment?->personality_scores_json ?? [];

        if (empty($personalityPreferences) || empty($candidatePersonality)) {
            return 50;
        }

        $totalScore = 0;
        $count = 0;

        foreach ($personalityPreferences as $trait => $preferredRange) {
            if (! isset($candidatePersonality[$trait])) {
                continue;
            }

            $candidateValue = $candidatePersonality[$trait];
            $min = $preferredRange['min'] ?? 0;
            $max = $preferredRange['max'] ?? 100;

            if ($candidateValue >= $min && $candidateValue <= $max) {
                $totalScore += 100;
            } elseif ($candidateValue < $min) {
                $totalScore += max(0, 100 - ($min - $candidateValue) * 2);
            } else {
                $totalScore += max(0, 100 - ($candidateValue - $max) * 2);
            }
            $count++;
        }

        if ($count === 0) {
            return 50;
        }

        return (int) round($totalScore / $count);
    }

    public function calculateTemperamentMatch(User $candidate, Job $job): int
    {
        $preferredTemperament = $job->temperament_preference;
        $candidateTemperament = $candidate->candidateAssessment?->temperament_type;

        if (empty($preferredTemperament) || empty($candidateTemperament)) {
            return 50;
        }

        return strtolower($candidateTemperament) === strtolower($preferredTemperament) ? 100 : 30;
    }

    public function getTopMatchingJobs(User $candidate, int $limit = 10): Collection
    {
        $jobs = Job::where('status', 'open')
            ->with('employer')
            ->get();

        $jobsWithScores = $jobs->map(function ($job) use ($candidate) {
            $scores = $this->calculateMatch($candidate, $job);

            return [
                'job' => $job,
                'match_percentage' => $scores['total_score'],
                'skill_score' => $scores['breakdown']['skills'],
                'experience_score' => $scores['breakdown']['experience'],
                'location_score' => $scores['breakdown']['location'],
                'salary_score' => $scores['breakdown']['salary'],
                'personality_score' => $scores['breakdown']['personality'],
                'temperament_score' => $scores['breakdown']['temperament'],
            ];
        });

        return $jobsWithScores
            ->sortByDesc('match_percentage')
            ->take($limit);
    }

    public function getTopMatchingCandidates(Job $job, int $limit = 10): Collection
    {
        $candidates = User::where('role', 'candidate')
            ->where('onboarding_completed', true)
            ->with(['candidateProfile', 'candidateSkills', 'candidateAssessment'])
            ->get();

        $candidatesWithScores = $candidates->map(function ($candidate) use ($job) {
            $scores = $this->calculateMatch($candidate, $job);

            return [
                'candidate' => $candidate,
                'match_percentage' => $scores['total_score'],
                'skill_score' => $scores['breakdown']['skills'],
                'experience_score' => $scores['breakdown']['experience'],
                'location_score' => $scores['breakdown']['location'],
                'salary_score' => $scores['breakdown']['salary'],
                'personality_score' => $scores['breakdown']['personality'],
                'temperament_score' => $scores['breakdown']['temperament'],
            ];
        });

        return $candidatesWithScores
            ->sortByDesc('match_percentage')
            ->take($limit);
    }
}

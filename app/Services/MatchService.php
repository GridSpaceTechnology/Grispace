<?php

namespace App\Services;

use App\Models\Job;
use App\Models\JobApplication;
use App\Models\MatchProfile;
use App\Models\User;
use Illuminate\Support\Collection;

class MatchService
{
    // Weight configuration - totals must equal 100
    public const WEIGHTS = [
        'skills' => 35,
        'experience' => 20,
        'salary' => 15,
        'work_preference' => 10,
        'personality' => 10,
        'education' => 5,
        'availability' => 5,
    ];

    public const CRITICAL_SKILL_BONUS = 10;

    public const OPTIONAL_SKILL_BONUS = 5;

    public function calculateMatchScore(User $candidate, Job $job): int
    {
        $data = $this->calculateFullMatchData($candidate, $job);

        return $data['overall_score'];
    }

    public function calculateFullMatchData(User $candidate, Job $job): array
    {
        $skillData = $this->calculateSkillMatch($candidate, $job);
        $experienceData = $this->calculateExperienceMatch($candidate, $job);
        $salaryData = $this->calculateSalaryMatch($candidate, $job);
        $workData = $this->calculateWorkPreferenceMatch($candidate, $job);
        $personalityData = $this->calculatePersonalityMatch($candidate, $job);
        $educationData = $this->calculateEducationMatch($candidate, $job);
        $availabilityData = $this->calculateAvailabilityMatch($candidate, $job);

        $weightedScore = (
            ($skillData['score'] * self::WEIGHTS['skills'] / 100) +
            ($experienceData['score'] * self::WEIGHTS['experience'] / 100) +
            ($salaryData['score'] * self::WEIGHTS['salary'] / 100) +
            ($workData['score'] * self::WEIGHTS['work_preference'] / 100) +
            ($personalityData['score'] * self::WEIGHTS['personality'] / 100) +
            ($educationData['score'] * self::WEIGHTS['education'] / 100) +
            ($availabilityData['score'] * self::WEIGHTS['availability'] / 100)
        );

        return [
            'overall_score' => (int) round($weightedScore),
            'skill_score' => $skillData['score'],
            'experience_score' => $experienceData['score'],
            'salary_score' => $salaryData['score'],
            'work_preference_score' => $workData['score'],
            'personality_score' => $personalityData['score'],
            'education_score' => $educationData['score'],
            'availability_score' => $availabilityData['score'],
            'matched_skills' => $skillData['matched'],
            'missing_skills' => $skillData['missing'],
            'critical_missing' => $skillData['critical_missing'],
            'requirements_met' => $experienceData['requirements_met'],
            'requirements_missing' => $experienceData['requirements_missing'],
        ];
    }

    public function calculateSkillMatch(User $candidate, Job $job): array
    {
        $candidateSkills = $candidate->candidateSkills()
            ->whereNotNull('skill_id')
            ->get()
            ->keyBy('skill_id');

        $jobSkills = $job->jobSkills()->with('skill')->get();

        if ($jobSkills->isEmpty()) {
            return [
                'score' => 100,
                'matched' => [],
                'missing' => [],
                'critical_missing' => [],
            ];
        }

        $requiredSkills = $jobSkills->where('is_required', true);
        $optionalSkills = $jobSkills->where('is_required', false);

        $matchedRequired = 0;
        $totalRequired = $requiredSkills->count();
        $matchedOptional = 0;
        $totalOptional = $optionalSkills->count();
        $matched = [];
        $missing = [];
        $criticalMissing = [];

        foreach ($requiredSkills as $jobSkill) {
            if ($candidateSkills->has($jobSkill->skill_id)) {
                $candidateSkill = $candidateSkills->get($jobSkill->skill_id);
                if ($this->meetsProficiency($candidateSkill, $jobSkill)) {
                    $matchedRequired++;
                    $matched[] = [
                        'skill_id' => $jobSkill->skill_id,
                        'name' => $jobSkill->skill?->name,
                        'required' => true,
                    ];
                } else {
                    $criticalMissing[] = [
                        'skill_id' => $jobSkill->skill_id,
                        'name' => $jobSkill->skill?->name,
                        'required_proficiency' => $jobSkill->min_proficiency,
                        'candidate_proficiency' => $candidateSkill->proficiency_level,
                    ];
                }
            } else {
                $missing[] = [
                    'skill_id' => $jobSkill->skill_id,
                    'name' => $jobSkill->skill?->name,
                    'required' => true,
                ];
                $criticalMissing[] = [
                    'skill_id' => $jobSkill->skill_id,
                    'name' => $jobSkill->skill?->name,
                ];
            }
        }

        foreach ($optionalSkills as $jobSkill) {
            if ($candidateSkills->has($jobSkill->skill_id)) {
                $candidateSkill = $candidateSkills->get($jobSkill->skill_id);
                if ($this->meetsProficiency($candidateSkill, $jobSkill)) {
                    $matchedOptional++;
                    $matched[] = [
                        'skill_id' => $jobSkill->skill_id,
                        'name' => $jobSkill->skill?->name,
                        'required' => false,
                    ];
                }
            }
        }

        $requiredScore = $totalRequired > 0 ? ($matchedRequired / $totalRequired) * 100 : 100;
        $optionalScore = $totalOptional > 0 ? ($matchedOptional / $totalOptional) * 100 : 100;

        $baseScore = ($requiredScore * 0.7) + ($optionalScore * 0.3);

        if ($matchedOptional > 0) {
            $bonus = min($matchedOptional * self::OPTIONAL_SKILL_BONUS, 15);
            $baseScore = min(100, $baseScore + $bonus);
        }

        return [
            'score' => (int) round($baseScore),
            'matched' => $matched,
            'missing' => $missing,
            'critical_missing' => $criticalMissing,
        ];
    }

    private function meetsProficiency($candidateSkill, $jobSkill): bool
    {
        $required = $jobSkill->min_proficiency ?? 1;

        return ($candidateSkill->proficiency_level ?? 0) >= $required;
    }

    public function calculateExperienceMatch(User $candidate, Job $job): array
    {
        $profile = $candidate->candidateProfile;
        $requirements = $job->jobRequirements()
            ->where('requirement_type', 'experience')
            ->get();

        if (! $profile) {
            return [
                'score' => 0,
                'requirements_met' => [],
                'requirements_missing' => [],
            ];
        }

        $candidateYears = $profile->years_of_experience ?? 0;
        $requiredYears = $job->minimum_experience ?? 0;

        $requirementsMet = [];
        $requirementsMissing = [];

        foreach ($requirements as $req) {
            $met = match ($req->requirement_value) {
                'junior' => $candidateYears <= 3,
                'mid' => $candidateYears >= 2 && $candidateYears <= 5,
                'senior' => $candidateYears >= 5,
                'lead' => $candidateYears >= 7,
                default => $candidateYears >= (int) $req->requirement_value,
            };

            if ($met) {
                $requirementsMet[] = $req->requirement_value;
            } else {
                $requirementsMissing[] = $req->requirement_value;
            }
        }

        if ($requiredYears === 0 && $requirements->isEmpty()) {
            return [
                'score' => 100,
                'requirements_met' => $requirementsMet,
                'requirements_missing' => $requirementsMissing,
            ];
        }

        $baseScore = $candidateYears >= $requiredYears
            ? min(100, 80 + ($candidateYears - $requiredYears) * 5)
            : ($candidateYears / max($requiredYears, 1)) * 100;

        if (! empty($requirementsMet)) {
            $baseScore = min(100, $baseScore + 10);
        }

        return [
            'score' => (int) round($baseScore),
            'requirements_met' => $requirementsMet,
            'requirements_missing' => $requirementsMissing,
        ];
    }

    public function calculateSalaryMatch(User $candidate, Job $job): array
    {
        $profile = $candidate->candidateProfile;
        $expected = $profile?->salary_expectation;
        $min = $job->salary_min;
        $max = $job->salary_max;

        if (! $expected || (! $min && ! $max)) {
            return [
                'score' => 75,
                'within_range' => null,
            ];
        }

        $withinRange = $expected >= $min && ($max === null || $expected <= $max);

        if ($withinRange) {
            return [
                'score' => 100,
                'within_range' => true,
            ];
        }

        if ($expected < $min) {
            $score = 90;
        } else {
            $overlap = $max ? ($max - $expected) / $expected : -1;
            $score = max(0, (int) round(($overlap + 1) * 50));
        }

        return [
            'score' => $score,
            'within_range' => false,
        ];
    }

    public function calculateWorkPreferenceMatch(User $candidate, Job $job): array
    {
        $profile = $candidate->candidateProfile;
        $candidatePref = $profile?->work_preference;
        $jobPref = $job->work_preference;

        if (! $candidatePref || ! $jobPref) {
            return ['score' => 75, 'match' => null];
        }

        if ($candidatePref === $jobPref) {
            return ['score' => 100, 'match' => true];
        }

        $compatibility = [
            'remote' => ['remote' => 100, 'hybrid' => 80, 'flexible' => 80, 'onsite' => 30],
            'hybrid' => ['remote' => 80, 'hybrid' => 100, 'flexible' => 90, 'onsite' => 60],
            'onsite' => ['remote' => 30, 'hybrid' => 60, 'flexible' => 60, 'onsite' => 100],
            'flexible' => ['remote' => 90, 'hybrid' => 90, 'flexible' => 100, 'onsite' => 70],
        ];

        $score = $compatibility[$candidatePref][$jobPref] ?? 50;

        return ['score' => $score, 'match' => $score >= 70];
    }

    public function calculatePersonalityMatch(User $candidate, Job $job): array
    {
        $assessment = $candidate->candidateAssessment;
        $jobPref = $job->temperament_preference;

        if (! $assessment || ! $jobPref) {
            return ['score' => 75, 'match' => null];
        }

        $candidateType = $assessment->temperament_type;

        if ($candidateType === $jobPref) {
            return ['score' => 100, 'match' => true];
        }

        $compatibility = [
            'analytical' => ['analytical' => 100, 'driver' => 70, 'expressive' => 50, 'amiable' => 50],
            'expressive' => ['analytical' => 50, 'driver' => 50, 'expressive' => 100, 'amiable' => 80],
            'amiable' => ['analytical' => 50, 'driver' => 50, 'expressive' => 80, 'amiable' => 100],
            'driver' => ['analytical' => 70, 'driver' => 100, 'expressive' => 50, 'amiable' => 50],
        ];

        $score = $compatibility[$candidateType][$jobPref] ?? 50;

        return ['score' => $score, 'match' => $score >= 70];
    }

    public function calculateEducationMatch(User $candidate, Job $job): int
    {
        $education = $candidate->candidateEducation;
        $requirements = $job->jobRequirements()
            ->where('requirement_type', 'education')
            ->get();

        if ($requirements->isEmpty() || $education->isEmpty()) {
            return 75;
        }

        $candidateDegrees = $education->pluck('qualification')->toArray();
        $met = false;

        foreach ($requirements as $req) {
            if (in_array($req->requirement_value, $candidateDegrees)) {
                $met = true;
                break;
            }
        }

        return $met ? 100 : 50;
    }

    public function calculateAvailabilityMatch(User $candidate, Job $job): array
    {
        $profile = $candidate->candidateProfile;
        $candidateAvail = $profile?->availability;
        $jobRequirements = $job->jobRequirements()
            ->where('requirement_type', 'availability')
            ->get();

        if (! $candidateAvail || $jobRequirements->isEmpty()) {
            return ['score' => 75, 'match' => null];
        }

        $availabilityOrder = [
            'immediately' => 1,
            '2_weeks' => 2,
            '1_month' => 3,
            '2_months' => 4,
            '3_months' => 5,
            'passive' => 6,
        ];

        $candidateScore = $availabilityOrder[$candidateAvail] ?? 6;

        foreach ($jobRequirements as $req) {
            $requiredScore = $availabilityOrder[$req->requirement_value] ?? 6;
            if ($candidateScore <= $requiredScore) {
                return ['score' => 100, 'match' => true];
            }
        }

        return ['score' => 40, 'match' => false];
    }

    public function saveMatchProfile(JobApplication $application): MatchProfile
    {
        $candidate = $application->candidate;
        $job = $application->job;

        $matchData = $this->calculateFullMatchData($candidate, $job);

        $application->matchProfile()->update(['is_latest' => false]);

        return MatchProfile::create([
            'application_id' => $application->id,
            'overall_score' => $matchData['overall_score'],
            'skill_score' => $matchData['skill_score'],
            'experience_score' => $matchData['experience_score'],
            'salary_score' => $matchData['salary_score'],
            'work_preference_score' => $matchData['work_preference_score'],
            'personality_score' => $matchData['personality_score'],
            'education_score' => $matchData['education_score'],
            'availability_score' => $matchData['availability_score'],
            'matched_skills' => $matchData['matched_skills'],
            'missing_skills' => $matchData['missing_skills'],
            'matched_requirements' => $matchData['requirements_met'] ?? [],
            'missing_requirements' => $matchData['requirements_missing'] ?? [],
            'scored_at' => now(),
            'is_latest' => true,
        ]);
    }

    public function recalculateMatchProfile(JobApplication $application): MatchProfile
    {
        return $this->saveMatchProfile($application);
    }

    public function getTopMatchingJobs(User $candidate, int $limit = 10): Collection
    {
        return Job::query()
            ->where('status', 'open')
            ->where('employer_id', '!=', $candidate->id)
            ->with(['company', 'jobSkills.skill'])
            ->get()
            ->map(fn ($job) => [
                'job' => $job,
                'match_score' => $this->calculateMatchScore($candidate, $job),
            ])
            ->sortByDesc('match_score')
            ->take($limit);
    }

    public function getTopMatchingCandidates(Job $job, int $limit = 10): Collection
    {
        return User::query()
            ->where('role', 'candidate')
            ->where('onboarding_completed', true)
            ->with([
                'candidateProfile',
                'candidateSkills.skill',
                'candidateAssessment',
                'candidatePreferences',
            ])
            ->get()
            ->map(fn ($candidate) => [
                'candidate' => $candidate,
                'match_score' => $this->calculateMatchScore($candidate, $job),
            ])
            ->sortByDesc('match_score')
            ->take($limit);
    }
}

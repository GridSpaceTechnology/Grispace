<?php

namespace App\Services;

use App\Enums\OnboardingStep;
use App\Models\SignalCategory;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnboardingService
{
    public function getStepProgress(User $candidate): array
    {
        $completion = $this->calculateCompletion($candidate);

        return [
            'current_step' => $this->getCurrentStep($candidate),
            'completed_steps' => $this->getCompletedSteps($candidate),
            'completion_percentage' => $completion,
            'can_apply' => $completion >= 100,
        ];
    }

    public function getCurrentStep(User $candidate): OnboardingStep
    {
        if (! $candidate->current_role) {
            return OnboardingStep::Profile;
        }

        if (! $candidate->candidateSkills()->exists()) {
            return OnboardingStep::Skills;
        }

        if (! $candidate->candidateExperiences()->exists()) {
            return OnboardingStep::Experience;
        }

        if (! $candidate->candidateEducation()->exists()) {
            return OnboardingStep::Education;
        }

        if (! $candidate->candidatePreferences) {
            return OnboardingStep::Preferences;
        }

        if (! $candidate->candidateAssessment) {
            return OnboardingStep::Assessment;
        }

        return OnboardingStep::Review;
    }

    public function getCompletedSteps(User $candidate): array
    {
        $steps = [];

        if ($candidate->current_role) {
            $steps[] = OnboardingStep::Profile->value;
        }

        if ($candidate->candidateSkills()->exists()) {
            $steps[] = OnboardingStep::Skills->value;
        }

        if ($candidate->candidateExperiences()->exists()) {
            $steps[] = OnboardingStep::Experience->value;
        }

        if ($candidate->candidateEducation()->exists()) {
            $steps[] = OnboardingStep::Education->value;
        }

        if ($candidate->candidatePreferences) {
            $steps[] = OnboardingStep::Preferences->value;
        }

        if ($candidate->candidateAssessment) {
            $steps[] = OnboardingStep::Assessment->value;
        }

        return $steps;
    }

    public function calculateCompletion(User $candidate): int
    {
        $completed = count($this->getCompletedSteps($candidate));

        return (int) floor(($completed / 7) * 100);
    }

    public function saveProfileStep(User $candidate, array $data): User
    {
        $candidate->update([
            'current_role' => $data['current_role'],
            'years_of_experience' => $data['years_of_experience'],
            'industry' => $data['industry'],
            'greatest_achievement' => $data['greatest_achievement'] ?? null,
        ]);

        if ($data['greatest_achievement'] ?? false) {
            $this->addSignal($candidate, 'achievement', $data['greatest_achievement'], [
                'type' => 'greatest_achievement',
            ]);
        }

        return $candidate->fresh();
    }

    public function saveSkillsStep(User $candidate, array $data): User
    {
        DB::transaction(function () use ($candidate, $data) {
            $candidate->candidateSkills()->delete();

            $technicalCategory = SignalCategory::where('slug', 'technical_skills')->first();
            $softCategory = SignalCategory::where('slug', 'soft_skills')->first();
            $toolsCategory = SignalCategory::where('slug', 'tools_platforms')->first();

            foreach ($data['skills'] ?? [] as $skill) {
                $normalizedSkill = $this->findOrCreateSkill($skill['name']);
                $proficiencyMap = [
                    'beginner' => 1,
                    'elementary' => 2,
                    'intermediate' => 3,
                    'upper_intermediate' => 4,
                    'advanced' => 5,
                    'expert' => 6,
                ];

                $candidate->candidateSkills()->create([
                    'skill_id' => $normalizedSkill->id,
                    'skill_name' => $skill['name'],
                    'proficiency_level' => $proficiencyMap[$skill['proficiency']] ?? 3,
                    'years_experience' => $skill['years_experience'] ?? null,
                ]);

                $this->addSignal($candidate, 'technical_skill', $normalizedSkill->name, [
                    'proficiency' => $skill['proficiency'],
                    'years_experience' => $skill['years_experience'] ?? null,
                    'category_id' => $technicalCategory?->id,
                ]);
            }

            foreach ($data['soft_skills'] ?? [] as $skill) {
                $this->addSignal($candidate, 'soft_skill', $skill, [
                    'category_id' => $softCategory?->id,
                ]);
            }

            foreach ($data['tools'] ?? [] as $tool) {
                $normalizedTool = $this->findOrCreateSkill($tool['name'], 'tool');

                $this->addSignal($candidate, 'tool_proficiency', $normalizedTool->name, [
                    'proficiency' => $tool['proficiency'] ?? 'intermediate',
                    'category_id' => $toolsCategory?->id,
                ]);
            }
        });

        return $candidate->fresh();
    }

    public function saveExperienceStep(User $candidate, array $data): User
    {
        DB::transaction(function () use ($candidate, $data) {
            $candidate->candidateExperiences()->delete();

            $industryCategory = SignalCategory::where('slug', 'industry_experience')->first();

            foreach ($data['experiences'] ?? [] as $exp) {
                $experience = $candidate->candidateExperiences()->create([
                    'company' => $exp['company'],
                    'role' => $exp['role'],
                    'start_date' => $exp['start_date'],
                    'end_date' => $exp['is_current'] ? null : ($exp['end_date'] ?? null),
                    'is_current' => $exp['is_current'] ?? false,
                    'description' => $exp['description'] ?? null,
                ]);

                $this->addSignal($candidate, 'industry_experience', $candidate->industry, [
                    'company' => $exp['company'],
                    'role' => $exp['role'],
                    'years' => $this->calculateYears($exp['start_date'], $exp['end_date'] ?? null),
                    'category_id' => $industryCategory?->id,
                ]);

                foreach ($exp['skills_used'] ?? [] as $skill) {
                    $this->addSignal($candidate, 'technical_skill', $skill, [
                        'context' => 'work_experience',
                        'experience_id' => $experience->id,
                    ]);
                }
            }
        });

        return $candidate->fresh();
    }

    public function saveEducationStep(User $candidate, array $data): User
    {
        DB::transaction(function () use ($candidate, $data) {
            $candidate->candidateEducation()->delete();

            $certCategory = SignalCategory::where('slug', 'certifications')->first();

            foreach ($data['education'] ?? [] as $edu) {
                $candidate->candidateEducation()->create([
                    'institution' => $edu['institution'],
                    'qualification' => $edu['qualification'],
                    'field_of_study' => $edu['field_of_study'] ?? null,
                    'start_date' => $edu['start_date'],
                    'end_date' => $edu['is_current'] ? null : ($edu['end_date'] ?? null),
                    'is_current' => $edu['is_current'] ?? false,
                    'grade' => $edu['grade'] ?? null,
                ]);
            }

            foreach ($data['certifications'] ?? [] as $cert) {
                $this->addSignal($candidate, 'certification', $cert['name'], [
                    'issuer' => $cert['issuer'] ?? null,
                    'date_obtained' => $cert['date_obtained'] ?? null,
                    'credential_id' => $cert['credential_id'] ?? null,
                    'category_id' => $certCategory?->id,
                ]);
            }
        });

        return $candidate->fresh();
    }

    public function savePreferencesStep(User $candidate, array $data): User
    {
        $candidate->update([
            'desired_role' => $data['desired_role'],
            'employment_type_preference' => $data['employment_type_preference'][0] ?? 'full_time',
            'work_preference' => $data['work_preference'],
            'location' => $data['location'] ?? null,
            'location_country' => $data['location_country'] ?? null,
            'salary_expectation_min' => $data['salary_expectation_min'] ?? null,
            'salary_expectation_max' => $data['salary_expectation_max'] ?? null,
            'availability' => $data['availability'],
            'experience_level' => $data['experience_level'],
        ]);

        $candidate->candidatePreferences()->delete();
        $candidate->candidatePreferences()->create([
            'organizational_type' => $data['organizational_type'] ?? null,
            'motivation_drivers_json' => $data['motivation_drivers'] ?? [],
        ]);

        $workStyleCategory = SignalCategory::where('slug', 'work_style')->first();
        $valuesCategory = SignalCategory::where('slug', 'values_motivations')->first();

        $this->addSignal($candidate, 'work_style', $data['work_preference'], [
            'category_id' => $workStyleCategory?->id,
        ]);

        foreach ($data['motivation_drivers'] ?? [] as $driver) {
            $this->addSignal($candidate, 'value', $driver, [
                'category_id' => $valuesCategory?->id,
            ]);
        }

        foreach ($data['role_interests'] ?? [] as $role) {
            $this->addSignal($candidate, 'industry_experience', $role, [
                'type' => 'role_interest',
            ]);
        }

        return $candidate->fresh();
    }

    public function completeOnboarding(User $candidate): User
    {
        $candidate->update([
            'onboarding_completed' => true,
            'onboarding_completed_at' => now(),
            'profile_completion_percentage' => 100,
        ]);

        return $candidate->fresh();
    }

    private function addSignal(User $candidate, string $type, string $value, array $metadata = []): CandidateSignal
    {
        $categoryId = $metadata['category_id'] ?? null;
        unset($metadata['category_id']);

        return $candidate->candidateSignals()->create([
            'signal_type' => $type,
            'value' => $value,
            'metadata' => $metadata,
            'category_id' => $categoryId,
        ]);
    }

    private function findOrCreateSkill(string $name, string $type = 'technical'): Skill
    {
        $slug = Str::slug($name);

        return Skill::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => ucwords($name),
                'category' => $type === 'tool' ? 'Tools & Platforms' : 'Programming Languages',
                'type' => $type,
                'is_active' => true,
            ]
        );
    }

    private function calculateYears(?string $startDate, ?string $endDate): int
    {
        $start = new \DateTime($startDate);
        $end = $endDate ? new \DateTime($endDate) : new \DateTime;

        return (int) floor($start->diff($end)->y);
    }
}

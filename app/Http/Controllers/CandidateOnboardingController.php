<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CandidateOnboardingController extends Controller
{
    public const TOTAL_STEPS = 8;

    public function show(Request $request)
    {
        $user = Auth::user();

        if ($user->onboarding_completed) {
            return redirect()->route('candidate.dashboard');
        }

        $step = $request->route('step') ?? 1;
        $step = max(1, min(self::TOTAL_STEPS, (int) $step));

        return view("onboarding.steps.{$step}", [
            'step' => $step,
            'totalSteps' => self::TOTAL_STEPS,
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $step = (int) $request->route('step');

        $this->validateStep($request, $step);

        $this->saveStep($request, $user, $step);

        $this->updateProfileCompletion($user);

        if ($step < self::TOTAL_STEPS) {
            return redirect()->route('candidate.onboarding.step', ['step' => $step + 1])
                ->with('success', 'Progress saved!');
        }

        return $this->completeOnboarding($user);
    }

    protected function validateStep(Request $request, int $step): void
    {
        $rules = match ($step) {
            1 => [
                'current_role' => 'nullable|string|max:255',
                'desired_role' => 'nullable|string|max:255',
                'years_of_experience' => 'nullable|integer|min:0|max:50',
                'industry' => 'nullable|string|max:255',
                'employment_type_preference' => ['nullable', Rule::in(['full-time', 'part-time', 'contract', 'freelance'])],
                'work_preference' => ['nullable', Rule::in(['remote', 'hybrid', 'onsite'])],
            ],
            2 => [
                'skills' => 'nullable|array',
                'skills.*.name' => 'nullable|string|max:255',
                'skills.*.level' => 'nullable|integer|min:1|max:5',
                'experiences' => 'nullable|array',
                'experiences.*.company' => 'nullable|string|max:255',
                'experiences.*.role' => 'nullable|string|max:255',
                'experiences.*.duration' => 'nullable|string|max:100',
                'experiences.*.description' => 'nullable|string',
                'education' => 'nullable|array',
                'education.*.institution' => 'nullable|string|max:255',
                'education.*.qualification' => 'nullable|string|max:255',
                'education.*.year_completed' => 'nullable|integer|min:1950|max:2030',
            ],
            3 => [
                'skill_score' => 'nullable|integer|min:0|max:100',
                'subskill_breakdown' => 'nullable|array',
            ],
            4 => [
                'personality_scores' => 'nullable|array',
            ],
            5 => [
                'temperament_type' => ['nullable', Rule::in(['analytical', 'driver', 'expressive', 'amiable'])],
            ],
            6 => [
                'organizational_type' => 'nullable|string|max:255',
                'motivation_drivers' => 'nullable|array',
            ],
            7 => [
                'greatest_achievement' => 'nullable|string',
            ],
            8 => [
                'role_video_url' => 'required|string|max:500',
                'cv_path' => 'required|string|max:500',
                'linkedin_url' => 'nullable|string|max:500',
                'github_url' => 'nullable|string|max:500',
                'portfolio_links' => 'nullable|array',
            ],
            default => [],
        };

        $request->validate($rules);
    }

    protected function saveStep(Request $request, $user, int $step): void
    {
        match ($step) {
            1 => $this->saveStep1($user, $request),
            2 => $this->saveStep2($user, $request),
            3 => $this->saveStep3($user, $request),
            4 => $this->saveStep4($user, $request),
            5 => $this->saveStep5($user, $request),
            6 => $this->saveStep6($user, $request),
            7 => $this->saveStep7($user, $request),
            8 => $this->saveStep8($user, $request),
            default => null,
        };
    }

    protected function saveStep1($user, Request $request): void
    {
        $profile = $user->candidateProfile()->firstOrNew([]);
        $profile->fill($request->only([
            'current_role',
            'desired_role',
            'years_of_experience',
            'industry',
            'employment_type_preference',
            'work_preference',
        ]));
        $profile->save();
    }

    protected function saveStep2($user, Request $request): void
    {
        $user->candidateSkills()->delete();
        if ($request->has('skills')) {
            foreach ($request->input('skills') as $skill) {
                if (! empty($skill['name'])) {
                    $user->candidateSkills()->create([
                        'skill_name' => $skill['name'],
                        'proficiency_level' => $skill['level'] ?? 1,
                    ]);
                }
            }
        }

        $user->candidateExperiences()->delete();
        if ($request->has('experiences')) {
            foreach ($request->input('experiences') as $exp) {
                if (! empty($exp['company'])) {
                    $user->candidateExperiences()->create([
                        'company' => $exp['company'],
                        'role' => $exp['role'] ?? '',
                        'duration' => $exp['duration'] ?? '',
                        'description' => $exp['description'] ?? '',
                    ]);
                }
            }
        }

        $user->candidateEducation()->delete();
        if ($request->has('education')) {
            foreach ($request->input('education') as $edu) {
                if (! empty($edu['institution'])) {
                    $user->candidateEducation()->create([
                        'institution' => $edu['institution'],
                        'qualification' => $edu['qualification'] ?? '',
                        'year_completed' => $edu['year_completed'] ?? date('Y'),
                    ]);
                }
            }
        }
    }

    protected function saveStep3($user, Request $request): void
    {
        $assessment = $user->candidateAssessment()->firstOrNew([]);
        $assessment->fill([
            'skill_score' => $request->input('skill_score', 0),
            'subskill_breakdown_json' => $request->input('subskill_breakdown', []),
        ]);
        $assessment->save();
    }

    protected function saveStep4($user, Request $request): void
    {
        $assessment = $user->candidateAssessment()->firstOrNew([]);
        $assessment->fill([
            'personality_scores_json' => $request->input('personality_scores', []),
        ]);
        $assessment->save();
    }

    protected function saveStep5($user, Request $request): void
    {
        $assessment = $user->candidateAssessment()->firstOrNew([]);
        $assessment->fill([
            'temperament_type' => $request->input('temperament_type'),
        ]);
        $assessment->save();
    }

    protected function saveStep6($user, Request $request): void
    {
        $preferences = $user->candidatePreferences()->firstOrNew([]);
        $preferences->fill([
            'organizational_type' => $request->input('organizational_type'),
            'motivation_drivers_json' => $request->input('motivation_drivers', []),
        ]);
        $preferences->save();
    }

    protected function saveStep7($user, Request $request): void
    {
        $profile = $user->candidateProfile()->firstOrNew([]);
        $profile->fill([
            'greatest_achievement' => $request->input('greatest_achievement'),
        ]);
        $profile->save();
    }

    protected function saveStep8($user, Request $request): void
    {
        $media = $user->candidateMedia()->firstOrNew([]);
        $media->fill([
            'role_video_url' => $request->input('role_video_url'),
            'cv_path' => $request->input('cv_path'),
            'linkedin_url' => $request->input('linkedin_url'),
            'github_url' => $request->input('github_url'),
            'portfolio_links_json' => $request->input('portfolio_links', []),
        ]);
        $media->save();
    }

    protected function updateProfileCompletion($user): void
    {
        $percentage = 0;

        if ($user->candidateProfile) {
            $percentage += 25;
            if ($user->candidateProfile->desired_role) {
                $percentage += 5;
            }
            if ($user->candidateProfile->years_of_experience > 0) {
                $percentage += 5;
            }
            if ($user->candidateProfile->industry) {
                $percentage += 5;
            }
        }

        if ($user->candidateSkills()->count() > 0) {
            $percentage += 10;
        }
        if ($user->candidateExperiences()->count() > 0) {
            $percentage += 10;
        }
        if ($user->candidateEducation()->count() > 0) {
            $percentage += 5;
        }

        if ($user->candidateAssessment) {
            $percentage += 10;
            if ($user->candidateAssessment->skill_score > 0) {
                $percentage += 5;
            }
            if ($user->candidateAssessment->personality_scores_json) {
                $percentage += 5;
            }
            if ($user->candidateAssessment->temperament_type) {
                $percentage += 5;
            }
        }

        if ($user->candidatePreferences) {
            $percentage += 5;
        }

        if ($user->candidateMedia) {
            $percentage += 5;
            if ($user->candidateMedia->role_video_url) {
                $percentage += 5;
            }
            if ($user->candidateMedia->cv_path) {
                $percentage += 5;
            }
        }

        $profile = $user->candidateProfile()->firstOrNew([]);
        $profile->profile_completion_percentage = min(100, $percentage);
        $profile->save();
    }

    protected function canCompleteOnboarding($user): bool
    {
        if (! $user->candidateProfile || ! $user->candidateProfile->desired_role) {
            return false;
        }

        if (! $user->candidateMedia || ! $user->candidateMedia->role_video_url) {
            return false;
        }

        if (! $user->candidateAssessment) {
            return false;
        }

        return true;
    }

    protected function completeOnboarding($user): \Illuminate\Http\RedirectResponse
    {
        if (! $this->canCompleteOnboarding($user)) {
            return redirect()->route('candidate.onboarding.step', ['step' => 1])
                ->with('error', 'Please complete all required sections before finishing onboarding.');
        }

        $user->onboarding_completed = true;
        $user->save();

        return redirect()->route('candidate.dashboard')
            ->with('success', 'Onboarding completed! Welcome to Gridspace.');
    }

    public function skip(Request $request)
    {
        $user = Auth::user();

        if (! $user->candidateProfile) {
            $user->candidateProfile()->create([]);
        }
        if (! $user->candidateMedia) {
            $user->candidateMedia()->create([
                'role_video_url' => '',
                'cv_path' => '',
            ]);
        }
        if (! $user->candidateAssessment) {
            $user->candidateAssessment()->create([]);
        }
        if (! $user->candidatePreferences) {
            $user->candidatePreferences()->create([]);
        }

        $profile = $user->candidateProfile;
        $profile->profile_completion_percentage = 10;
        $profile->save();

        $user->onboarding_completed = true;
        $user->save();

        return redirect()->route('candidate.dashboard');
    }
}

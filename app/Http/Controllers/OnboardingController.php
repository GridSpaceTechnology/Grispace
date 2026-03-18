<?php

namespace App\Http\Controllers;

use App\Enums\OnboardingStep;
use App\Http\Requests\Onboarding\EducationStepRequest;
use App\Http\Requests\Onboarding\ExperienceStepRequest;
use App\Http\Requests\Onboarding\PreferencesStepRequest;
use App\Http\Requests\Onboarding\ProfileStepRequest;
use App\Http\Requests\Onboarding\SkillsStepRequest;
use App\Services\OnboardingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function __construct(
        private OnboardingService $onboardingService,
    ) {}

    public function status(Request $request): JsonResponse
    {
        $candidate = $request->user();

        $progress = $this->onboardingService->getStepProgress($candidate);

        return response()->json($progress);
    }

    public function showStep(Request $request, string $step): JsonResponse
    {
        $candidate = $request->user();
        $stepEnum = OnboardingStep::tryFrom($step);

        if (! $stepEnum) {
            return response()->json(['error' => 'Invalid step'], 400);
        }

        $currentStep = $this->onboardingService->getCurrentStep($candidate);

        if ($stepEnum->order() > $currentStep->order() + 1) {
            return response()->json(['error' => 'Cannot skip ahead'], 403);
        }

        return response()->json([
            'step' => $stepEnum->value,
            'previous_step' => $stepEnum->previous()?->value,
            'next_step' => $stepEnum->next()?->value,
            'can_skip' => in_array($stepEnum->value, ['education', 'assessment']),
            'required_fields' => $this->getRequiredFields($stepEnum),
        ]);
    }

    public function saveProfile(ProfileStepRequest $request): JsonResponse
    {
        $candidate = $request->user();

        $this->onboardingService->saveProfileStep($candidate, $request->validated());

        return response()->json([
            'message' => 'Profile saved successfully',
            'next_step' => OnboardingStep::Skills->value,
        ]);
    }

    public function saveSkills(SkillsStepRequest $request): JsonResponse
    {
        $candidate = $request->user();

        $this->onboardingService->saveSkillsStep($candidate, $request->validated());

        return response()->json([
            'message' => 'Skills saved successfully',
            'next_step' => OnboardingStep::Experience->value,
        ]);
    }

    public function saveExperience(ExperienceStepRequest $request): JsonResponse
    {
        $candidate = $request->user();

        $this->onboardingService->saveExperienceStep($candidate, $request->validated());

        return response()->json([
            'message' => 'Experience saved successfully',
            'next_step' => OnboardingStep::Education->value,
        ]);
    }

    public function saveEducation(EducationStepRequest $request): JsonResponse
    {
        $candidate = $request->user();

        $this->onboardingService->saveEducationStep($candidate, $request->validated());

        return response()->json([
            'message' => 'Education saved successfully',
            'next_step' => OnboardingStep::Preferences->value,
        ]);
    }

    public function savePreferences(PreferencesStepRequest $request): JsonResponse
    {
        $candidate = $request->user();

        $this->onboardingService->savePreferencesStep($candidate, $request->validated());

        return response()->json([
            'message' => 'Preferences saved successfully',
            'next_step' => OnboardingStep::Assessment->value,
        ]);
    }

    public function complete(Request $request): JsonResponse
    {
        $candidate = $request->user();

        $this->onboardingService->completeOnboarding($candidate);

        return response()->json([
            'message' => 'Onboarding completed successfully',
            'can_apply' => true,
        ]);
    }

    private function getRequiredFields(OnboardingStep $step): array
    {
        return match ($step) {
            OnboardingStep::Profile => ['current_role', 'years_of_experience', 'industry'],
            OnboardingStep::Skills => ['skills'],
            OnboardingStep::Experience => ['experiences'],
            OnboardingStep::Education => [],
            OnboardingStep::Preferences => ['desired_role', 'work_preference', 'availability', 'experience_level'],
            OnboardingStep::Assessment => [],
            OnboardingStep::Review => [],
        };
    }
}

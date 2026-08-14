<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\EmployerCultureProfile;
use App\Models\PersonalityQuestion;
use App\Services\PersonalityAssessmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EmployerOnboardingCultureController extends Controller
{
    public function __construct(
        protected PersonalityAssessmentService $assessmentService
    ) {}

    public function start(): RedirectResponse
    {
        $user = Auth::user();

        if ($user->onboarding_completed) {
            return redirect()->route('employer.dashboard');
        }

        $firstQuestion = $this->assessmentService->getFirstQuestionForSections([PersonalityQuestion::EMPLOYER_CATEGORY]);

        if (! $firstQuestion) {
            $user->update(['onboarding_completed' => true]);

            return redirect()->route('employer.dashboard')
                ->with('info', 'Onboarding complete.');
        }

        return redirect()->route('employer.onboarding.culture.question', $firstQuestion);
    }

    public function showQuestion(Request $request, PersonalityQuestion $question): View|RedirectResponse
    {
        $user = Auth::user();

        if ($question->category !== PersonalityQuestion::EMPLOYER_CATEGORY || ! $question->is_active) {
            return redirect()->route('employer.onboarding.culture');
        }

        $question->load('options');

        $categories = [PersonalityQuestion::EMPLOYER_CATEGORY];
        $totalQuestions = $this->assessmentService->getTotalQuestionsForSections($categories);
        $currentQuestionNumber = $this->assessmentService->getQuestionNumberForSections($categories, $question);
        $previousQuestion = $this->assessmentService->getPreviousQuestionForSections($categories, $question);
        $existingAnswer = $user->personalityAnswers()
            ->where('question_id', $question->id)
            ->first();

        return view('employer.onboarding-culture', compact(
            'question',
            'totalQuestions',
            'currentQuestionNumber',
            'previousQuestion',
            'existingAnswer',
        ));
    }

    public function answer(Request $request, PersonalityQuestion $question): RedirectResponse
    {
        $user = Auth::user();

        if ($question->category !== PersonalityQuestion::EMPLOYER_CATEGORY) {
            abort(404);
        }

        $validated = $request->validate([
            'option_id' => 'required|exists:personality_question_options,id',
        ]);

        $option = $question->options()->findOrFail($validated['option_id']);

        $this->assessmentService->saveAnswer($user, $question, $option->id);

        $nextQuestion = $this->assessmentService->getNextQuestionForSections([PersonalityQuestion::EMPLOYER_CATEGORY], $question);

        if ($nextQuestion) {
            return redirect()->route('employer.onboarding.culture.question', $nextQuestion);
        }

        $this->saveCultureProfile($user);

        $user->update(['onboarding_completed' => true]);

        return redirect()->route('employer.dashboard')
            ->with('success', 'Company setup complete. Welcome to Gridspace!');
    }

    private function saveCultureProfile($user): void
    {
        $questions = PersonalityQuestion::active()
            ->ordered()
            ->where('category', PersonalityQuestion::EMPLOYER_CATEGORY)
            ->get();

        $answers = $user->personalityAnswers()
            ->with('selectedOption')
            ->get();

        $answerText = function ($question) use ($answers) {
            $answer = $answers->firstWhere('question_id', $question?->id);

            return $answer?->selectedOption?->option_text;
        };

        $workEnvironment = $answerText($questions->get(0)) ?: 'Balanced work environment';
        $preferredEmployees = $answerText($questions->get(1)) ?: 'Balanced professionals';
        $communicationStyle = $answerText($questions->get(2)) ?: 'Collaborative and open';

        $cultureSummary = trim(implode(' ', array_filter([
            $workEnvironment ? "A {$workEnvironment} environment." : null,
            $preferredEmployees ? "Best fit for {$preferredEmployees}." : null,
            $communicationStyle ? "Communication is {$communicationStyle}." : null,
        ])));

        EmployerCultureProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'leadership_style' => 'Collaborative',
                'communication_style' => $communicationStyle,
                'innovation_level' => 'Balanced',
                'decision_making_style' => 'Collaborative',
                'work_pace' => 'Balanced',
                'collaboration_level' => 'Collaborative',
                'work_environment' => $workEnvironment,
                'preferred_traits' => $preferredEmployees ? [$preferredEmployees] : [],
                'culture_summary' => $cultureSummary,
            ]
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\PersonalityAnswer;
use App\Models\PersonalityQuestion;
use App\Services\MatchingEngineService;
use App\Services\PersonalityScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PersonalityAssessmentController extends Controller
{
    public function __construct(
        protected PersonalityScoringService $scoringService
    ) {}

    public function start()
    {
        $user = Auth::user();

        $profile = $user->personalityProfile;

        if ($profile && $profile->assessment_completed) {
            return redirect()->route('candidate.personality.complete');
        }

        $firstQuestion = PersonalityQuestion::active()
            ->ordered()
            ->with('options')
            ->first();

        if (! $firstQuestion) {
            return redirect()->route('candidate.dashboard')
                ->with('error', 'Assessment is not available at this time.');
        }

        $totalQuestions = PersonalityQuestion::active()->count();
        $answeredCount = PersonalityAnswer::where('candidate_id', $user->id)->count();
        $currentQuestionNumber = 1;

        return view('candidate.assessment.assessment', compact(
            'firstQuestion',
            'totalQuestions',
            'answeredCount',
            'currentQuestionNumber'
        ));
    }

    public function showQuestion(Request $request, PersonalityQuestion $question)
    {
        $user = Auth::user();

        if (! $question->active_status) {
            return redirect()->route('candidate.personality.start');
        }

        $question->load('options');

        $totalQuestions = PersonalityQuestion::active()->count();
        $answeredCount = PersonalityAnswer::where('candidate_id', $user->id)->count();

        $allQuestions = PersonalityQuestion::active()->ordered()->pluck('id')->toArray();
        $currentIndex = array_search($question->id, $allQuestions);
        $currentQuestionNumber = $currentIndex !== false ? $currentIndex + 1 : 1;

        return view('candidate.assessment.assessment', compact(
            'question',
            'totalQuestions',
            'answeredCount',
            'currentQuestionNumber'
        ));
    }

    public function answer(Request $request, PersonalityQuestion $question)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'option_id' => 'required|exists:personality_question_options,id',
        ]);

        $option = $question->options()->findOrFail($validated['option_id']);

        PersonalityAnswer::updateOrCreate(
            [
                'candidate_id' => $user->id,
                'question_id' => $question->id,
            ],
            [
                'selected_option_id' => $option->id,
            ]
        );

        $nextQuestion = PersonalityQuestion::active()
            ->ordered()
            ->where('display_order', '>', $question->display_order)
            ->orWhere(function ($q) use ($question) {
                $q->where('id', '>', $question->id)
                    ->where('display_order', $question->display_order);
            })
            ->where('active_status', true)
            ->orderBy('display_order')
            ->orderBy('id')
            ->first();

        if (! $nextQuestion) {
            $this->scoringService->generateProfile($user);

            return redirect()->route('candidate.personality.complete');
        }

        return redirect()->route('candidate.personality.question', $nextQuestion);
    }

    public function complete()
    {
        $user = Auth::user();
        $profile = $user->personalityProfile;

        if (! $profile || ! $profile->assessment_completed) {
            return redirect()->route('candidate.personality.start');
        }

        $recommendedJobs = app(MatchingEngineService::class)
            ->getTopMatchingJobs($user, 5);

        return view('candidate.assessment.complete', compact('profile', 'recommendedJobs'));
    }

    public function skip()
    {
        $user = Auth::user();

        if (! $user->personalityProfile) {
            $user->personalityProfile()->create([
                'assessment_completed' => false,
            ]);
        }

        return redirect()->route('candidate.dashboard')
            ->with('info', 'You can complete your personality assessment anytime.');
    }
}

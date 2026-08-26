<?php

namespace App\Http\Controllers;

use App\Jobs\RecalculateCandidateMatches;
use App\Models\PersonalityQuestion;
use App\Services\MatchingEngineService;
use App\Services\PersonalityAssessmentService;
use App\Services\PersonalityScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PersonalityAssessmentController extends Controller
{
    public function __construct(
        protected PersonalityAssessmentService $assessmentService,
        protected PersonalityScoringService $scoringService
    ) {}

    public function start()
    {
        $user = Auth::user();

        $profile = $user->personalityProfile;

        if ($profile && $profile->assessment_completed) {
            return redirect()->route('candidate.personality.complete');
        }

        $firstQuestion = $this->assessmentService->getFirstQuestion();

        if (! $firstQuestion) {
            return redirect()->route('candidate.dashboard')
                ->with('error', 'Assessment is not available at this time.');
        }

        $progress = $this->assessmentService->getQuestionProgress($user);
        $estimatedTime = $this->assessmentService->getEstimatedTimeRemaining($user);

        return view('candidate.assessment.assessment', compact(
            'firstQuestion',
            'progress',
            'estimatedTime',
        ));
    }

    public function showQuestion(Request $request, PersonalityQuestion $question)
    {
        $user = Auth::user();

        if (! $question->is_active) {
            return redirect()->route('candidate.personality.start');
        }

        $question->load('options');

        $progress = $this->assessmentService->getQuestionProgress($user);
        $currentQuestionNumber = $this->assessmentService->getCurrentQuestionNumber($user, $question);
        $estimatedTime = $this->assessmentService->getEstimatedTimeRemaining($user);
        $previousQuestion = $this->assessmentService->getPreviousQuestion($question);

        $existingAnswer = $user->personalityAnswers()
            ->where('question_id', $question->id)
            ->first();

        return view('candidate.assessment.assessment', compact(
            'question',
            'progress',
            'currentQuestionNumber',
            'estimatedTime',
            'previousQuestion',
            'existingAnswer',
        ));
    }

    public function answer(Request $request, PersonalityQuestion $question)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'option_id' => 'required|exists:personality_question_options,id',
        ]);

        $option = $question->options()->findOrFail($validated['option_id']);

        $this->assessmentService->saveAnswer($user, $question, $option->id);

        if ($request->wantsJson()) {
            $progress = $this->assessmentService->getQuestionProgress($user);
            $estimatedTime = $this->assessmentService->getEstimatedTimeRemaining($user);

            return response()->json([
                'success' => true,
                'progress' => $progress,
                'estimated_time_remaining' => $estimatedTime,
            ]);
        }

        $nextQuestion = $this->assessmentService->getNextQuestion($question);

        if (! $nextQuestion) {
            $this->assessmentService->completeAssessment($user);

            RecalculateCandidateMatches::dispatch($user);

            return redirect()->route('candidate.personality.complete');
        }

        $previous = $request->input('previous', false);

        if ($previous) {
            $prev = $this->assessmentService->getPreviousQuestion($question);

            if ($prev) {
                return redirect()->route('candidate.personality.question', $prev);
            }
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

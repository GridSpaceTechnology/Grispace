<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CandidatePersonalityProfile;
use App\Models\EmployerCultureProfile;
use App\Models\PersonalityAnswer;
use App\Models\PersonalityQuestion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PersonalityAnalyticsController extends Controller
{
    public function index(): View
    {
        $totalCandidates = User::where('role', 'candidate')->count();
        $completedAssessments = CandidatePersonalityProfile::where('assessment_completed', true)->count();
        $completionPercentage = $totalCandidates > 0
            ? round(($completedAssessments / $totalCandidates) * 100)
            : 0;

        $totalEmployers = User::where('role', 'employer')->count();
        $completedCulture = EmployerCultureProfile::whereNotNull('culture_summary')->count();
        $employerCompletionPercentage = $totalEmployers > 0
            ? round(($completedCulture / $totalEmployers) * 100)
            : 0;

        $totalQuestions = PersonalityQuestion::count();
        $activeQuestions = PersonalityQuestion::where('is_active', true)->count();

        $totalAnswers = PersonalityAnswer::count();

        $questionsWithAnswerCount = PersonalityQuestion::withCount('options')
            ->withCount(['options as answer_count' => function ($q) {
                $q->select(DB::raw('COUNT(personality_answers.id)'))
                    ->join('personality_answers', 'personality_question_options.id', '=', 'personality_answers.selected_option_id');
            }])
            ->get();

        $dropOffData = $this->calculateDropOff();

        return view('admin.personality.analytics.index', compact(
            'totalCandidates',
            'completedAssessments',
            'completionPercentage',
            'totalEmployers',
            'completedCulture',
            'employerCompletionPercentage',
            'totalQuestions',
            'activeQuestions',
            'totalAnswers',
            'questionsWithAnswerCount',
            'dropOffData',
        ));
    }

    private function calculateDropOff(): array
    {
        $questions = PersonalityQuestion::active()->ordered()->pluck('id');
        $dropOff = [];
        $previousCount = null;

        foreach ($questions as $index => $questionId) {
            $count = PersonalityAnswer::where('question_id', $questionId)->count();

            $dropOffRate = $previousCount !== null && $previousCount > 0
                ? round((($previousCount - $count) / $previousCount) * 100, 1)
                : 0;

            $dropOff[] = [
                'step' => $index + 1,
                'question_id' => $questionId,
                'answers_count' => $count,
                'drop_off_rate' => $dropOffRate,
            ];

            $previousCount = $count;
        }

        return $dropOff;
    }
}

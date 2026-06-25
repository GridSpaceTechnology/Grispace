<?php

namespace App\Services;

use App\Models\PersonalityAnswer;
use App\Models\PersonalityQuestion;
use App\Models\User;

class PersonalityAssessmentService
{
    public function __construct(
        protected PersonalityScoringService $scoringService
    ) {}

    public function getFirstQuestion(): ?PersonalityQuestion
    {
        return PersonalityQuestion::active()
            ->ordered()
            ->with('options')
            ->first();
    }

    public function getTotalQuestions(): int
    {
        return PersonalityQuestion::active()->count();
    }

    public function getAnsweredCount(User $user): int
    {
        return PersonalityAnswer::where('candidate_id', $user->id)->count();
    }

    public function getQuestionProgress(User $user): array
    {
        $total = $this->getTotalQuestions();
        $answered = $this->getAnsweredCount($user);

        return [
            'total' => $total,
            'answered' => $answered,
            'percentage' => $total > 0 ? (int) round(($answered / $total) * 100) : 0,
        ];
    }

    public function getCurrentQuestionNumber(User $user, PersonalityQuestion $question): int
    {
        $allQuestions = PersonalityQuestion::active()->ordered()->pluck('id')->toArray();
        $index = array_search($question->id, $allQuestions);

        return $index !== false ? $index + 1 : 1;
    }

    public function getNextQuestion(PersonalityQuestion $current): ?PersonalityQuestion
    {
        return PersonalityQuestion::active()
            ->ordered()
            ->where('display_order', '>', $current->display_order)
            ->orWhere(function ($q) use ($current) {
                $q->where('id', '>', $current->id)
                    ->where('display_order', $current->display_order);
            })
            ->where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('id')
            ->first();
    }

    public function getPreviousQuestion(PersonalityQuestion $current): ?PersonalityQuestion
    {
        return PersonalityQuestion::active()
            ->ordered()
            ->where('display_order', '<', $current->display_order)
            ->orWhere(function ($q) use ($current) {
                $q->where('id', '<', $current->id)
                    ->where('display_order', $current->display_order);
            })
            ->where('is_active', true)
            ->orderBy('display_order', 'desc')
            ->orderBy('id', 'desc')
            ->first();
    }

    public function saveAnswer(User $user, PersonalityQuestion $question, int $optionId): PersonalityAnswer
    {
        return PersonalityAnswer::updateOrCreate(
            [
                'candidate_id' => $user->id,
                'question_id' => $question->id,
            ],
            [
                'selected_option_id' => $optionId,
            ]
        );
    }

    public function isLastQuestion(PersonalityQuestion $question): bool
    {
        return $this->getNextQuestion($question) === null;
    }

    public function completeAssessment(User $user): void
    {
        $this->scoringService->generateProfile($user);
    }

    public function getEstimatedTimeRemaining(User $user): int
    {
        $answered = $this->getAnsweredCount($user);
        $total = $this->getTotalQuestions();
        $remaining = $total - $answered;
        $secondsPerQuestion = 18;

        return max(1, $remaining * $secondsPerQuestion);
    }
}

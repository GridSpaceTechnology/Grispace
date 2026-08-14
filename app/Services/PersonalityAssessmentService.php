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
        return PersonalityQuestion::candidate()
            ->active()
            ->ordered()
            ->with('options')
            ->first();
    }

    public function getTotalQuestions(): int
    {
        return PersonalityQuestion::candidate()->active()->count();
    }

    public function getAnsweredCount(User $user): int
    {
        return PersonalityAnswer::where('candidate_id', $user->id)
            ->whereHas('question', fn ($q) => $q->candidate())
            ->count();
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
        return $this->getQuestionNumberForSections(PersonalityQuestion::CANDIDATE_CATEGORIES, $question);
    }

    public function getNextQuestion(PersonalityQuestion $current): ?PersonalityQuestion
    {
        return $this->getNextQuestionForSections(PersonalityQuestion::CANDIDATE_CATEGORIES, $current);
    }

    public function getPreviousQuestion(PersonalityQuestion $current): ?PersonalityQuestion
    {
        return $this->getPreviousQuestionForSections(PersonalityQuestion::CANDIDATE_CATEGORIES, $current);
    }

    public function getFirstQuestionForSections(array $categories): ?PersonalityQuestion
    {
        return PersonalityQuestion::active()
            ->ordered()
            ->whereIn('category', $categories)
            ->with('options')
            ->first();
    }

    public function getTotalQuestionsForSections(array $categories): int
    {
        return PersonalityQuestion::active()->whereIn('category', $categories)->count();
    }

    public function getQuestionNumberForSections(array $categories, PersonalityQuestion $question): int
    {
        $ids = $this->orderedQuestionIds($categories);
        $index = array_search($question->id, $ids);

        return $index !== false ? $index + 1 : 1;
    }

    public function getNextQuestionForSections(array $categories, PersonalityQuestion $current): ?PersonalityQuestion
    {
        $ids = $this->orderedQuestionIds($categories);
        $index = array_search($current->id, $ids);
        $nextId = $index !== false && isset($ids[$index + 1]) ? $ids[$index + 1] : null;

        return $nextId ? PersonalityQuestion::with('options')->find($nextId) : null;
    }

    public function getPreviousQuestionForSections(array $categories, PersonalityQuestion $current): ?PersonalityQuestion
    {
        $ids = $this->orderedQuestionIds($categories);
        $index = array_search($current->id, $ids);
        $previousId = $index !== false && $index > 0 ? $ids[$index - 1] : null;

        return $previousId ? PersonalityQuestion::with('options')->find($previousId) : null;
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
                'section' => $question->category,
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

    private function orderedQuestionIds(array $categories): array
    {
        return PersonalityQuestion::active()
            ->ordered()
            ->whereIn('category', $categories)
            ->pluck('id')
            ->all();
    }
}

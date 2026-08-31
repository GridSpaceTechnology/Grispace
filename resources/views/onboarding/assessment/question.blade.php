@extends('onboarding.layout')

@section('content')
    @php
        $sectionLabels = [
            'work_style' => 'Work Style',
            'communication_style' => 'Communication Style',
            'team_dynamics' => 'Team Dynamics',
            'problem_solving' => 'Problem Solving',
            'leadership_initiative' => 'Leadership & Initiative',
            'work_environment_preference' => 'Work Environment',
            'motivation_drivers' => 'Motivation Drivers',
            'temperament_indicators' => 'Temperament Indicators',
            'organizational_culture' => 'Organizational Culture',
        ];

        $sectionDescription = $sectionLabels[$question->category ?? ''] ?? ucwords(str_replace('_', ' ', $question->category ?? 'Assessment'));
    @endphp
    <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-medium text-gray-600">
            Question {{ $currentQuestionNumber }} of {{ $totalQuestions }}
        </span>
        <span class="text-sm font-medium text-indigo-600">
            {{ (int) round(($currentQuestionNumber / $totalQuestions) * 100) }}% complete
        </span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden mb-6">
        <div class="h-full bg-indigo-600 rounded-full transition-all duration-500"
             style="width: {{ (int) round(($currentQuestionNumber / $totalQuestions) * 100) }}%"></div>
    </div>

    <div class="inline-flex items-center gap-2 mb-3 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-indigo-100 text-indigo-700">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
        {{ $sectionDescription }}
    </div>

    <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-6 leading-tight">
        {{ $question->question_text }}
    </h2>

    <form method="POST" action="{{ route('candidate.onboarding.assessment.answer', ['step' => $step, 'question' => $question]) }}" class="space-y-3">
        @csrf
        @foreach($question->options as $option)
            <button type="submit" name="option_id" value="{{ $option->id }}"
                    class="option-btn w-full text-left p-4 md:p-5 rounded-xl border-2 transition-all duration-200 group
                    {{ $existingAnswer && $existingAnswer->selected_option_id === $option->id ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200 hover:border-indigo-400 hover:bg-indigo-50' }}">
                <span class="text-base md:text-lg text-gray-700 group-hover:text-gray-900 font-medium">
                    {{ $option->option_text }}
                </span>
            </button>
        @endforeach
    </form>

    <div class="mt-6">
        @if($previousQuestion)
            <a href="{{ route('candidate.onboarding.assessment.question', ['step' => $step, 'question' => $previousQuestion]) }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
        @else
            <a href="{{ route('candidate.onboarding.step', ['step' => max(1, $step - 1)]) }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
        @endif
    </div>
@endsection

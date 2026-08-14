@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 py-12">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-slate-900">Define Your Company Culture</h1>
            <p class="mt-2 text-slate-600">Three quick questions to help us match you with the right candidates.</p>
        </div>

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-slate-600">
                Question {{ $currentQuestionNumber }} of {{ $totalQuestions }}
            </span>
            <span class="text-sm font-medium" style="color: #EB5233;">
                {{ (int) round(($currentQuestionNumber / $totalQuestions) * 100) }}% complete
            </span>
        </div>
        <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden mb-8">
            <div class="h-full rounded-full transition-all duration-500"
                 style="width: {{ (int) round(($currentQuestionNumber / $totalQuestions) * 100) }}%; background-color: #EB5233;"></div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 md:p-8">
            <h2 class="text-xl md:text-2xl font-bold text-slate-900 mb-6 leading-tight">
                {{ $question->question_text }}
            </h2>

            <form method="POST" action="{{ route('employer.onboarding.culture.answer', $question) }}" class="space-y-3">
                @csrf
                @foreach($question->options as $option)
                    <button type="submit" name="option_id" value="{{ $option->id }}"
                            class="w-full text-left p-4 md:p-5 rounded-xl border-2 transition-all duration-200 group
                            {{ $existingAnswer && $existingAnswer->selected_option_id === $option->id ? 'border-orange-500 bg-orange-50' : 'border-slate-200 hover:border-orange-400 hover:bg-orange-50' }}"
                            style="{{ $existingAnswer && $existingAnswer->selected_option_id === $option->id ? 'border-color: #EB5233;' : '' }}">
                        <span class="text-base md:text-lg text-slate-700 group-hover:text-slate-900 font-medium">
                            {{ $option->option_text }}
                        </span>
                    </button>
                @endforeach
            </form>

            <div class="mt-6 flex items-center justify-between">
                @if($previousQuestion)
                    <a href="{{ route('employer.onboarding.culture.question', $previousQuestion) }}"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back
                    </a>
                @else
                    <a href="{{ route('employer.setup') }}"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Back
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

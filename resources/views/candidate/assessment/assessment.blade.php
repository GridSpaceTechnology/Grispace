@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-white to-orange-50">
    <div class="max-w-2xl mx-auto px-4 py-8">
        <div class="mb-8">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-medium text-slate-600">
                    Question {{ $currentQuestionNumber }} of {{ $totalQuestions }}
                </span>
                <span class="text-sm font-medium text-primary" style="color: #EB5233;">
                    {{ $answeredCount > 0 ? round(($currentQuestionNumber - 1) / $totalQuestions * 100) : 0 }}% complete
                </span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500 ease-out"
                     style="width: {{ $answeredCount > 0 ? round(($currentQuestionNumber - 1) / $totalQuestions * 100) : 0 }}%; background-color: #EB5233;">
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-2 text-center">
                Employers discover complete profiles faster. Keep going!
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-8 md:p-12">
            @isset($question)
                <p class="text-xs font-semibold uppercase tracking-wider text-primary mb-4" style="color: #EB5233;">
                    {{ str_replace('_', ' ', $question->category) }}
                </p>

                <h2 class="text-2xl md:text-3xl font-bold text-slate-900 mb-8 leading-tight">
                    {{ $question->question_text }}
                </h2>

                <form method="POST" action="{{ route('candidate.personality.answer', $question) }}" class="space-y-3">
                    @csrf
                    @foreach($question->options as $option)
                        <button type="submit" name="option_id" value="{{ $option->id }}" class="w-full text-left p-5 rounded-xl border-2 border-slate-200 hover:border-primary hover:bg-orange-50 transition-all duration-200 group">
                            <span class="text-lg text-slate-700 group-hover:text-slate-900 font-medium">
                                {{ $option->option_text }}
                            </span>
                        </button>
                    @endforeach
                </form>

                <div class="mt-8 flex items-center justify-between">
                    <a href="{{ route('candidate.personality.skip') }}" class="text-sm text-slate-400 hover:text-slate-600 transition-colors">
                        Save & continue later
                    </a>
                    <span class="text-xs text-slate-400">
                        Just a few more questions
                    </span>
                </div>
            @else
                <div class="text-center py-12">
                    <h2 class="text-2xl font-bold text-slate-900 mb-4">
                        Ready to Get Started?
                    </h2>
                    <p class="text-slate-600 mb-8">
                        Discover your work personality in just a few minutes.
                    </p>
                    <form method="GET" action="{{ route('candidate.personality.question', $firstQuestion ?? 1) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white rounded-xl hover:opacity-90 transition-all duration-200 shadow-lg" style="background-color: #EB5233;">
                            Begin Assessment
                        </button>
                    </form>
                    <div class="mt-4">
                        <a href="{{ route('candidate.personality.skip') }}" class="text-sm text-slate-400 hover:text-slate-600">
                            Skip for now
                        </a>
                    </div>
                </div>
            @endisset
        </div>

        <div class="mt-6 text-center">
            <p class="text-xs text-slate-400">
                Your responses help us find the best opportunities for you.
                No personality labels — just better matches.
            </p>
        </div>
    </div>
</div>
@endsection

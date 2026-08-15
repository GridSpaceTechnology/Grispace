@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-white to-orange-50">
    <div class="max-w-2xl mx-auto px-4 py-6 md:py-8">
        @isset($question)
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-600">
                        Question {{ $currentQuestionNumber }} of {{ $progress['total'] }}
                    </span>
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-slate-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ gmdate('i:s', $estimatedTime) }} remaining
                        </span>
                        <span class="text-sm font-medium" style="color: #EB5233;">
                            {{ $progress['percentage'] }}% complete
                        </span>
                    </div>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500 ease-out"
                         style="width: {{ $progress['percentage'] }}%; background-color: #EB5233;">
                    </div>
                </div>
                <p class="text-xs text-slate-400 mt-2 text-center">
                    Employers discover complete profiles faster. Keep going!
                </p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-6 md:p-10">
                <p class="text-xs font-semibold uppercase tracking-wider mb-3" style="color: #EB5233;">
                    {{ str_replace('_', ' ', $question->category) }}
                </p>

                <h2 class="text-xl md:text-2xl font-bold text-slate-900 mb-6 leading-tight">
                    {{ $question->question_text }}
                </h2>

                <form method="POST" action="{{ route('candidate.personality.answer', $question) }}" class="space-y-3" id="assessment-form">
                    @csrf
                    @foreach($question->options as $option)
                        <button type="submit" name="option_id" value="{{ $option->id }}"
                                class="option-btn w-full text-left p-4 md:p-5 rounded-xl border-2 transition-all duration-200 group
                                {{ $existingAnswer && $existingAnswer->selected_option_id === $option->id ? 'border-orange-500 bg-orange-50' : 'border-slate-200 hover:border-orange-400 hover:bg-orange-50' }}"
                                style="{{ $existingAnswer && $existingAnswer->selected_option_id === $option->id ? 'border-color: #EB5233;' : '' }}">
                            <span class="text-base md:text-lg text-slate-700 group-hover:text-slate-900 font-medium">
                                {{ $option->option_text }}
                            </span>
                        </button>
                    @endforeach
                </form>

                <div class="mt-6 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        @isset($previousQuestion)
                            <a href="{{ route('candidate.personality.question', $previousQuestion) }}"
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-600 border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                                Previous
                            </a>
                        @endisset
                    </div>
                    <form method="POST" action="{{ route('candidate.personality.skip') }}">
                        @csrf
                        <button type="submit" class="text-sm text-slate-400 hover:text-slate-600 transition-colors">
                            Save & continue later
                        </button>
                    </form>
                </div>
            </div>
        @else
            <div class="mb-8">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-slate-600">
                        0 of {{ $progress['total'] }} questions
                    </span>
                    <span class="text-sm font-medium" style="color: #EB5233;">
                        0% complete
                    </span>
                </div>
                <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
                    <div class="h-full rounded-full" style="width: 0%; background-color: #EB5233;"></div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg border border-slate-100 p-8 md:p-12">
                <div class="text-center py-8 md:py-12">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-6" style="background-color: #EB5233;">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold text-slate-900 mb-4">
                        Ready to Get Started?
                    </h2>
                    <p class="text-slate-600 mb-2">
                        Discover your work personality in just a few minutes.
                    </p>
                    <p class="text-sm text-slate-400 mb-8">
                        {{ $progress['total'] }} questions &middot; ~8–12 minutes
                    </p>
                    <form method="GET" action="{{ route('candidate.personality.question', $firstQuestion ?? 1) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white rounded-xl hover:opacity-90 transition-all duration-200 shadow-lg"
                                style="background-color: #EB5233;">
                            Begin Assessment
                        </button>
                    </form>
                    <div class="mt-4">
                        <form method="POST" action="{{ route('candidate.personality.skip') }}">
                            @csrf
                            <button type="submit" class="text-sm text-slate-400 hover:text-slate-600">
                                Skip for now
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endisset

        <div class="mt-6 text-center">
            <p class="text-xs text-slate-400">
                Your responses help us find the best opportunities for you.
                No personality labels &mdash; just better matches.
            </p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('.option-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            this.closest('form').submit();
        });
    });
</script>
@endpush
@endsection

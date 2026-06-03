@php
    $stageColors = [
        'applied' => 'bg-blue-100 text-blue-800',
        'shortlisted' => 'bg-purple-100 text-purple-800',
        'interview' => 'bg-yellow-100 text-yellow-800',
        'offer' => 'bg-orange-100 text-orange-800',
        'hired' => 'bg-green-100 text-green-800',
        'rejected' => 'bg-red-100 text-red-800',
        'withdrawn' => 'bg-gray-100 text-gray-800',
    ];
    $candidate = $application->candidate;
    $profile = $candidate->candidateProfile;
    $personality = $candidate->personalityProfile;
@endphp

@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('employer.ats.dashboard') }}" class="text-indigo-600 hover:text-indigo-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to ATS Dashboard
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-brand-primary/10 rounded-full flex items-center justify-center">
                                <span class="text-brand-primary font-bold text-xl">{{ substr($candidate->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">{{ $candidate->name }}</h1>
                                <p class="text-gray-500">{{ $profile?->desired_role ?? 'No role specified' }}</p>
                                <div class="flex items-center gap-3 mt-1 text-sm text-gray-400">
                                    @if($profile?->location)
                                        <span>{{ $profile->location }}</span>
                                    @endif
                                    @if($profile?->years_of_experience)
                                        <span>{{ $profile->years_of_experience }} years experience</span>
                                    @endif
                                    <span>{{ $profile?->employment_type ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <span class="px-3 py-1.5 rounded-full text-sm font-medium {{ $stageColors[$application->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($application->status) }}
                        </span>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Personality Profile</h2>
                    @if($personality)
                        <div class="grid grid-cols-2 gap-4">
                            @foreach(['work_style' => 'Work Style', 'communication_style' => 'Communication', 'collaboration_style' => 'Collaboration', 'leadership_style' => 'Leadership', 'motivation_type' => 'Motivation', 'temperament_type' => 'Temperament'] as $key => $label)
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <p class="text-xs text-gray-500 mb-1">{{ $label }}</p>
                                    <p class="text-sm font-medium text-gray-900">{{ $personality->$key ?? 'N/A' }}</p>
                                </div>
                            @endforeach
                        </div>
                        @if($personality->personality_summary)
                            <div class="mt-4 p-4 bg-indigo-50 rounded-lg">
                                <p class="text-sm text-indigo-700">{{ $personality->personality_summary }}</p>
                            </div>
                        @endif
                    @else
                        <p class="text-gray-400 text-sm">Personality assessment not yet completed.</p>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Skills</h2>
                    @if($candidate->candidateSkills->isNotEmpty())
                        <div class="flex flex-wrap gap-2">
                            @foreach($candidate->candidateSkills as $skill)
                                <span class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-full text-sm font-medium">
                                    {{ $skill->skill?->name ?? $skill->skill_name ?? 'Unknown' }}
                                    @if($skill->proficiency)
                                        <span class="text-gray-400">({{ $skill->proficiency }})</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-400 text-sm">No skills listed.</p>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Experience</h2>
                    @if($candidate->candidateExperiences->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($candidate->candidateExperiences as $exp)
                                <div class="border-l-2 border-brand-primary pl-4">
                                    <h3 class="font-medium text-gray-900">{{ $exp->title ?? $exp->role }}</h3>
                                    <p class="text-sm text-gray-500">{{ $exp->company }} &bull; {{ $exp->start_date?->format('M Y') }} - {{ $exp->end_date?->format('M Y') ?? 'Present' }}</p>
                                    @if($exp->description)
                                        <p class="text-sm text-gray-600 mt-1">{{ $exp->description }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-400 text-sm">No experience listed.</p>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Education</h2>
                    @if($candidate->candidateEducation->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($candidate->candidateEducation as $edu)
                                <div class="border-l-2 border-gray-300 pl-4">
                                    <h3 class="font-medium text-gray-900">{{ $edu->degree }} in {{ $edu->field_of_study }}</h3>
                                    <p class="text-sm text-gray-500">{{ $edu->institution }} &bull; {{ $edu->start_year }} - {{ $edu->end_year ?? 'Present' }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-400 text-sm">No education listed.</p>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Notes</h2>
                    </div>
                    <form method="POST" action="{{ route('employer.ats.notes.store', $application) }}" class="mb-6">
                        @csrf
                        <textarea name="content" rows="3" placeholder="Add a note about this candidate..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-transparent resize-none" required></textarea>
                        <div class="mt-2 flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary-hover transition-colors text-sm font-medium">
                                Add Note
                            </button>
                        </div>
                    </form>
                    <div class="space-y-4">
                        @forelse($application->notes as $note)
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-brand-primary/10 rounded-full flex items-center justify-center">
                                            <span class="text-brand-primary font-medium text-xs">{{ substr($note->employer->name, 0, 1) }}</span>
                                        </div>
                                        <span class="text-sm font-medium text-gray-900">{{ $note->employer->name }}</span>
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $note->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-sm text-gray-600">{{ $note->content }}</p>
                            </div>
                        @empty
                            <p class="text-gray-400 text-sm text-center py-4">No notes yet. Add the first note above.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Match Score</h2>
                    @if($application->match_score)
                        <div class="text-center">
                            <div class="text-4xl font-bold @if($application->match_score >= 80) text-green-600 @elseif($application->match_score >= 60) text-yellow-600 @else text-red-600 @endif">
                                {{ $application->match_score }}%
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3 mt-2">
                                <div class="h-3 rounded-full @if($application->match_score >= 80) bg-green-500 @elseif($application->match_score >= 60) bg-yellow-500 @else bg-red-500 @endif" style="width: {{ $application->match_score }}%"></div>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-400 text-sm text-center">Not yet calculated</p>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Ratings</h2>
                    @foreach(['skills', 'communication', 'experience', 'culture_fit', 'overall'] as $category)
                        @php
                            $rating = $ratings->get($category)?->first();
                        @endphp
                        <form method="POST" action="{{ route('employer.ats.ratings.store', $application) }}" class="mb-4">
                            @csrf
                            <input type="hidden" name="category" value="{{ $category }}">
                            <p class="text-sm font-medium text-gray-700 capitalize mb-1">{{ str_replace('_', ' ', $category) }}</p>
                            <div class="flex items-center gap-1 mb-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <button type="submit" name="rating" value="{{ $i }}" class="focus:outline-none transition-colors {{ $rating && $rating->rating >= $i ? 'text-yellow-400' : 'text-gray-300' }} hover:text-yellow-400">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    </button>
                                @endfor
                            </div>
                            @if($rating)
                                <p class="text-xs text-gray-400">Rating: {{ $rating->rating }}/5</p>
                                @if($rating->review)
                                    <p class="text-xs text-gray-500 mt-1">{{ $rating->review }}</p>
                                @endif
                            @endif
                            <input type="hidden" name="review" value="">
                        </form>
                    @endforeach
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Stage History</h2>
                    <div class="space-y-4">
                        @forelse($application->stageHistories->sortByDesc('created_at') as $history)
                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 mt-2 rounded-full bg-brand-primary flex-shrink-0"></div>
                                <div>
                                    <p class="text-sm text-gray-900">
                                        Moved from <span class="font-medium">{{ $history->from_status ? ucfirst($history->from_status) : 'Applied' }}</span>
                                        to <span class="font-medium">{{ ucfirst($history->to_status) }}</span>
                                    </p>
                                    <p class="text-xs text-gray-400">{{ $history->employer->name }} &bull; {{ $history->created_at->diffForHumans() }}</p>
                                    @if($history->notes)
                                        <p class="text-xs text-gray-500 mt-1">{{ $history->notes }}</p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-400 text-sm text-center py-4">No stage history yet.</p>
                        @endforelse
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 mt-2 rounded-full bg-green-500 flex-shrink-0"></div>
                            <div>
                                <p class="text-sm text-gray-900">Applied for <span class="font-medium">{{ $application->job->title }}</span></p>
                                <p class="text-xs text-gray-400">{{ $application->applied_at?->diffForHumans() ?? $application->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                @if($application->interviews->isNotEmpty())
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Interviews</h2>
                        <div class="space-y-3">
                            @foreach($application->interviews as $interview)
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-900 capitalize">{{ $interview->interview_type }}</span>
                                        <span class="px-2 py-0.5 rounded text-xs font-medium @if($interview->status === 'scheduled') bg-blue-100 text-blue-700 @elseif($interview->status === 'completed') bg-green-100 text-green-700 @else bg-red-100 text-red-700 @endif">
                                            {{ ucfirst($interview->status) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500">{{ $interview->scheduled_at->format('M d, Y g:i A') }}</p>
                                    @if($interview->meeting_link)
                                        <a href="{{ $interview->meeting_link }}" target="_blank" class="text-xs text-indigo-600 hover:text-indigo-700">Meeting Link</a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
                    <div class="space-y-3">
                        @if($application->status !== 'rejected' && $application->status !== 'hired' && $application->status !== 'withdrawn')
                            @php
                                $nextActions = [
                                    'applied' => ['action' => 'next', 'label' => 'Shortlist', 'color' => 'bg-purple-600 hover:bg-purple-700'],
                                    'shortlisted' => ['action' => 'next', 'label' => 'Move to Interview', 'color' => 'bg-yellow-600 hover:bg-yellow-700'],
                                    'interview' => ['action' => 'next', 'label' => 'Send Offer', 'color' => 'bg-orange-600 hover:bg-orange-700'],
                                    'offer' => ['action' => 'next', 'label' => 'Mark Hired', 'color' => 'bg-green-600 hover:bg-green-700'],
                                ];
                                $next = $nextActions[$application->status] ?? null;
                            @endphp
                            @if($next)
                                <form method="POST" action="{{ route('employer.applications.move', $application) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="{{ $next['action'] }}">
                                    <button type="submit" class="w-full px-4 py-2 {{ $next['color'] }} text-white rounded-lg transition-colors text-sm font-medium">
                                        {{ $next['label'] }}
                                    </button>
                                </form>
                            @endif
                            @if($application->status !== 'rejected')
                                <form method="POST" action="{{ route('employer.applications.move', $application) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm font-medium" onclick="return confirm('Are you sure you want to reject this candidate?')">
                                        Reject Candidate
                                    </button>
                                </form>
                            @endif
                        @endif
                        @if($application->status === 'interview')
                            <a href="{{ route('employer.applications.schedule-interview', $application) }}" class="block w-full px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-colors text-sm font-medium text-center">
                                Schedule Interview
                            </a>
                        @endif
                        <a href="{{ route('employer.jobs.pipeline', ['job' => $application->job_id]) }}" class="block w-full px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors text-sm font-medium text-center">
                            View Pipeline
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

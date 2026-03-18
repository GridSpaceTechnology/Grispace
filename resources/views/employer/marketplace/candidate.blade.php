@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('employer.marketplace.index') }}" class="text-brand-primary hover:text-brand-primary-hover flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Marketplace
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="mb-6 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded-lg">
                {{ session('info') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="flex items-start gap-4 mb-6">
                        <div class="w-20 h-20 bg-brand-primary/10 rounded-full flex items-center justify-center">
                            <span class="text-brand-primary text-2xl font-semibold">{{ substr($candidate->name, 0, 1) }}</span>
                        </div>
                        <div class="flex-1">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $candidate->name }}</h1>
                            <p class="text-lg text-gray-600">{{ $candidate->candidateProfile?->current_role ?? 'Not specified' }}</p>
                            <p class="text-gray-500">{{ $candidate->candidateProfile?->desired_role ?? '' }}</p>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-6">
                        @if($candidate->candidateProfile?->location)
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                                {{ $candidate->candidateProfile->location }}
                            </span>
                        @endif
                        @if($candidate->candidateProfile?->years_of_experience)
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $candidate->candidateProfile->years_of_experience }} years experience
                            </span>
                        @endif
                        @if($candidate->candidateProfile?->work_preference)
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                                </svg>
                                {{ ucfirst($candidate->candidateProfile->work_preference) }}
                            </span>
                        @endif
                        @if($candidate->candidateProfile?->availability)
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ ucfirst($candidate->candidateProfile->availability) }}
                            </span>
                        @endif
                    </div>

                    @if($candidate->candidateProfile?->greatest_achievement)
                        <div class="mb-6">
                            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wide mb-2">Greatest Achievement</h3>
                            <p class="text-gray-700">{{ $candidate->candidateProfile->greatest_achievement }}</p>
                        </div>
                    @endif

                    @if($aiInsights)
                        <div class="mb-6 p-4 bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg border border-indigo-200">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                                <h3 class="text-sm font-semibold text-indigo-900 uppercase tracking-wide">AI Insight</h3>
                            </div>
                            
                            @if($aiInsights['summary'])
                                <div class="mb-4">
                                    <h4 class="text-xs font-medium text-indigo-700 mb-1">Summary</h4>
                                    <p class="text-sm text-gray-800">{{ $aiInsights['summary'] }}</p>
                                </div>
                            @endif

                            @if($aiInsights['strengths'] && count($aiInsights['strengths']) > 0)
                                <div class="mb-3">
                                    <h4 class="text-xs font-medium text-indigo-700 mb-2">Strengths</h4>
                                    <ul class="space-y-1">
                                        @foreach($aiInsights['strengths'] as $strength)
                                            <li class="flex items-start gap-2 text-sm text-gray-700">
                                                <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ $strength }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($aiInsights['risks'] && count($aiInsights['risks']) > 0)
                                <div class="mb-3">
                                    <h4 class="text-xs font-medium text-indigo-700 mb-2">Potential Risks</h4>
                                    <ul class="space-y-1">
                                        @foreach($aiInsights['risks'] as $risk)
                                            <li class="flex items-start gap-2 text-sm text-gray-700">
                                                <svg class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92A5.502 5.502 0 0014.8 17H5.2a5.502 5.502 0 00-2.943-7.901l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ $risk }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if($aiInsights['recommendation'])
                                <div class="pt-3 border-t border-indigo-200">
                                    <h4 class="text-xs font-medium text-indigo-700 mb-1">Hiring Recommendation</h4>
                                    <p class="text-sm text-gray-800">{{ $aiInsights['recommendation'] }}</p>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Skills</h2>
                    <div class="flex flex-wrap gap-2">
                        @forelse($candidate->candidateSkills as $skill)
                            <span class="px-3 py-1 bg-slate-100 rounded-full text-sm text-slate-700">
                                {{ $skill->skill_name }}
                                @if($skill->proficiency_level)
                                    <span class="text-gray-500 text-xs">({{ $skill->proficiency_level }}%)</span>
                                @endif
                            </span>
                        @empty
                            <p class="text-gray-500">No skills added yet</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Experience</h2>
                    <div class="space-y-4">
                        @forelse($candidate->candidateExperiences as $experience)
                            <div class="border-l-2 border-brand-primary pl-4">
                                <h3 class="font-semibold text-gray-900">{{ $experience->role }}</h3>
                                <p class="text-gray-600">{{ $experience->company }}</p>
                                <p class="text-sm text-gray-500">{{ $experience->duration }}</p>
                                @if($experience->description)
                                    <p class="mt-2 text-gray-700">{{ $experience->description }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="text-gray-500">No experience added yet</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Education</h2>
                    <div class="space-y-4">
                        @forelse($candidate->candidateEducation as $education)
                            <div class="border-l-2 border-brand-primary pl-4">
                                <h3 class="font-semibold text-gray-900">{{ $education->qualification }}</h3>
                                <p class="text-gray-600">{{ $education->institution }}</p>
                                <p class="text-sm text-gray-500">Completed: {{ $education->year_completed }}</p>
                            </div>
                        @empty
                            <p class="text-gray-500">No education added yet</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sticky top-24">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
                    
                    <div class="space-y-3">
                        @if($isShortlisted)
                            <div class="p-3 bg-green-100 border border-green-300 rounded-lg text-center text-green-700">
                                Already Shortlisted
                            </div>
                        @else
                            <form method="POST" action="{{ route('employer.marketplace.shortlist', ['candidate' => $candidate->id]) }}">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary-hover transition-colors">
                                    Add to Shortlist
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('employer.interviews.create', ['candidate_id' => $candidate->id]) }}" 
                           class="block w-full px-4 py-2 border border-brand-primary text-brand-primary text-center rounded-lg hover:bg-brand-primary hover:text-white transition-colors">
                            Schedule Interview
                        </a>

                        <button type="button" onclick="startConversation({{ $candidate->id }})"
                           class="block w-full px-4 py-2 mt-2 border border-slate-300 text-slate-700 text-center rounded-lg hover:bg-slate-50 transition-colors">
                            Message Candidate
                        </button>
                    </div>
                </div>

                @if($candidate->candidateAssessment)
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Assessment</h2>
                        
                        @if($candidate->candidateAssessment->temperament_type)
                            <div class="mb-4">
                                <p class="text-sm text-gray-500 mb-1">Temperament</p>
                                <p class="font-semibold text-gray-900 capitalize">{{ $candidate->candidateAssessment->temperament_type }}</p>
                            </div>
                        @endif

                        @if($candidate->candidateAssessment->skill_score)
                            @php $score = $candidate->candidateAssessment->skill_score; @endphp
                            <div class="mb-4">
                                <p class="text-sm text-gray-500 mb-1">Skill Score</p>
                                <div class="flex items-center gap-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                                        <div class="bg-brand-primary h-2 rounded-full" style="width: {{ $score }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium">{{ $score }}%</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                @if($candidate->candidateMedia)
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Media</h2>
                        
                        @if($candidate->candidateMedia->intro_video_url)
                            <div class="mb-3">
                                <p class="text-sm text-gray-500 mb-1">Video Introduction</p>
                                <a href="{{ $candidate->candidateMedia->intro_video_url }}" target="_blank" class="text-brand-primary hover:underline">
                                    Watch Video
                                </a>
                            </div>
                        @endif

                        @if($candidate->candidateMedia->linkedin_url)
                            <div class="mb-3">
                                <p class="text-sm text-gray-500 mb-1">LinkedIn</p>
                                <a href="{{ $candidate->candidateMedia->linkedin_url }}" target="_blank" class="text-brand-primary hover:underline">
                                    View Profile
                                </a>
                            </div>
                        @endif

                        @if($candidate->candidateMedia->github_url)
                            <div class="mb-3">
                                <p class="text-sm text-gray-500 mb-1">GitHub</p>
                                <a href="{{ $candidate->candidateMedia->github_url }}" target="_blank" class="text-brand-primary hover:underline">
                                    View Profile
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
async function startConversation(candidateId) {
    try {
        const response = await fetch('/employer/messages/conversation/' + candidateId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
        
        const data = await response.json();
        
        if (response.ok) {
            window.location.href = '/employer/messages/' + data.conversation_id;
        } else {
            alert(data.error || 'Failed to start conversation');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Failed to start conversation');
    }
}
</script>
@endpush
@endsection

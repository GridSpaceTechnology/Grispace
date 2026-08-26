@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('jobs.index') }}" class="text-brand-primary hover:text-brand-primary-hover flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Jobs
            </a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 sm:p-8">
                <div class="flex items-start gap-4 mb-6">
                    <x-company-logo :company="$job->company" :fallback="$job->company?->name ?? $job->employer->name ?? 'Company'" size="lg"/>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $job->title }}</h1>
                        <p class="text-lg text-gray-600">
                            @if($job->company)
                                <a href="{{ route('employers.show', $job->company) }}" class="hover:text-brand-primary hover:underline">{{ $job->company->name }}</a>
                            @else
                                {{ $job->company?->name ?? $job->employer->name ?? 'Company' }}
                            @endif
                            @if($job->company?->is_verified)
                                <svg class="w-4 h-4 text-blue-500 inline" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        </p>
                        <p class="text-sm text-gray-500">Posted {{ $job->created_at->diffForHumans() }}</p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 mb-6">
                    @if($job->location)
                        <span class="flex items-center gap-1 text-sm text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            </svg>
                            {{ $job->location }}
                        </span>
                    @endif
                    @if($job->work_preference)
                        <span class="flex items-center gap-1 text-sm text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                            </svg>
                            {{ ucfirst($job->work_preference) }}
                        </span>
                    @endif
                    @if($job->employment_type)
                        <span class="flex items-center gap-1 text-sm text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ ucfirst($job->employment_type) }}
                        </span>
                    @endif
                    @if($job->minimum_experience)
                        <span class="flex items-center gap-1 text-sm text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $job->minimum_experience }}+ years
                        </span>
                    @endif
                </div>

                <div class="flex flex-wrap gap-2 mb-6">
                    @if($job->role)
                        <x-badge variant="indigo">{{ $job->role }}</x-badge>
                    @endif
                    @if($job->salaryLabel())
                        <x-badge variant="success">{{ $job->salaryLabel() }}</x-badge>
                    @endif
                    @if($job->industry)
                        <x-badge>{{ $job->industry }}</x-badge>
                    @endif
                    @if($job->status === 'open')
                        <x-badge variant="success">Open</x-badge>
                    @elseif($job->status === 'closed')
                        <x-badge variant="danger">Closed</x-badge>
                    @endif
                </div>

                @php
                    $overview = array_filter([
                        'Role' => $job->role,
                        'Industry' => $job->industry,
                        'Employment Type' => $job->employment_type ? str_replace('_', ' ', ucfirst($job->employment_type)) : null,
                        'Experience Level' => filled($job->experience_level) && $job->experience_level !== 'any' ? ucfirst($job->experience_level) : null,
                        'Minimum Experience' => filled($job->minimum_experience) ? $job->minimum_experience.'+ year'.($job->minimum_experience == 1 ? '' : 's') : null,
                        'Work Mode' => $job->work_preference ? ucfirst($job->work_preference) : null,
                        'Location' => collect([$job->location, $job->location_country])->filter()->implode(', ') ?: null,
                        'Salary' => $job->salaryLabel(),
                        'Temperament Fit' => $job->temperament_preference ? ucfirst($job->temperament_preference) : null,
                        'Status' => ucfirst($job->status),
                        'Posted' => $job->created_at->format('M d, Y'),
                    ], fn ($value) => filled($value));
                @endphp

                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Job Overview</h2>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4 bg-slate-50 border border-slate-100 rounded-xl p-5">
                        @foreach($overview as $label => $value)
                            <div>
                                <dt class="text-xs uppercase tracking-wide text-slate-500">{{ $label }}</dt>
                                <dd class="mt-0.5 text-sm font-medium text-gray-900 break-words">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>

                @if($job->description)
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">Job Description</h2>
                        <div class="prose prose-sm max-w-none text-gray-700">
                            {!! nl2br(e($job->description)) !!}
                        </div>
                    </div>
                @endif

                @if($job->responsibilities)
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">Responsibilities</h2>
                        <div class="prose prose-sm max-w-none text-gray-700">
                            {!! nl2br(e($job->responsibilities)) !!}
                        </div>
                    </div>
                @endif

                @if($job->requirements)
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">Requirements</h2>
                        <div class="prose prose-sm max-w-none text-gray-700">
                            {!! nl2br(e($job->requirements)) !!}
                        </div>
                    </div>
                @endif

                @if($job->benefits)
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">Benefits</h2>
                        <div class="prose prose-sm max-w-none text-gray-700">
                            {!! nl2br(e($job->benefits)) !!}
                        </div>
                    </div>
                @endif

                @if($job->required_skills_json && count($job->required_skills_json) > 0)
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">Required Skills</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($job->required_skills_json as $skill)
                                <span class="px-3 py-1 bg-slate-100 rounded-full text-sm text-slate-700">
                                    {{ $skill }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(is_array($job->personality_preferences_json) && count($job->personality_preferences_json) > 0)
                    <div class="mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">Candidate Preferences</h2>
                        <div class="flex flex-wrap gap-2">
                            @foreach($job->personality_preferences_json as $preference)
                                <span class="px-3 py-1 bg-purple-50 rounded-full text-sm text-purple-700">
                                    {{ is_array($preference) ? implode(': ', $preference) : $preference }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="pt-6 border-t border-gray-200">
                    @auth
                        @if(auth()->user()->isCandidate())
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                <form method="POST" action="{{ route('candidate.jobs.apply', ['job' => $job->id]) }}">
                                    @csrf
                                    <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                                        Apply Now
                                    </button>
                                </form>
                                @if($canMessage)
                                    <button type="button" x-data="{ sending: false }"
                                        @click="
                                            if (sending) return;
                                            sending = true;
                                            fetch('{{ route('candidate.messages.create', ['employer' => $job->employer_id]) }}', {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                },
                                                body: JSON.stringify({}),
                                            })
                                            .then(async (res) => {
                                                const data = await res.json();
                                                if (res.ok && data.conversation_id) {
                                                    window.location.href = '{{ url('messages') }}/' + data.conversation_id;
                                                } else {
                                                    alert(data.error || 'Unable to start conversation.');
                                                    sending = false;
                                                }
                                            })
                                            .catch(() => { alert('Something went wrong.'); sending = false; });
                                        "
                                        :disabled="sending" class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-3 border border-brand-primary text-brand-primary rounded-lg hover:bg-brand-primary/5 transition-colors font-medium disabled:opacity-60">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        Message Employer
                                    </button>
                                @endif
                            </div>
                        @else
                            <p class="text-sm text-gray-500">Log in as a candidate to apply for this position.</p>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="inline-block px-8 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-medium">
                            Sign in to Apply
                        </a>
                        <p class="mt-2 text-sm text-gray-500">Don't have an account? <a href="{{ route('register') }}" class="text-brand-primary hover:underline">Register here</a></p>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

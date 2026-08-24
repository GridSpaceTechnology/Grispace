@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ url()->previous() ?: route('jobs.index') }}" class="text-brand-primary hover:text-brand-primary-hover flex items-center gap-2 text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                {{-- Company header --}}
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
                    <div class="flex flex-col sm:flex-row items-start gap-5">
                        <x-company-logo :company="$company" size="xl"/>
                        <div class="flex-1 min-w-0 w-full">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 break-words">{{ $company->name }}</h1>
                                @if($company->is_verified)
                                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>

                            @if($company->tagline)
                                <p class="text-lg text-gray-600 mt-1">{{ $company->tagline }}</p>
                            @endif

                            <dl class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm text-gray-600">
                                @if($company->industry)
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        {{ $company->industry }}
                                    </div>
                                @endif
                                @if($company->company_size)
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                        {{ $company->company_size }} employees
                                    </div>
                                @endif
                                @if($company->location)
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        {{ $company->location }}@if($company->location_country), {{ $company->location_country }}@endif
                                    </div>
                                @endif
                                @if($company->work_model)
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        {{ ucfirst($company->work_model) }}
                                    </div>
                                @endif
                                @if($company->founded_year)
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        Founded {{ $company->founded_year }}
                                    </div>
                                @endif
                                @if($company->website)
                                    <div class="flex items-center gap-2 min-w-0">
                                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                        <a href="{{ $company->website }}" target="_blank" rel="noopener noreferrer" class="text-brand-primary hover:underline truncate">{{ $company->website }}</a>
                                    </div>
                                @endif
                            </dl>

                            @if($canMessage)
                                <div class="mt-5" x-data="{ sending: false }">
                                    <button
                                        type="button"
                                        @click="
                                            if (sending) return;
                                            sending = true;
                                            fetch('{{ route('candidate.messages.create', ['employer' => $company->user_id]) }}', {
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
                                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-primary text-white text-sm font-medium rounded-lg hover:bg-brand-primary-hover transition-colors disabled:opacity-60"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        Message Employer
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- About --}}
                @if($company->description)
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">About {{ $company->name }}</h2>
                        <div class="prose prose-sm max-w-none text-gray-700">{!! nl2br(e($company->description)) !!}</div>
                    </div>
                @endif

                {{-- Culture --}}
                @if($company->culture_description || $company->culture_values_json)
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">Culture & Values</h2>
                        @if($company->culture_description)
                            <div class="prose prose-sm max-w-none text-gray-700 mb-4">{!! nl2br(e($company->culture_description)) !!}</div>
                        @endif
                        @if($company->culture_values_json && count($company->culture_values_json))
                            <div class="flex flex-wrap gap-2">
                                @foreach($company->culture_values_json as $value)
                                    <x-badge variant="brand">{{ is_array($value) ? ($value['name'] ?? $value['value'] ?? '') : $value }}</x-badge>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Benefits --}}
                @if($company->benefits_json && count($company->benefits_json))
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8">
                        <h2 class="text-lg font-semibold text-gray-900 mb-3">Benefits</h2>
                        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2">
                            @foreach($company->benefits_json as $benefit)
                                <li class="flex items-start gap-2 text-sm text-gray-700">
                                    <svg class="w-4 h-4 text-emerald-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    {{ is_array($benefit) ? ($benefit['name'] ?? $benefit['title'] ?? '') : $benefit }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            {{-- Sidebar: open jobs --}}
            <div class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-base font-semibold text-gray-900 mb-4">Open Positions ({{ $jobs->count() }})</h2>
                    @if($jobs->isEmpty())
                        <p class="text-sm text-gray-500">No open positions right now. Check back soon.</p>
                    @else
                        <ul class="divide-y divide-slate-100 -mx-2">
                            @foreach($jobs as $job)
                                <li>
                                    <a href="{{ route('jobs.show', $job) }}" class="block px-2 py-3 hover:bg-slate-50 rounded-lg transition-colors">
                                        <p class="font-medium text-gray-900 text-sm line-clamp-1">{{ $job->title }}</p>
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-1 text-xs text-gray-500">
                                            @if($job->salaryLabel())
                                                <span class="text-emerald-700 font-medium">{{ $job->salaryLabel() }}</span>
                                                <span>·</span>
                                            @endif
                                            <span>{{ ucfirst(str_replace('_', '-', $job->employment_type)) }}</span>
                                            @if($job->location)
                                                <span>·</span>
                                                <span class="truncate">{{ $job->location }}</span>
                                            @endif
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

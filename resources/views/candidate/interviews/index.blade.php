@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-slate-900">My Interviews</h1>
            <p class="text-slate-600 mt-1">View your scheduled and past interviews</p>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-100 border border-emerald-400 text-emerald-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        @php
            $statusConfig = [
                'scheduled' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Scheduled'],
                'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'Completed'],
                'cancelled' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'Cancelled'],
            ];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($interviews as $interview)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-slate-900">{{ $interview->job->title }}</h3>
                            <p class="text-sm text-slate-500">
                                {{ $interview->employer->employerProfile?->company_name ?? $interview->employer->name }}
                            </p>
                        </div>
                        @php
                            $status = $statusConfig[$interview->status] ?? $statusConfig['scheduled'];
                        @endphp
                        <span class="px-2 py-1 {{ $status['bg'] }} {{ $status['text'] }} text-xs font-medium rounded-full">
                            {{ $status['label'] }}
                        </span>
                    </div>

                    <div class="space-y-2 text-sm text-slate-600 mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $interview->scheduled_at->format('F j, Y') }}
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $interview->scheduled_at->format('g:i A') }}
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            {{ ucfirst($interview->interview_type) }}
                        </div>
                    </div>

                    @if($interview->meeting_link && $interview->status === 'scheduled')
                        <a href="{{ $interview->meeting_link }}" target="_blank" 
                            class="block w-full text-center px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary-hover transition-colors mb-2">
                            Join Interview
                        </a>
                    @endif

                    @if($interview->location)
                        <div class="text-xs text-slate-500 mb-2">
                            <span class="font-medium">Location:</span> {{ $interview->location }}
                        </div>
                    @endif

                    @if($interview->notes)
                        <div class="text-xs text-slate-500">
                            <span class="font-medium">Notes:</span> {{ $interview->notes }}
                        </div>
                    @endif
                </div>
            @empty
                <div class="col-span-full">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
                        <svg class="w-12 h-12 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <h3 class="text-lg font-medium text-slate-900 mb-2">No interviews scheduled</h3>
                        <p class="text-slate-500 mb-4">You don't have any scheduled interviews yet</p>
                        <a href="{{ route('candidate.jobs') }}" class="inline-flex px-4 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary-hover transition-colors">
                            Browse Jobs
                        </a>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
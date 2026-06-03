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
@endphp

@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Applicant Tracking System</h1>
                <p class="text-gray-600 mt-1">Manage all applications across your job posts</p>
            </div>
            <div class="mt-4 md:mt-0 flex gap-3">
                <a href="{{ route('employer.ats.analytics') }}" class="px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 transition-colors">
                    View Analytics
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-4 md:grid-cols-7 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 text-center">
                <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Total</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-4 text-center">
                <p class="text-2xl font-bold text-blue-600">{{ $stats['applied'] }}</p>
                <p class="text-xs text-blue-500 mt-1">Applied</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-purple-200 p-4 text-center">
                <p class="text-2xl font-bold text-purple-600">{{ $stats['shortlisted'] }}</p>
                <p class="text-xs text-purple-500 mt-1">Shortlisted</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-yellow-200 p-4 text-center">
                <p class="text-2xl font-bold text-yellow-600">{{ $stats['interview'] }}</p>
                <p class="text-xs text-yellow-500 mt-1">Interview</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-4 text-center">
                <p class="text-2xl font-bold text-orange-600">{{ $stats['offer'] }}</p>
                <p class="text-xs text-orange-500 mt-1">Offer</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-green-200 p-4 text-center">
                <p class="text-2xl font-bold text-green-600">{{ $stats['hired'] }}</p>
                <p class="text-xs text-green-500 mt-1">Hired</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-red-200 p-4 text-center">
                <p class="text-2xl font-bold text-red-600">{{ $stats['rejected'] }}</p>
                <p class="text-xs text-red-500 mt-1">Rejected</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="p-6 border-b border-gray-100">
                <form method="GET" action="{{ route('employer.ats.dashboard') }}" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <input type="text" name="search" placeholder="Search by candidate name or email..." value="{{ $filters['search'] ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary focus:border-transparent">
                    </div>
                    <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary">
                        <option value="">All Statuses</option>
                        <option value="applied" @selected(($filters['status'] ?? '') === 'applied')>Applied</option>
                        <option value="shortlisted" @selected(($filters['status'] ?? '') === 'shortlisted')>Shortlisted</option>
                        <option value="interview" @selected(($filters['status'] ?? '') === 'interview')>Interview</option>
                        <option value="offer" @selected(($filters['status'] ?? '') === 'offer')>Offer</option>
                        <option value="hired" @selected(($filters['status'] ?? '') === 'hired')>Hired</option>
                        <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Rejected</option>
                    </select>
                    <select name="job_id" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary">
                        <option value="">All Jobs</option>
                        @foreach($jobs as $job)
                            <option value="{{ $job->id }}" @selected(($filters['job_id'] ?? '') == $job->id)>{{ $job->title }}</option>
                        @endforeach
                    </select>
                    <select name="min_score" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-brand-primary">
                        <option value="">Min Match Score</option>
                        <option value="80" @selected(($filters['min_score'] ?? '') == 80)>80%+</option>
                        <option value="60" @selected(($filters['min_score'] ?? '') == 60)>60%+</option>
                        <option value="40" @selected(($filters['min_score'] ?? '') == 40)>40%+</option>
                    </select>
                    <button type="submit" class="px-6 py-2 bg-brand-primary text-white rounded-lg hover:bg-brand-primary-hover transition-colors">Filter</button>
                    <a href="{{ route('employer.ats.dashboard') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">Clear</a>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <th class="px-6 py-3">Candidate</th>
                            <th class="px-6 py-3">Job</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Match Score</th>
                            <th class="px-6 py-3">Applied</th>
                            <th class="px-6 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($applications as $application)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <a href="{{ route('employer.ats.show', $application) }}" class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-brand-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-brand-primary font-medium text-sm">{{ substr($application->candidate->name, 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900 text-sm">{{ $application->candidate->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $application->candidate->email }}</p>
                                        </div>
                                    </a>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-700">{{ $application->job->title }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $stageColors[$application->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($application->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @if($application->match_score)
                                        <div class="flex items-center gap-2">
                                            <div class="w-16 bg-gray-200 rounded-full h-2">
                                                <div class="h-2 rounded-full @if($application->match_score >= 80) bg-green-500 @elseif($application->match_score >= 60) bg-yellow-500 @else bg-red-500 @endif" style="width: {{ $application->match_score }}%"></div>
                                            </div>
                                            <span class="text-xs font-medium @if($application->match_score >= 80) text-green-600 @elseif($application->match_score >= 60) text-yellow-600 @else text-red-600 @endif">{{ $application->match_score }}%</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $application->applied_at?->diffForHumans() ?? $application->created_at->diffForHumans() }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('employer.ats.show', $application) }}" class="px-3 py-1.5 text-xs font-medium text-brand-primary bg-brand-primary/10 rounded hover:bg-brand-primary/20 transition-colors">
                                            View
                                        </a>
                                        @if($application->status === 'interview')
                                            <a href="{{ route('employer.applications.schedule-interview', $application) }}" class="px-3 py-1.5 text-xs font-medium text-emerald-600 bg-emerald-100 rounded hover:bg-emerald-200 transition-colors">
                                                Schedule
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p>No applications found matching your filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($applications->hasPages())
                <div class="px-6 py-4 border-t border-gray-100">
                    {{ $applications->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

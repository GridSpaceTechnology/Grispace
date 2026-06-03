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

        <h1 class="text-3xl font-bold text-gray-900 mb-8">Hiring Analytics</h1>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Total Applications</p>
                <p class="text-3xl font-bold text-gray-900">{{ $totalApplications }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Average Match Score</p>
                <p class="text-3xl font-bold text-gray-900">{{ $avgMatchScore }}%</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Conversion Rate</p>
                <p class="text-3xl font-bold text-gray-900">{{ $conversionRate }}%</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <p class="text-sm text-gray-500">Hired</p>
                <p class="text-3xl font-bold text-green-600">{{ $stageCounts['hired'] ?? 0 }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Applications by Stage</h2>
                <div class="space-y-3">
                    @foreach(['applied', 'shortlisted', 'interview', 'offer', 'hired', 'rejected'] as $stage)
                        @php
                            $colors = [
                                'applied' => 'bg-blue-500',
                                'shortlisted' => 'bg-purple-500',
                                'interview' => 'bg-yellow-500',
                                'offer' => 'bg-orange-500',
                                'hired' => 'bg-green-500',
                                'rejected' => 'bg-red-500',
                            ];
                            $maxCount = max($stageCounts->max(), 1);
                            $width = $maxCount > 0 ? ($stageCounts[$stage] / $maxCount) * 100 : 0;
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700 capitalize">{{ $stage }}</span>
                                <span class="text-gray-500">{{ $stageCounts[$stage] ?? 0 }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                <div class="h-2.5 rounded-full {{ $colors[$stage] }}" style="width: {{ $width }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Applications by Job</h2>
                @if($applicationsByJob->isNotEmpty())
                    <div class="space-y-3">
                        @foreach($applicationsByJob as $job)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">{{ $job->title }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    {{ $job->applications_count }} applications
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-400 text-sm">No jobs posted yet.</p>
                @endif
            </div>
        </div>

        @if($monthlyTrend->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Monthly Trend</h2>
                <div class="space-y-3">
                    @php $maxMonthly = $monthlyTrend->max('total'); @endphp
                    @foreach($monthlyTrend as $trend)
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-gray-500 w-20">{{ $trend->month }}</span>
                            <div class="flex-1 bg-gray-200 rounded-full h-4">
                                <div class="h-4 rounded-full bg-brand-primary" style="width: {{ $maxMonthly > 0 ? ($trend->total / $maxMonthly) * 100 : 0 }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-700 w-10 text-right">{{ $trend->total }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

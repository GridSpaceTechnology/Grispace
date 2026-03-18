@extends('layouts.admin')

@section('admin-content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Analytics</h1>
    <p class="text-gray-600 mt-1">Platform insights and statistics</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500">Total Users</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalUsers }}</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500">Active Jobs</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $activeJobs }}</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500">Total Applications</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $totalApplications }}</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500">Onboarding Rate</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">
            @if($totalUsers > 0)
                {{ round(($completedOnboardings / $totalUsers) * 100) }}%
            @else
                0%
            @endif
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Recent Users</h2>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse($recentUsers as $user)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                <span class="text-indigo-600 font-medium">{{ substr($user->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                            @if($user->role === 'admin') bg-purple-100 text-purple-800
                            @elseif($user->role === 'employer') bg-blue-100 text-blue-800
                            @else bg-green-100 text-green-800
                            @endif">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No users yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Recent Jobs</h2>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                @forelse($recentJobs as $job)
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">{{ $job->title }}</p>
                            <p class="text-sm text-gray-500">{{ $job->company?->name ?? 'No company' }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                            @if($job->status === 'open') bg-green-100 text-green-800
                            @elseif($job->status === 'closed') bg-red-100 text-red-800
                            @else bg-gray-100 text-gray-800
                            @endif">
                            {{ ucfirst($job->status) }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">No jobs yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-lg font-semibold text-gray-900">Recent Applications</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Candidate</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Match Score</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($recentApplications as $application)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $application->user->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $application->job->title ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                @if($application->status === 'applied') bg-blue-100 text-blue-800
                                @elseif($application->status === 'shortlisted') bg-purple-100 text-purple-800
                                @elseif($application->status === 'interview') bg-yellow-100 text-yellow-800
                                @elseif($application->status === 'offer') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($application->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $application->match_score_snapshot ?? 'N/A' }}%</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $application->created_at->format('M d, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">No applications yet</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

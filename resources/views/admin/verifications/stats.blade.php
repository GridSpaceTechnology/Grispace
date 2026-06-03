@extends('layouts.admin')

@section('admin-content')
<div class="mb-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Verification Analytics</h1>
            <p class="text-gray-600 mt-1">Overview of verification system performance</p>
        </div>
        <a href="{{ route('admin.verifications.index') }}" class="text-brand-primary hover:underline">Back to Verifications</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <p class="text-3xl font-bold text-gray-900">{{ $total }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Requests</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <p class="text-3xl font-bold text-green-600">{{ $approvalRate }}%</p>
            <p class="text-sm text-gray-500 mt-1">Approval Rate</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <p class="text-3xl font-bold text-blue-600">{{ $completionRate }}%</p>
            <p class="text-sm text-gray-500 mt-1">Completion Rate</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <p class="text-3xl font-bold text-brand-primary">{{ number_format($averageScore, 1) }}</p>
            <p class="text-sm text-gray-500 mt-1">Avg Trust Score</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Status Breakdown</h2>
            <div class="space-y-4">
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-gray-600">Approved</span>
                        <span class="font-medium">{{ $approved }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $total > 0 ? ($approved / $total) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-gray-600">Pending</span>
                        <span class="font-medium">{{ $pending }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $total > 0 ? ($pending / $total) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-gray-600">Under Review</span>
                        <span class="font-medium">{{ $underReview }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $total > 0 ? ($underReview / $total) * 100 : 0 }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <span class="text-gray-600">Rejected</span>
                        <span class="font-medium">{{ $rejected }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-red-500 h-2 rounded-full" style="width: {{ $total > 0 ? ($rejected / $total) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Most Common Verification Types</h2>
            @if($mostCommonType)
                <div class="text-center mb-4">
                    <p class="text-4xl font-bold text-brand-primary">{{ $mostCommonType->count }}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ $mostCommonType->verificationType?->name ?? 'Unknown' }}</p>
                </div>
            @endif
            <div class="space-y-3">
                @foreach($verificationTypeCounts->sortByDesc('count') as $typeCount)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-700">{{ $typeCount->verificationType?->name ?? 'Unknown' }}</span>
                        <span class="font-medium">{{ $typeCount->count }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

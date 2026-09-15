@extends('layouts.admin')

@section('admin-content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Matching Analytics</h1>
    <p class="text-gray-600 mt-1">Does a high match score predict a real outcome? Read-only, aggregate-only report used for human-controlled calibration.</p>
</div>

@if(!$sampleSatisfied)
    <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-xl p-4 mb-8 text-sm">
        Not enough data yet. Conversion metrics are hidden until at least {{ $minSample }} outcome events are recorded.
    </div>
@endif

<div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500">Recommended</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $funnel['recommended'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500">Viewed</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $funnel['viewed'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500">Applied</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $funnel['applied'] }}</p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500">Hired</p>
        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $funnel['hired'] }}</p>
    </div>
</div>

@if($sampleSatisfied)
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-500">Recommendation → View</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $funnel['view_rate'] ?? '–' }}%</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-500">View → Apply</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $funnel['apply_rate'] ?? '–' }}%</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-500">Apply → Interview</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $funnel['interview_rate'] ?? '–' }}%</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <p class="text-sm text-gray-500">Interview → Hire</p>
            <p class="text-3xl font-bold text-gray-900 mt-1">{{ $funnel['hire_rate'] ?? '–' }}%</p>
        </div>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Outcome by Match Band</h2>
            @if(!$sampleSatisfied)
                <span class="text-xs text-amber-600">hidden below minimum sample</span>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="px-6 py-3">Band</th>
                        <th class="px-6 py-3">Exposed</th>
                        <th class="px-6 py-3">CTR</th>
                        <th class="px-6 py-3">Apply rate</th>
                        <th class="px-6 py-3">Interview rate</th>
                        <th class="px-6 py-3">Hire rate</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bands as $row)
                        <tr class="border-b border-gray-50 {{ !$sampleSatisfied ? 'opacity-50' : '' }}">
                            <td class="px-6 py-3 font-medium">{{ $row['band'] }}</td>
                            <td class="px-6 py-3">{{ $row['exposed'] }}</td>
                            <td class="px-6 py-3">{{ $row['ctr'] ?? '–' }}%</td>
                            <td class="px-6 py-3">{{ $row['apply_rate'] ?? '–' }}%</td>
                            <td class="px-6 py-3">{{ $row['interview_rate'] ?? '–' }}%</td>
                            <td class="px-6 py-3">{{ $row['hire_rate'] ?? '–' }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Ranking Quality (Precision@K / Recall@K)</h2>
            <p class="text-xs text-gray-500 mt-1">Of the top-{{ $precision['k'] }} exposed jobs, how often did candidates actually apply or click?</p>
        </div>
        <div class="px-6 py-4 space-y-4">
            @foreach([$viewPrecision, $precision] as $metric)
                <div>
                    <div class="flex justify-between items-center">
                        <p class="text-sm font-medium text-gray-700">Top-{{ $metric['k'] }} {{ ucfirst($metric['criterion']) }}</p>
                        <p class="text-sm text-gray-500">{{ $metric['candidates'] }} candidates</p>
                    </div>
                    <div class="flex gap-6 mt-2">
                        <div class="flex-1">
                            <p class="text-2xl font-bold text-gray-900">{{ $metric['precision_at_k'] !== null ? round($metric['precision_at_k'] * 100, 1).'%' : '–' }}</p>
                            <p class="text-xs text-gray-500">Precision@K</p>
                        </div>
                        <div class="flex-1">
                            <p class="text-2xl font-bold text-gray-900">{{ $metric['recall_at_k'] !== null ? round($metric['recall_at_k'] * 100, 1).'%' : '–' }}</p>
                            <p class="text-xs text-gray-500">Recall@K</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Feedback Signals</h2>
            <p class="text-xs text-gray-500 mt-1">Explicit candidate + employer feedback used for calibration.</p>
        </div>
        <div class="px-6 py-4 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Candidates</p>
                <ul class="space-y-1">
                    @forelse($feedback['candidate'] as $type => $count)
                        <li class="flex justify-between text-sm"><span class="capitalize text-gray-700">{{ str_replace('_', ' ', $type) }}</span><span class="font-semibold">{{ $count }}</span></li>
                    @empty
                        <li class="text-sm text-gray-400">No candidate feedback yet.</li>
                    @endforelse
                </ul>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Employers</p>
                <ul class="space-y-1">
                    @forelse($feedback['employer'] as $type => $count)
                        <li class="flex justify-between text-sm"><span class="capitalize text-gray-700">{{ str_replace('_', ' ', $type) }}</span><span class="font-semibold">{{ $count }}</span></li>
                    @empty
                        <li class="text-sm text-gray-400">No employer feedback yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-semibold text-gray-900">Algorithm Versions & Experiments</h2>
            <p class="text-xs text-gray-500 mt-1">Live routing: {{ $experiments ? 'ENABLED' : 'disabled (recording only)' }}</p>
        </div>
        <div class="px-6 py-4">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100">
                        <th class="py-2">Version</th>
                        <th class="py-2">Label</th>
                        <th class="py-2">Snapshots</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($versions as $version)
                        <tr class="border-b border-gray-50">
                            <td class="py-2">v{{ $version['version'] }}</td>
                            <td class="py-2">{{ $version['label'] }}</td>
                            <td class="py-2">{{ $version['snapshots'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-2 text-gray-400">No snapshots recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-200">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-lg font-semibold text-gray-900">Engine Configuration (read-only)</h2>
        <p class="text-xs text-gray-500 mt-1">Calibration happens here, deliberately, after reviewing the evidence above. Never in controllers.</p>
    </div>
    <div class="px-6 py-4 grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach($weights as $key => $weight)
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', $key) }}</p>
                <p class="text-lg font-semibold text-gray-900">{{ $weight }}%</p>
            </div>
        @endforeach
        @foreach($thresholds as $key => $threshold)
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-xs text-gray-500 capitalize">{{ $key }}</p>
                <p class="text-lg font-semibold text-gray-900">{{ $threshold }}+</p>
            </div>
        @endforeach
    </div>
</div>
@endsection
@props([
    'score' => 0,
    'level' => 'Beginner',
])

@php
$levelSteps = [
    ['label' => 'Beginner', 'min' => 0],
    ['label' => 'Trusted', 'min' => 26],
    ['label' => 'Highly Trusted', 'min' => 51],
    ['label' => 'Verified Professional', 'min' => 76],
];

$currentStepIndex = 0;
foreach ($levelSteps as $i => $step) {
    if ($score >= $step['min']) {
        $currentStepIndex = $i;
    }
}
@endphp

<div class="space-y-2">
    <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-gray-700">Verification Progress</span>
        <span class="text-sm font-semibold text-brand-primary">{{ $score }}/100</span>
    </div>
    <div class="w-full bg-gray-200 rounded-full h-2.5 overflow-hidden">
        <div class="h-full rounded-full transition-all duration-1000 ease-out
            @if($score >= 76) bg-green-500
            @elseif($score >= 51) bg-blue-500
            @elseif($score >= 26) bg-amber-500
            @else bg-gray-400
            @endif"
            style="width: {{ $score }}%">
        </div>
    </div>
    <div class="flex justify-between items-center pt-1">
        @foreach($levelSteps as $i => $step)
            <div class="flex flex-col items-center {{ $i <= $currentStepIndex ? 'text-brand-primary' : 'text-gray-400' }}">
                <div class="w-2 h-2 rounded-full {{ $i <= $currentStepIndex ? 'bg-brand-primary' : 'bg-gray-300' }} mb-1"></div>
                <span class="text-[10px] leading-tight text-center">{{ $step['label'] }}</span>
            </div>
        @endforeach
    </div>
</div>

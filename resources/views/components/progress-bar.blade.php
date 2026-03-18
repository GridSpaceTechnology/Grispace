@props([
    'value' => 0,
    'max' => 100,
    'size' => 'sm',
    'showLabel' => false,
    'color' => 'indigo',
])

@php
    $percentage = min(100, max(0, ($value / $max) * 100));
    
    $sizes = [
        'xs' => 'h-1',
        'sm' => 'h-2',
        'md' => 'h-3',
        'lg' => 'h-4',
    ];

    $colors = [
        'indigo' => 'bg-indigo-600',
        'green' => 'bg-green-600',
        'blue' => 'bg-blue-600',
        'yellow' => 'bg-yellow-500',
        'red' => 'bg-red-600',
        'purple' => 'bg-purple-600',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['sm'];
    $colorClass = $colors[$color] ?? $colors['indigo'];
@endphp

<div class="w-full">
    @if($showLabel)
        <div class="flex justify-between items-center mb-1">
            <span class="text-sm font-medium text-gray-700">{{ $slot ?: 'Progress' }}</span>
            <span class="text-sm font-medium text-gray-700">{{ round($percentage) }}%</span>
        </div>
    @endif
    <div class="w-full bg-gray-200 rounded-full overflow-hidden {{ $sizeClass }}">
        <div class="{{ $colorClass }} {{ $sizeClass }} rounded-full transition-all duration-300 ease-out" style="width: {{ $percentage }}%"></div>
    </div>
</div>

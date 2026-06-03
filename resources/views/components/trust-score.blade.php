@props([
    'score' => 0,
    'level' => 'Beginner',
    'size' => 'md',
    'showLabel' => true,
])

@php
$sizes = [
    'sm' => ['w-16 h-16', 'text-lg', 'text-xs'],
    'md' => ['w-24 h-24', 'text-2xl', 'text-sm'],
    'lg' => ['w-32 h-32', 'text-3xl', 'text-base'],
];

$levelColors = [
    'Verified Professional' => ['stroke' => '#059669', 'bg' => 'bg-green-50', 'text' => 'text-green-700'],
    'Highly Trusted' => ['stroke' => '#2563eb', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700'],
    'Trusted' => ['stroke' => '#d97706', 'bg' => 'bg-amber-50', 'text' => 'text-amber-700'],
    'Beginner' => ['stroke' => '#6b7280', 'bg' => 'bg-gray-50', 'text' => 'text-gray-600'],
];

$color = $levelColors[$level] ?? $levelColors['Beginner'];
$sizeClasses = $sizes[$size];
$circumference = 2 * pi() * 38;
$offset = $circumference - ($score / 100) * $circumference;
@endphp

<div class="flex flex-col items-center gap-1">
    <div class="relative {{ $sizeClasses[0] }}">
        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="38" fill="none" stroke="#e5e7eb" stroke-width="6"/>
            <circle cx="50" cy="50" r="38" fill="none" stroke="{{ $color['stroke'] }}" stroke-width="6"
                    stroke-dasharray="{{ $circumference }}"
                    stroke-dashoffset="{{ $offset }}"
                    stroke-linecap="round"
                    class="transition-all duration-1000 ease-out"/>
        </svg>
        <div class="absolute inset-0 flex items-center justify-center">
            <span class="font-bold {{ $sizeClasses[1] }} {{ $color['text'] }}">{{ $score }}</span>
        </div>
    </div>
    @if($showLabel)
        <span class="font-medium {{ $sizeClasses[2] }} {{ $color['text'] }}">{{ $level }}</span>
    @endif
</div>

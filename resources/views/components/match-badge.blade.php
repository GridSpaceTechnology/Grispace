@props(['score', 'category' => null, 'size' => 'md'])

@php
    $sizes = [
        'sm' => 'w-14 h-14 text-xs',
        'md' => 'w-16 h-16 text-sm',
        'lg' => 'w-20 h-20 text-base',
    ];
@endphp

<div class="rounded-full {{ $sizes[$size] ?? $sizes['md'] }} bg-[#052E5C] text-white flex flex-col items-center justify-center shadow-md shrink-0">
    <span class="font-bold leading-none">{{ $score }}%</span>
    @if($category)
        <span class="text-[9px] leading-tight mt-0.5 opacity-90 px-1 text-center">{{ $category }}</span>
    @endif
</div>

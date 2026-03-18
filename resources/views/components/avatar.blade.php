@props([
    'name' => null,
    'src' => null,
    'size' => 'md',
    'initials' => null,
])

@php
    $sizes = [
        'xs' => 'w-6 h-6 text-xs',
        'sm' => 'w-8 h-8 text-sm',
        'md' => 'w-10 h-10 text-sm',
        'lg' => 'w-12 h-12 text-base',
        'xl' => 'w-16 h-16 text-lg',
    ];

    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

@if($src)
    <img 
        src="{{ $src }}" 
        alt="{{ $name }}" 
        class="rounded-full object-cover {{ $sizeClass }}"
    />
@else
    <div class="rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-medium {{ $sizeClass }}">
        {{ $initials ?? ($name ? substr($name, 0, 2) : '?') }}
    </div>
@endif

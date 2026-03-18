@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
])

@php
    $variants = [
        'primary' => 'bg-brand-primary hover:bg-brand-primary-hover text-white focus:ring-brand-primary',
        'secondary' => 'bg-brand-secondary text-white hover:bg-brand-secondary/90 focus:ring-brand-secondary',
        'success' => 'bg-emerald-500 text-white hover:bg-emerald-600 focus:ring-emerald-500',
        'danger' => 'bg-red-500 text-white hover:bg-red-600 focus:ring-red-500',
        'ghost' => 'bg-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-100',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base',
    ];

    $variantClass = $variants[$variant] ?? $variants['primary'];
    $sizeClass = $sizes[$size] ?? $sizes['md'];
@endphp

@if($href)
    <a href="{{ $href }}" class="inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 {{ $variantClass }} {{ $sizeClass }}">
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => "inline-flex items-center justify-center font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 {$variantClass} {$sizeClass}"]) }}>
        {{ $slot }}
    </button>
@endif

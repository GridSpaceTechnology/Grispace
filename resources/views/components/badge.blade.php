@props([
    'variant' => 'default',
    'size' => 'sm',
])

@php
    $variants = [
        'default' => 'bg-slate-100 text-slate-700',
        'success' => 'bg-emerald-100 text-emerald-700',
        'warning' => 'bg-amber-100 text-amber-700',
        'danger' => 'bg-red-100 text-red-700',
        'info' => 'bg-blue-100 text-blue-700',
        'purple' => 'bg-purple-100 text-purple-700',
        'indigo' => 'bg-indigo-100 text-indigo-700',
        'orange' => 'bg-orange-100 text-orange-700',
        'brand' => 'bg-brand-primary/10 text-brand-primary',
    ];

    $sizes = [
        'xs' => 'px-2 py-0.5 text-xs',
        'sm' => 'px-2.5 py-1 text-xs',
        'md' => 'px-3 py-1.5 text-sm',
    ];

    $variantClass = $variants[$variant] ?? $variants['default'];
    $sizeClass = $sizes[$size] ?? $sizes['sm'];
@endphp

<span class="inline-flex items-center font-medium rounded-full {{ $variantClass }} {{ $sizeClass }}">
    {{ $slot }}
</span>

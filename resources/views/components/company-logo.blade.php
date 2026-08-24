@props([
    'company' => null,
    'fallback' => 'Company',
    'size' => 'md',
])

@php
    $sizes = [
        'sm' => ['box' => 'w-9 h-9 rounded-lg', 'text' => 'text-sm'],
        'md' => ['box' => 'w-12 h-12 rounded-xl', 'text' => 'text-lg'],
        'lg' => ['box' => 'w-20 h-20 rounded-2xl', 'text' => 'text-3xl'],
        'xl' => ['box' => 'w-24 h-24 rounded-2xl', 'text' => 'text-4xl'],
    ];
    $classes = $sizes[$size] ?? $sizes['md'];
@endphp

@if ($company?->logo_url)
    <img src="{{ Storage::url($company->logo_url) }}" alt="{{ $company->name }}" class="{{ $classes['box'] }} object-cover flex-shrink-0 bg-white border border-slate-200">
@else
    <div class="{{ $classes['box'] }} bg-brand-secondary/10 flex items-center justify-center flex-shrink-0">
        <span class="{{ $classes['text'] }} font-semibold text-brand-secondary">{{ mb_strtoupper(mb_substr($company?->name ?? $fallback, 0, 1)) }}</span>
    </div>
@endif

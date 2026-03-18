@props([
    'title' => '',
    'value' => '',
    'icon' => null,
    'trend' => null,
    'trendLabel' => null,
    'color' => 'indigo',
])

@php
    $colors = [
        'brand' => ['bg' => 'bg-brand-primary/10', 'text' => 'text-brand-primary', 'icon' => 'text-brand-primary'],
        'blue' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'icon' => 'text-blue-600'],
        'green' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'icon' => 'text-emerald-600'],
        'purple' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'icon' => 'text-purple-600'],
        'orange' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-600', 'icon' => 'text-orange-600'],
    ];
    
    $colorClasses = $colors[$color] ?? $colors['brand'];
@endphp

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            @if($icon)
                <div class="w-12 h-12 {{ $colorClasses['bg'] }} rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 {{ $colorClasses['icon'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                    </svg>
                </div>
            @endif
            <div>
                <p class="text-sm text-slate-500">{{ $title }}</p>
                <p class="text-2xl font-bold text-slate-900">{{ $value }}</p>
            </div>
        </div>
        @if($trend)
            <div class="text-right">
                <span class="text-sm font-medium {{ $trend >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $trend >= 0 ? '+' : '' }}{{ $trend }}%
                </span>
                @if($trendLabel)
                    <p class="text-xs text-slate-500">{{ $trendLabel }}</p>
                @endif
            </div>
        @endif
    </div>
</div>

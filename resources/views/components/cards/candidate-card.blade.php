@props([
    'name' => '',
    'role' => '',
    'image' => null,
    'skills' => [],
    'badge' => null,
    'badgeVariant' => 'default',
    'matchScore' => null,
    'location' => null,
    'experience' => null,
    'workPreference' => null,
])

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 hover:shadow-md transition-shadow duration-200">
    <div class="flex items-start gap-4 mb-3">
        @if($image)
            <img src="{{ $image }}" alt="{{ $name }}" class="w-12 h-12 rounded-full object-cover flex-shrink-0">
        @else
            <div class="w-12 h-12 bg-brand-primary/10 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-brand-primary font-semibold text-sm">{{ substr($name, 0, 2) }}</span>
            </div>
        @endif
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between">
                <h4 class="font-semibold text-slate-900 text-sm truncate">{{ $name }}</h4>
                @if($badge)
                    <x-badge :variant="$badgeVariant" size="xs">{{ $badge }}</x-badge>
                @endif
            </div>
            <p class="text-xs text-slate-500 truncate">{{ $role }}</p>
        </div>
        @if($matchScore)
            <div class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs font-medium">
                {{ $matchScore }}%
            </div>
        @endif
    </div>
    
    @if(count($skills) > 0)
        <div class="mb-3">
            <div class="flex flex-wrap gap-1">
                @foreach($skills as $skill)
                    <span class="px-2 py-0.5 bg-slate-100 rounded text-xs text-slate-600">{{ $skill }}</span>
                @endforeach
            </div>
        </div>
    @endif
    
    <div class="flex flex-wrap gap-3 text-xs text-slate-500 mb-3">
        @if($experience)
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $experience }}
            </span>
        @endif
        @if($location)
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                </svg>
                {{ $location }}
            </span>
        @endif
        @if($workPreference)
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/>
                </svg>
                {{ $workPreference }}
            </span>
        @endif
    </div>
    
    {{ $slot }}
</div>

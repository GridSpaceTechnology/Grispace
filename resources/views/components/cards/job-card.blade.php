@props([
    'title' => '',
    'company' => '',
    'location' => null,
    'workMode' => null,
    'employmentType' => null,
    'salary' => null,
    'matchScore' => null,
    'skills' => [],
    'postedAt' => null,
])

<div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:shadow-md transition-shadow duration-200">
    <div class="flex items-start justify-between mb-3">
        <div class="flex-1">
            <h3 class="font-semibold text-slate-900">{{ $title }}</h3>
            <p class="text-sm text-slate-500">{{ $company }}</p>
        </div>
        @if($matchScore)
            <div class="text-right">
                <div class="text-lg font-bold text-brand-primary">{{ $matchScore }}%</div>
                <div class="text-xs text-slate-500">match</div>
            </div>
        @endif
    </div>
    
    <div class="flex flex-wrap gap-2 mb-3">
        @if($employmentType)
            <span class="px-2 py-1 bg-brand-primary/10 text-brand-primary rounded text-xs font-medium">
                {{ $employmentType }}
            </span>
        @endif
        @if($workMode)
            <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs">
                {{ $workMode }}
            </span>
        @endif
        @if($location)
            <span class="px-2 py-1 bg-slate-100 text-slate-700 rounded text-xs">
                {{ $location }}
            </span>
        @endif
    </div>
    
    @if(count($skills) > 0)
        <div class="flex flex-wrap gap-1 mb-3">
            @foreach($skills as $skill)
                <span class="px-2 py-0.5 bg-slate-100 rounded text-xs text-slate-600">{{ $skill }}</span>
            @endforeach
        </div>
    @endif
    
    @if($salary)
        <p class="text-sm font-medium text-slate-700 mb-3">{{ $salary }}</p>
    @endif
    
    <div class="flex items-center justify-between">
        @if($postedAt)
            <span class="text-xs text-slate-500">Posted {{ $postedAt }}</span>
        @endif
        {{ $slot }}
    </div>
</div>

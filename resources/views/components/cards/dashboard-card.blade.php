@props([
    'title' => '',
    'padding' => 'p-6',
    'hover' => false,
    'noBorder' => false,
])

<div class="bg-white rounded-xl {{ $noBorder ? '' : 'shadow-sm border border-slate-200' }} {{ $padding }} @if($hover) hover:shadow-md transition-shadow duration-200 @endif">
    @if($title)
        <div class="px-{{ str_replace('p-', '', $padding) }} py-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-slate-900">{{ $title }}</h3>
            @if(isset($actions))
                {{ $actions }}
            @endif
        </div>
    @endif
    {{ $slot }}
</div>

@props([
    'padding' => 'p-6',
    'hover' => false,
])

<div class="bg-white rounded-xl shadow-sm border border-slate-200 {{ $padding }} @if($hover) hover:shadow-md transition-shadow duration-200 @endif">
    {{ $slot }}
</div>

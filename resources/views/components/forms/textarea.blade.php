@props([
    'label' => null,
    'error' => null,
    'helpText' => null,
    'rows' => 4,
])

<div class="space-y-1">
    @if($label)
        <label class="block text-sm font-medium text-slate-700">
            {{ $label }}
        </label>
    @endif
    
    <textarea 
        rows="{{ $rows }}"
        {{ $attributes->merge(['class' => 'w-full rounded-lg border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500']) }}
    >{{ $slot }}</textarea>
    
    @if($error)
        <p class="text-sm text-red-600">{{ $error }}</p>
    @elseif($helpText)
        <p class="text-sm text-slate-500">{{ $helpText }}</p>
    @endif
</div>

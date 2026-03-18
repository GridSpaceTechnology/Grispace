@props([
    'label' => null,
    'error' => null,
    'helpText' => null,
    'type' => 'text',
])

<div class="space-y-1">
    @if($label)
        <label class="block text-sm font-medium text-slate-700">
            {{ $label }}
        </label>
    @endif
    
    <input 
        type="{{ $type }}" 
        {{ $attributes->merge(['class' => 'w-full rounded-lg border-slate-300 shadow-sm focus:border-brand-primary focus:ring-brand-primary']) }}
    />
    
    @if($error)
        <p class="text-sm text-red-600">{{ $error }}</p>
    @elseif($helpText)
        <p class="text-sm text-slate-500">{{ $helpText }}</p>
    @endif
</div>

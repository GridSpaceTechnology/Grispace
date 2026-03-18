@props([
    'label' => null,
    'error' => null,
    'helpText' => null,
    'accept' => null,
    'multiple' => false,
])

<div class="space-y-1">
    @if($label)
        <label class="block text-sm font-medium text-slate-700">
            {{ $label }}
        </label>
    @endif
    
    <div class="relative">
        <input 
            type="file"
            @if($accept) accept="{{ $accept }}" @endif
            @if($multiple) multiple @endif
            {{ $attributes->merge(['class' => 'block w-full text-sm text-slate-500
                file:mr-4 file:py-2 file:px-4
                file:rounded-lg file:border-0
                file:text-sm file:font-medium
                file:bg-indigo-50 file:text-indigo-700
                hover:file:bg-indigo-100
                cursor-pointer
            ']) }}
        />
    </div>
    
    @if($error)
        <p class="text-sm text-red-600">{{ $error }}</p>
    @elseif($helpText)
        <p class="text-sm text-slate-500">{{ $helpText }}</p>
    @endif
</div>

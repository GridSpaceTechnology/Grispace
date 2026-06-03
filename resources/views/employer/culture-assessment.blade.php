@extends('layouts.app')

@section('content')
<div class="py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Define Your Company Culture</h1>
            <p class="text-lg text-slate-600">Help us understand your work environment to find the best talent matches.</p>
        </div>

        <form method="POST" action="{{ route('employer.culture.store') }}" class="space-y-8">
            @csrf

            @foreach($questions as $key => $q)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ $q['question'] }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($q['options'] as $optionKey => $optionLabel)
                            <label class="relative flex items-center p-4 rounded-xl border-2 cursor-pointer transition-all duration-200
                                {{ ($selected[$key] ?? '') === $optionKey ? 'border-primary bg-orange-50' : 'border-slate-200 hover:border-slate-300' }}"
                                style="{{ ($selected[$key] ?? '') === $optionKey ? 'border-color: #EB5233;' : '' }}">
                                <input type="{{ in_array($key, ['preferred_traits', 'motivation_factors']) ? 'checkbox' : 'radio' }}"
                                    name="{{ $key }}{{ in_array($key, ['preferred_traits', 'motivation_factors']) ? '[]' : '' }}"
                                    value="{{ $optionKey }}"
                                    {{ in_array($key, ['preferred_traits', 'motivation_factors']) ? (in_array($optionKey, ($selected[$key] ?? [])) ? 'checked' : '') : (($selected[$key] ?? '') === $optionKey ? 'checked' : '') }}
                                    class="sr-only peer">
                                <span class="text-sm font-medium text-slate-700 peer-checked:text-slate-900">
                                    {{ $optionLabel }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error($key)
                        <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <div class="flex items-center justify-between">
                <a href="{{ route('employer.dashboard') }}" class="text-sm text-slate-500 hover:text-slate-700">
                    Skip for now
                </a>
                <button type="submit" class="px-8 py-3 text-base font-semibold text-white rounded-xl hover:opacity-90 transition-all duration-200 shadow-lg" style="background-color: #EB5233;">
                    Save Culture Profile
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

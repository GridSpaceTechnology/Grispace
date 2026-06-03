@extends('layouts.admin')

@section('admin-content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Create Question</h1>
    <p class="text-gray-600 mt-1">Add a new personality assessment question</p>
</div>

<form method="POST" action="{{ route('admin.personality.questions.store') }}" class="max-w-3xl">
    @csrf

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select name="category" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach($categories as $cat)
                    <option value="{{ $cat }}">{{ str_replace('_', ' ', $cat) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Question Text</label>
            <textarea name="question_text" rows="3" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Question Type</label>
            <select name="question_type" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="single_choice">Single Choice</option>
                <option value="multiple_choice">Multiple Choice</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Display Order</label>
            <input type="number" name="display_order" min="0" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-3">Answer Options</label>
            <div id="options-container" class="space-y-3">
                <div class="option-row flex gap-3 items-start">
                    <div class="flex-1">
                        <input type="text" name="options[0][option_text]" placeholder="Option text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div class="w-40">
                        <input type="text" name="options[0][signal_key]" placeholder="Signal key" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div class="w-24">
                        <input type="number" name="options[0][signal_value]" placeholder="Value" min="1" max="10" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                </div>
                <div class="option-row flex gap-3 items-start">
                    <div class="flex-1">
                        <input type="text" name="options[1][option_text]" placeholder="Option text" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div class="w-40">
                        <input type="text" name="options[1][signal_key]" placeholder="Signal key" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div class="w-24">
                        <input type="number" name="options[1][signal_value]" placeholder="Value" min="1" max="10" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between pt-4 border-t">
            <a href="{{ route('admin.personality.questions.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                Create Question
            </button>
        </div>
    </div>
</form>
@endsection

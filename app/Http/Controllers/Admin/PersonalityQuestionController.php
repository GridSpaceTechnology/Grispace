<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PersonalityQuestion;
use App\Models\PersonalityQuestionOption;
use Illuminate\Http\Request;

class PersonalityQuestionController extends Controller
{
    public function index()
    {
        $questions = PersonalityQuestion::with('options')
            ->ordered()
            ->paginate(20);

        return view('admin.personality.questions.index', compact('questions'));
    }

    public function create()
    {
        $categories = [
            'work_style',
            'communication_style',
            'team_dynamics',
            'problem_solving',
            'leadership_initiative',
            'work_environment_preference',
            'motivation_drivers',
            'temperament_indicators',
            'organizational_culture',
        ];

        return view('admin.personality.questions.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'question_text' => 'required|string',
            'question_type' => 'required|string|max:50',
            'display_order' => 'required|integer|min:0',
            'options' => 'required|array|min:2',
            'options.*.option_text' => 'required|string|max:255',
            'options.*.option_value' => 'nullable|integer|min:1|max:10',
            'options.*.personality_dimension' => 'nullable|string|max:100',
            'options.*.weight' => 'nullable|integer|min:1|max:10',
        ]);

        $question = PersonalityQuestion::create([
            'category' => $validated['category'],
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'display_order' => $validated['display_order'],
        ]);

        foreach ($validated['options'] as $option) {
            $question->options()->create([
                'option_text' => $option['option_text'],
                'option_value' => $option['option_value'] ?? 1,
                'personality_dimension' => $option['personality_dimension'] ?? null,
                'weight' => $option['weight'] ?? 1,
                'signal_key' => $option['personality_dimension'] ?? '',
                'signal_value' => $option['weight'] ?? 1,
            ]);
        }

        return redirect()->route('admin.personality.questions.index')
            ->with('success', 'Question created successfully.');
    }

    public function edit(PersonalityQuestion $question)
    {
        $question->load('options');
        $categories = [
            'work_style',
            'communication_style',
            'team_dynamics',
            'problem_solving',
            'leadership_initiative',
            'work_environment_preference',
            'motivation_drivers',
            'temperament_indicators',
            'organizational_culture',
        ];

        return view('admin.personality.questions.edit', compact('question', 'categories'));
    }

    public function update(Request $request, PersonalityQuestion $question)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'question_text' => 'required|string',
            'question_type' => 'required|string|max:50',
            'is_active' => 'required|boolean',
            'display_order' => 'required|integer|min:0',
            'options' => 'required|array|min:2',
            'options.*.id' => 'nullable|exists:personality_question_options,id',
            'options.*.option_text' => 'required|string|max:255',
            'options.*.option_value' => 'nullable|integer|min:1|max:10',
            'options.*.personality_dimension' => 'nullable|string|max:100',
            'options.*.weight' => 'nullable|integer|min:1|max:10',
        ]);

        $question->update([
            'category' => $validated['category'],
            'question_text' => $validated['question_text'],
            'question_type' => $validated['question_type'],
            'is_active' => $validated['is_active'],
            'display_order' => $validated['display_order'],
        ]);

        $existingIds = [];
        foreach ($validated['options'] as $optionData) {
            if (! empty($optionData['id'])) {
                $option = PersonalityQuestionOption::find($optionData['id']);
                if ($option) {
                    $option->update([
                        'option_text' => $optionData['option_text'],
                        'option_value' => $optionData['option_value'] ?? 1,
                        'personality_dimension' => $optionData['personality_dimension'] ?? null,
                        'weight' => $optionData['weight'] ?? 1,
                        'signal_key' => $optionData['personality_dimension'] ?? '',
                        'signal_value' => $optionData['weight'] ?? 1,
                    ]);
                    $existingIds[] = $option->id;
                }
            } else {
                $newOption = $question->options()->create([
                    'option_text' => $optionData['option_text'],
                    'option_value' => $optionData['option_value'] ?? 1,
                    'personality_dimension' => $optionData['personality_dimension'] ?? null,
                    'weight' => $optionData['weight'] ?? 1,
                    'signal_key' => $optionData['personality_dimension'] ?? '',
                    'signal_value' => $optionData['weight'] ?? 1,
                ]);
                $existingIds[] = $newOption->id;
            }
        }

        $question->options()->whereNotIn('id', $existingIds)->delete();

        return redirect()->route('admin.personality.questions.index')
            ->with('success', 'Question updated successfully.');
    }

    public function destroy(PersonalityQuestion $question)
    {
        $question->options()->delete();
        $question->delete();

        return redirect()->route('admin.personality.questions.index')
            ->with('success', 'Question deleted successfully.');
    }

    public function toggleStatus(PersonalityQuestion $question)
    {
        $question->update([
            'is_active' => ! $question->is_active,
        ]);

        return back()->with('success', 'Question status updated.');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'questions' => 'required|array',
            'questions.*.id' => 'required|exists:personality_questions,id',
            'questions.*.display_order' => 'required|integer|min:0',
        ]);

        foreach ($request->questions as $item) {
            PersonalityQuestion::where('id', $item['id'])
                ->update(['display_order' => $item['display_order']]);
        }

        return response()->json(['success' => true]);
    }
}

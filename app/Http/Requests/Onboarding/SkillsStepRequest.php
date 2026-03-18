<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class SkillsStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'skills' => 'required|array|min:1',
            'skills.*.name' => 'required|string|max:100',
            'skills.*.proficiency' => 'required|in:beginner,elementary,intermediate,upper_intermediate,advanced,expert',
            'skills.*.years_experience' => 'nullable|integer|min:0|max:30',
            'soft_skills' => 'nullable|array',
            'soft_skills.*' => 'string|max:100',
            'tools' => 'nullable|array',
            'tools.*.name' => 'required_with:tools|string|max:100',
            'tools.*.proficiency' => 'required_with:tools|in:beginner,elementary,intermediate,upper_intermediate,advanced,expert',
        ];
    }
}

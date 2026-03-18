<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class ExperienceStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'experiences' => 'required|array|min:1',
            'experiences.*.company' => 'required|string|max:255',
            'experiences.*.role' => 'required|string|max:255',
            'experiences.*.start_date' => 'required|date|before:experiences.*.end_date',
            'experiences.*.end_date' => 'nullable|date|after:experiences.*.start_date|before:today',
            'experiences.*.is_current' => 'boolean',
            'experiences.*.description' => 'nullable|string|max:1000',
            'experiences.*.achievements' => 'nullable|array',
            'experiences.*.achievements.*' => 'string|max:500',
            'experiences.*.skills_used' => 'nullable|array',
            'experiences.*.skills_used.*' => 'string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'experiences.required' => 'Please add at least one work experience',
            'experiences.*.company.required' => 'Company name is required for each experience',
            'experiences.*.role.required' => 'Role is required for each experience',
            'experiences.*.start_date.required' => 'Start date is required',
        ];
    }
}

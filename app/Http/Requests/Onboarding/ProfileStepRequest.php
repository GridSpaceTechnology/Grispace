<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class ProfileStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_role' => 'required|string|max:255',
            'years_of_experience' => 'required|integer|min:0|max:50',
            'industry' => 'required|string|max:100',
            'greatest_achievement' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'current_role.required' => 'Please enter your current role',
            'years_of_experience.required' => 'Please enter years of experience',
            'industry.required' => 'Please select your industry',
        ];
    }
}

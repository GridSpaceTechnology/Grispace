<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class PreferencesStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'desired_role' => 'required|string|max:255',
            'employment_type_preference' => 'required|array|min:1',
            'employment_type_preference.*' => 'in:full_time,part_time,contract,freelance,internship',
            'work_preference' => 'required|in:remote,hybrid,onsite,flexible',
            'location' => 'nullable|string|max:255',
            'location_country' => 'nullable|string|max:100',
            'salary_expectation_min' => 'nullable|numeric|min:0',
            'salary_expectation_max' => 'nullable|numeric|gte:salary_expectation_min',
            'availability' => 'required|in:immediately,2_weeks,1_month,2_months,3_months,passive',
            'experience_level' => 'required|in:entry,junior,mid,senior,lead,principal,executive',
            'role_interests' => 'nullable|array',
            'role_interests.*' => 'string|max:255',
            'industries_interest' => 'nullable|array',
            'industries_interest.*' => 'string|max:100',
            'organizational_type' => 'nullable|in:startup,small_business,mid_size_company,large_corporation,non_profit,government,agency,consulting',
            'motivation_drivers' => 'nullable|array',
            'motivation_drivers.*' => 'string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'desired_role.required' => 'Please enter your desired role',
            'employment_type_preference.required' => 'Select at least one employment type',
            'work_preference.required' => 'Select your work preference',
            'availability.required' => 'Select your availability',
            'experience_level.required' => 'Select your experience level',
        ];
    }
}

<?php

namespace App\Http\Requests\Onboarding;

use Illuminate\Foundation\Http\FormRequest;

class EducationStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'education' => 'nullable|array',
            'education.*.institution' => 'required_with:education|string|max:255',
            'education.*.qualification' => 'required_with:education|string|max:255',
            'education.*.field_of_study' => 'nullable|string|max:255',
            'education.*.start_date' => 'required_with:education|date',
            'education.*.end_date' => 'nullable|date|after:education.*.start_date',
            'education.*.is_current' => 'boolean',
            'education.*.grade' => 'nullable|string|max:50',
            'education.*.description' => 'nullable|string|max:500',
            'certifications' => 'nullable|array',
            'certifications.*.name' => 'required_with:certifications|string|max:255',
            'certifications.*.issuer' => 'nullable|string|max:255',
            'certifications.*.date_obtained' => 'nullable|date',
            'certifications.*.expires_at' => 'nullable|date|after:certifications.*.date_obtained',
            'certifications.*.credential_id' => 'nullable|string|max:255',
        ];
    }
}

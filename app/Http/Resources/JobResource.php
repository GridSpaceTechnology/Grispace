<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'role' => $this->role,
            'description' => $this->description,
            'industry' => $this->industry,
            'employment_type' => $this->employment_type,
            'salary_min' => $this->salary_min,
            'salary_max' => $this->salary_max,
            'salary_range' => $this->salary_range,
            'work_preference' => $this->work_preference,
            'minimum_experience' => $this->minimum_experience,
            'required_skills' => $this->whenLoaded('jobSkills', function () {
                return $this->jobSkills->map(fn ($js) => [
                    'id' => $js->skill_id,
                    'name' => $js->skill?->name,
                    'is_required' => $js->is_required,
                    'min_proficiency' => $js->min_proficiency,
                ]);
            }),
            'temperament_preference' => $this->temperament_preference,
            'status' => $this->status,
            'company' => new CompanyResource($this->whenLoaded('company')),
            'posted_at' => $this->created_at->toIso8601String(),
            'match_score' => $this->when($this->match_score !== null, $this->match_score),
        ];
    }

    public function getSalaryRangeAttribute(): ?string
    {
        if (! $this->salary_min && ! $this->salary_max) {
            return null;
        }

        $min = $this->salary_min ? '$'.number_format($this->salary_min) : 'Negotiable';
        $max = $this->salary_max ? '$'.number_format($this->salary_max) : '';

        return $max ? "{$min} - {$max}" : $min;
    }
}

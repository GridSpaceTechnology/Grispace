<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'avatar_url' => $this->candidateMedia?->avatar_url,
            'current_role' => $this->candidateProfile?->current_role,
            'desired_role' => $this->candidateProfile?->desired_role,
            'years_of_experience' => $this->candidateProfile?->years_of_experience,
            'industry' => $this->candidateProfile?->industry,
            'work_preference' => $this->candidateProfile?->work_preference,
            'salary_expectation' => $this->candidateProfile?->salary_expectation,
            'skills' => $this->whenLoaded('candidateSkills', function () {
                return $this->candidateSkills->map(fn ($s) => [
                    'id' => $s->skill_id,
                    'name' => $s->skill?->name ?? $s->skill_name,
                    'proficiency_level' => $s->proficiency_level,
                ]);
            }),
            'temperament_type' => $this->candidateAssessment?->temperament_type,
            'skill_score' => $this->candidateAssessment?->skill_score,
            'profile_completion' => $this->candidateProfile?->profile_completion_percentage ?? 0,
            'match_score' => $this->when($this->match_score !== null, $this->match_score),
        ];
    }
}

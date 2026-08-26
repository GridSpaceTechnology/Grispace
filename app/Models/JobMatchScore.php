<?php

namespace App\Models;

use App\Services\JobMatchingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobMatchScore extends Model
{
    protected $fillable = [
        'candidate_id',
        'job_id',
        'skills_fit_score',
        'personality_fit_score',
        'culture_fit_score',
        'temperament_fit_score',
        'overall_match_score',
        'skill_score',
        'role_score',
        'experience_score',
        'personality_score',
        'work_preference_score',
        'salary_score',
        'education_score',
        'availability_score',
        'matched_skills',
        'missing_skills',
        'strengths',
        'gaps',
        'reasons',
        'scored_at',
        'is_latest',
    ];

    protected function casts(): array
    {
        return [
            'matched_skills' => 'array',
            'missing_skills' => 'array',
            'strengths' => 'array',
            'gaps' => 'array',
            'reasons' => 'array',
            'scored_at' => 'datetime',
            'is_latest' => 'boolean',
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }

    public function category(): string
    {
        return app(JobMatchingService::class)->categoryFor($this->overall_match_score);
    }
}

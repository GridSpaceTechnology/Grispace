<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchProfile extends Model
{
    protected $table = 'match_profiles';

    protected $fillable = [
        'application_id',
        'overall_score',
        'skill_score',
        'experience_score',
        'salary_score',
        'work_preference_score',
        'personality_score',
        'education_score',
        'availability_score',
        'matched_skills',
        'missing_skills',
        'matched_requirements',
        'missing_requirements',
        'scored_at',
        'is_latest',
    ];

    protected function casts(): array
    {
        return [
            'matched_skills' => 'array',
            'missing_skills' => 'array',
            'matched_requirements' => 'array',
            'missing_requirements' => 'array',
            'scored_at' => 'datetime',
            'is_latest' => 'boolean',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }

    public function getMatchPercentageAttribute(): string
    {
        return match (true) {
            $this->overall_score >= 90 => 'Excellent',
            $this->overall_score >= 75 => 'Good',
            $this->overall_score >= 50 => 'Fair',
            default => 'Low',
        };
    }
}

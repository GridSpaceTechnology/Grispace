<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalityQuestion extends Model
{
    public const CANDIDATE_CATEGORIES = [
        'work_style',
        'communication_style',
        'team_dynamics',
        'problem_solving',
        'leadership_initiative',
        'work_environment_preference',
        'motivation_drivers',
        'temperament_indicators',
    ];

    public const EMPLOYER_CATEGORY = 'organizational_culture';

    protected $fillable = [
        'category',
        'question_text',
        'question_type',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function options(): HasMany
    {
        return $this->hasMany(PersonalityQuestionOption::class, 'question_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeCandidate($query)
    {
        return $query->whereIn('category', self::CANDIDATE_CATEGORIES);
    }

    public function scopeEmployer($query)
    {
        return $query->where('category', self::EMPLOYER_CATEGORY);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('id');
    }
}

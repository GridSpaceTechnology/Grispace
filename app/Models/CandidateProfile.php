<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateProfile extends Model
{
    protected $fillable = [
        'user_id',
        'current_role',
        'desired_role',
        'years_of_experience',
        'industry',
        'employment_type_preference',
        'salary_expectation',
        'work_preference',
        'greatest_achievement',
        'profile_completion_percentage',
    ];

    protected function casts(): array
    {
        return [
            'salary_expectation' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

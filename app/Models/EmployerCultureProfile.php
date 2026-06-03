<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployerCultureProfile extends Model
{
    protected $fillable = [
        'user_id',
        'work_environment',
        'communication_style',
        'leadership_style',
        'company_pace',
        'preferred_traits',
        'motivation_factors',
        'independence_level',
        'culture_summary',
        'innovation_level',
        'decision_making_style',
        'work_pace',
        'collaboration_level',
        'values',
    ];

    protected function casts(): array
    {
        return [
            'preferred_traits' => 'array',
            'motivation_factors' => 'array',
            'values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidatePersonalityProfile extends Model
{
    protected $fillable = [
        'candidate_id',
        'work_style',
        'communication_style',
        'collaboration_style',
        'leadership_style',
        'motivation_type',
        'temperament_type',
        'organizational_fit',
        'personality_summary',
        'work_style_summary',
        'strengths_summary',
        'dimension_scores',
        'dominant_traits',
        'workplace_compatibility',
        'assessment_completed',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'assessment_completed' => 'boolean',
            'completed_at' => 'datetime',
            'dimension_scores' => 'array',
            'dominant_traits' => 'array',
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateAssessment extends Model
{
    protected $fillable = [
        'user_id',
        'skill_score',
        'subskill_breakdown_json',
        'personality_scores_json',
        'temperament_type',
    ];

    protected function casts(): array
    {
        return [
            'subskill_breakdown_json' => 'array',
            'personality_scores_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

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
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }
}

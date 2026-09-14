<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateBehavioralProfile extends Model
{
    protected $fillable = [
        'user_id',
        'role_signals',
        'domain_signals',
        'skill_signals',
        'work_preference_signals',
        'location_signals',
        'viewed_jobs',
        'applied_jobs',
        'saved_jobs',
        'total_events',
        'last_activity_at',
    ];

    protected function casts(): array
    {
        return [
            'role_signals' => 'array',
            'domain_signals' => 'array',
            'skill_signals' => 'array',
            'work_preference_signals' => 'array',
            'location_signals' => 'array',
            'viewed_jobs' => 'array',
            'applied_jobs' => 'array',
            'saved_jobs' => 'array',
            'last_activity_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

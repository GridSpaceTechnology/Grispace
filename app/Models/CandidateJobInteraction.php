<?php

namespace App\Models;

use App\Enums\CandidateJobInteractionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateJobInteraction extends Model
{
    protected $fillable = [
        'user_id',
        'job_id',
        'interaction_type',
        'acted_at',
    ];

    protected function casts(): array
    {
        return [
            'interaction_type' => CandidateJobInteractionType::class,
            'acted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class, 'job_id');
    }
}

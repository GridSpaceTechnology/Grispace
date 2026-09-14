<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateSearchHistory extends Model
{
    protected $fillable = [
        'user_id',
        'query',
        'normalized_query',
        'domain',
        'detected_role',
        'skills',
        'seniority',
        'location',
        'work_preference',
        'filters',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'filters' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

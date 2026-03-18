<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateSignal extends Model
{
    protected $table = 'candidate_signals';

    protected $fillable = [
        'user_id',
        'category_id',
        'signal_type',
        'value',
        'metadata_json',
        'is_verified',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata_json' => 'array',
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SignalCategory::class);
    }
}

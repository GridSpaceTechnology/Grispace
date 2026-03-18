<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidatePreference extends Model
{
    protected $fillable = [
        'user_id',
        'organizational_type',
        'motivation_drivers_json',
    ];

    protected function casts(): array
    {
        return [
            'motivation_drivers_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

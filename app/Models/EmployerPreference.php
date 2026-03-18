<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployerPreference extends Model
{
    protected $fillable = [
        'user_id',
        'preferred_candidate_experience',
        'preferred_temperament',
        'preferred_personality_traits',
    ];

    protected $casts = [
        'preferred_personality_traits' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployerCultureProfile extends Model
{
    protected $fillable = [
        'user_id',
        'leadership_style',
        'communication_style',
        'innovation_level',
        'decision_making_style',
        'work_pace',
        'collaboration_level',
        'values',
    ];

    protected $casts = [
        'values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployerHiringProfile extends Model
{
    protected $fillable = [
        'user_id',
        'hiring_volume',
        'typical_roles',
        'departments',
        'experience_levels',
    ];

    protected $casts = [
        'typical_roles' => 'array',
        'departments' => 'array',
        'experience_levels' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobRequirement extends Model
{
    protected $table = 'job_requirements';

    protected $fillable = [
        'job_id',
        'requirement_type',
        'requirement_value',
        'weight',
        'is_mandatory',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'integer',
            'is_mandatory' => 'boolean',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }
}

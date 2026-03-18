<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSkill extends Model
{
    protected $table = 'job_skills';

    public $timestamps = false;

    protected $fillable = [
        'job_id',
        'skill_id',
        'is_required',
        'min_proficiency',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'min_proficiency' => 'integer',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }
}

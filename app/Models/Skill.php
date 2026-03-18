<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Skill extends Model
{
    protected $table = 'skills';

    protected $fillable = [
        'name',
        'slug',
        'category',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function candidateSkills(): HasMany
    {
        return $this->hasMany(CandidateSkill::class, 'skill_id');
    }

    public function jobSkills(): HasMany
    {
        return $this->hasMany(JobSkill::class, 'skill_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

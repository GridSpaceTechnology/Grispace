<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalityQuestion extends Model
{
    protected $fillable = [
        'category',
        'question_text',
        'question_type',
        'active_status',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'active_status' => 'boolean',
        ];
    }

    public function options(): HasMany
    {
        return $this->hasMany(PersonalityQuestionOption::class, 'question_id');
    }

    public function scopeActive($query)
    {
        return $query->where('active_status', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('id');
    }
}

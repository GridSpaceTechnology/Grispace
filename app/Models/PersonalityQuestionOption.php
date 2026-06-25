<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalityQuestionOption extends Model
{
    protected $fillable = [
        'question_id',
        'option_text',
        'option_value',
        'personality_dimension',
        'weight',
        'signal_key',
        'signal_value',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(PersonalityQuestion::class, 'question_id');
    }
}

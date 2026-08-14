<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalityAnswer extends Model
{
    protected $fillable = [
        'candidate_id',
        'question_id',
        'selected_option_id',
        'section',
    ];

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(PersonalityQuestion::class, 'question_id');
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(PersonalityQuestionOption::class, 'selected_option_id');
    }
}

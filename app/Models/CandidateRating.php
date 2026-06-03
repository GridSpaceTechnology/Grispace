<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateRating extends Model
{
    protected $fillable = [
        'application_id',
        'employer_id',
        'rating',
        'category',
        'review',
    ];

    public const CATEGORY_SKILLS = 'skills';

    public const CATEGORY_COMMUNICATION = 'communication';

    public const CATEGORY_EXPERIENCE = 'experience';

    public const CATEGORY_CULTURE_FIT = 'culture_fit';

    public const CATEGORY_OVERALL = 'overall';

    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'application_id');
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JobApplication extends Model
{
    protected $table = 'applications';

    protected $fillable = [
        'job_id',
        'candidate_id',
        'status',
        'match_score',
        'screening_answers',
        'candidate_note',
        'employer_note',
        'applied_at',
        'viewed_at',
        'shortlisted_at',
        'interview_at',
        'offer_sent_at',
        'hired_at',
        'rejected_at',
    ];

    protected function casts(): array
    {
        return [
            'screening_answers' => 'array',
            'applied_at' => 'datetime',
            'viewed_at' => 'datetime',
            'shortlisted_at' => 'datetime',
            'interview_at' => 'datetime',
            'offer_sent_at' => 'datetime',
            'hired_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public const STATUS_APPLIED = 'applied';

    public const STATUS_VIEWED = 'viewed';

    public const STATUS_SHORTLISTED = 'shortlisted';

    public const STATUS_INTERVIEW = 'interview';

    public const STATUS_OFFER = 'offer';

    public const STATUS_HIRED = 'hired';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_WITHDRAWN = 'withdrawn';

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function matchProfile(): HasOne
    {
        return $this->hasOne(MatchProfile::class)->where('is_latest', true);
    }

    public function matchProfiles(): HasMany
    {
        return $this->hasMany(MatchProfile::class);
    }

    public function isPending(): bool
    {
        return in_array($this->status, [self::STATUS_APPLIED, self::STATUS_VIEWED]);
    }

    public function canWithdraw(): bool
    {
        return in_array($this->status, [self::STATUS_APPLIED, self::STATUS_VIEWED, self::STATUS_SHORTLISTED]);
    }
}

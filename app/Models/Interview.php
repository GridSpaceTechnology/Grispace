<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    protected $fillable = [
        'employer_id',
        'candidate_id',
        'job_id',
        'job_application_id',
        'interview_type',
        'scheduled_at',
        'meeting_link',
        'location',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public const TYPE_PHONE = 'phone';

    public const TYPE_VIDEO = 'video';

    public const TYPE_ON_SITE = 'onsite';

    public const TYPE_TECHNICAL = 'technical';

    public const TYPE_BEHAVIORAL = 'behavioral';

    public const TYPE_PANEL = 'panel';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }

    public function isUpcoming(): bool
    {
        return $this->status === self::STATUS_SCHEDULED && $this->scheduled_at->isFuture();
    }
}

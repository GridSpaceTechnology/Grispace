<?php

namespace App\Models;

use App\Notifications\QueuedVerifyEmail;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    final public const SUSPENSION_REASON_UNVERIFIED_EMAIL = 'unverified_email';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'welcome_dismissed_at',
        'onboarding_completed',
        'profile_photo_path',
        'suspension_reason',
        'phone_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'welcome_dismissed_at' => 'datetime',
            'is_suspended' => 'boolean',
            'onboarding_completed' => 'boolean',
        ];
    }

    public function candidateProfile(): HasOne
    {
        return $this->hasOne(CandidateProfile::class);
    }

    public function candidateSkills(): HasMany
    {
        return $this->hasMany(CandidateSkill::class);
    }

    public function candidateExperiences(): HasMany
    {
        return $this->hasMany(CandidateExperience::class);
    }

    public function candidateEducation(): HasMany
    {
        return $this->hasMany(CandidateEducation::class);
    }

    public function candidateAssessment(): HasOne
    {
        return $this->hasOne(CandidateAssessment::class);
    }

    public function candidatePreferences(): HasOne
    {
        return $this->hasOne(CandidatePreference::class);
    }

    public function candidateMedia(): HasOne
    {
        return $this->hasOne(CandidateMedia::class);
    }

    public function candidateSignals(): HasMany
    {
        return $this->hasMany(CandidateSignal::class);
    }

    public function jobApplications(): HasMany
    {
        return $this->hasMany(JobApplication::class, 'candidate_id');
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class);
    }

    public function postedJobs(): HasMany
    {
        return $this->hasMany(Job::class, 'employer_id');
    }

    public function isCandidate(): bool
    {
        return $this->role === 'candidate';
    }

    public function isEmployer(): bool
    {
        return $this->role === 'employer';
    }

    public function employerProfile(): HasOne
    {
        return $this->hasOne(EmployerProfile::class);
    }

    public function employerHiringProfile(): HasOne
    {
        return $this->hasOne(EmployerHiringProfile::class);
    }

    public function employerCultureProfile(): HasOne
    {
        return $this->hasOne(EmployerCultureProfile::class);
    }

    public function employerPreference(): HasOne
    {
        return $this->hasOne(EmployerPreference::class);
    }

    public function employerShortlists(): HasMany
    {
        return $this->hasMany(EmployerShortlist::class, 'employer_id');
    }

    public function scheduledInterviews(): HasMany
    {
        return $this->hasMany(Interview::class, 'employer_id');
    }

    public function personalityAnswers(): HasMany
    {
        return $this->hasMany(PersonalityAnswer::class, 'candidate_id');
    }

    public function personalityProfile(): HasOne
    {
        return $this->hasOne(CandidatePersonalityProfile::class, 'candidate_id');
    }

    public function jobMatchScores(): HasMany
    {
        return $this->hasMany(JobMatchScore::class, 'candidate_id');
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profile_photo_path
            ? Storage::disk('public')->url($this->profile_photo_path)
            : null;
    }

    public function shouldShowWelcome(): bool
    {
        return is_null($this->welcome_dismissed_at);
    }

    public function dismissWelcome(): void
    {
        $this->update(['welcome_dismissed_at' => now()]);
    }

    public function conversationsAsEmployer(): HasMany
    {
        return $this->hasMany(Conversation::class, 'employer_id');
    }

    public function conversationsAsCandidate(): HasMany
    {
        return $this->hasMany(Conversation::class, 'candidate_id');
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function messageReads(): HasMany
    {
        return $this->hasMany(MessageRead::class);
    }

    public function isOnline(): bool
    {
        return Cache::has("user-online-{$this->id}");
    }

    public function lastSeen(): ?string
    {
        return Cache::get("user-last-seen-{$this->id}");
    }

    public function candidateVerifications(): HasMany
    {
        return $this->hasMany(CandidateVerification::class, 'candidate_id');
    }

    public function trustScore(): HasOne
    {
        return $this->hasOne(TrustScore::class, 'candidate_id');
    }

    public function verifiedPhones(): HasMany
    {
        return $this->hasMany(CandidateVerification::class, 'candidate_id')
            ->whereHas('verificationType', fn ($q) => $q->where('slug', 'phone'))
            ->where('status', 'approved');
    }

    public function hasVerifiedEmail(): bool
    {
        return ! is_null($this->email_verified_at);
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new QueuedVerifyEmail);
    }

    public function isSuspendedForUnverifiedEmail(): bool
    {
        return $this->is_suspended
            && $this->suspension_reason === self::SUSPENSION_REASON_UNVERIFIED_EMAIL;
    }

    public function deactivationDeadline(): ?CarbonInterface
    {
        if ($this->hasVerifiedEmail()) {
            return null;
        }

        return $this->created_at?->addDays((int) config('auth.email_verification.grace_days', 14));
    }

    public function daysUntilDeactivation(): ?int
    {
        $deadline = $this->deactivationDeadline();

        if (is_null($deadline)) {
            return null;
        }

        return max(0, now()->diffInDays($deadline, false));
    }

    public function hasVerifiedPhone(): bool
    {
        return ! is_null($this->phone_verified_at);
    }

    public function hasVerifiedIdentity(): bool
    {
        return $this->candidateVerifications()
            ->whereHas('verificationType', fn ($q) => $q->where('slug', 'identity'))
            ->approved()->exists();
    }

    public function hasVerifiedEducation(): bool
    {
        return $this->candidateVerifications()
            ->whereHas('verificationType', fn ($q) => $q->where('slug', 'education'))
            ->approved()->exists();
    }

    public function hasVerifiedEmployment(): bool
    {
        return $this->candidateVerifications()
            ->whereHas('verificationType', fn ($q) => $q->where('slug', 'employment'))
            ->approved()->exists();
    }

    public function hasVerifiedCertification(): bool
    {
        return $this->candidateVerifications()
            ->whereHas('verificationType', fn ($q) => $q->where('slug', 'certification'))
            ->approved()->exists();
    }

    public function unreadMessageCount(): int
    {
        return Conversation::forUser($this)
            ->get()
            ->sum(fn (Conversation $c) => $c->unreadMessagesCount($this));
    }
}

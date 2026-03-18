<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'welcome_dismissed_at',
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

    public function shouldShowWelcome(): bool
    {
        return is_null($this->welcome_dismissed_at);
    }

    public function dismissWelcome(): void
    {
        $this->update(['welcome_dismissed_at' => now()]);
    }
}

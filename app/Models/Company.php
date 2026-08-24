<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'tagline',
        'description',
        'industry',
        'company_size',
        'founded_year',
        'website',
        'logo_url',
        'cover_image_url',
        'location',
        'location_country',
        'culture_values_json',
        'benefits_json',
        'is_verified',
        'phone_number',
        'linkedin_url',
        'instagram_url',
        'twitter_url',
        'culture_description',
        'work_model',
        'allow_candidate_messages',
    ];

    protected function casts(): array
    {
        return [
            'culture_values_json' => 'array',
            'benefits_json' => 'array',
            'is_verified' => 'boolean',
            'founded_year' => 'integer',
            'allow_candidate_messages' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class, 'employer_id', 'user_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

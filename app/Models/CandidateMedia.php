<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateMedia extends Model
{
    protected $fillable = [
        'user_id',
        'intro_video_url',
        'role_video_url',
        'cv_path',
        'portfolio_links_json',
        'linkedin_url',
        'github_url',
    ];

    protected function casts(): array
    {
        return [
            'portfolio_links_json' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

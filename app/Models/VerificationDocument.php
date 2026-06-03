<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VerificationDocument extends Model
{
    protected $fillable = [
        'candidate_verification_id',
        'document_name',
        'document_path',
        'document_type',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'uploaded_at' => 'datetime',
        ];
    }

    public function candidateVerification(): BelongsTo
    {
        return $this->belongsTo(CandidateVerification::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->document_path);
    }
}

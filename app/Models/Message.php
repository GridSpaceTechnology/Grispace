<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_id',
        'sender_type',
        'message',
        'attachment_path',
        'attachment_type',
        'attachment_name',
        'attachment_size',
        'is_read',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function reads(): HasMany
    {
        return $this->hasMany(MessageRead::class);
    }

    public function getAttachmentUrlAttribute(): ?string
    {
        return $this->attachment_path
            ? Storage::disk('public')->url($this->attachment_path)
            : null;
    }

    public function getAttachmentIconAttribute(): string
    {
        return match ($this->attachment_type) {
            'application/pdf' => 'far fa-file-pdf',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'far fa-file-word',
            'image/jpeg', 'image/png', 'image/gif', 'image/webp' => 'far fa-file-image',
            default => 'far fa-file',
        };
    }

    public function isAttachmentImage(): bool
    {
        return $this->attachment_type && str_starts_with($this->attachment_type, 'image/');
    }
}

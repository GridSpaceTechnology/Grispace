<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consent extends Model
{
    protected $fillable = [
        'user_id',
        'consent_type',
        'consent_given',
        'ip_address',
        'user_agent',
        'granted_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'consent_given' => 'boolean',
            'granted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public const CONSENT_MARKETING = 'marketing';

    public const CONSENT_DATA_PROCESSING = 'data_processing';

    public const CONSENT_THIRD_PARTY = 'third_party_sharing';

    public const CONSENT_PROFILE_VISIBILITY = 'profile_visibility';

    public const CONSENT_EMAIL_NOTIFICATIONS = 'email_notifications';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function isGranted(User $user, string $type): bool
    {
        return static::where('user_id', $user->id)
            ->where('consent_type', $type)
            ->where('consent_given', true)
            ->exists();
    }

    public static function grant(User $user, string $type, ?string $ipAddress = null): self
    {
        return static::updateOrCreate(
            [
                'user_id' => $user->id,
                'consent_type' => $type,
            ],
            [
                'consent_given' => true,
                'granted_at' => now(),
                'revoked_at' => null,
                'ip_address' => $ipAddress,
                'user_agent' => request()->userAgent(),
            ]
        );
    }

    public static function revoke(User $user, string $type): bool
    {
        return static::where('user_id', $user->id)
            ->where('consent_type', $type)
            ->update([
                'consent_given' => false,
                'revoked_at' => now(),
            ]) > 0;
    }
}

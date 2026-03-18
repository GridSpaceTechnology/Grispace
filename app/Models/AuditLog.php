<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
        ];
    }

    public const ACTION_VIEW = 'view';

    public const ACTION_CREATE = 'create';

    public const ACTION_UPDATE = 'update';

    public const ACTION_DELETE = 'delete';

    public const ACTION_EXPORT = 'export';

    public const ACTION_SEARCH = 'search';

    public const ACTION_LOGIN = 'login';

    public const ACTION_LOGOUT = 'logout';

    public const ACTION_CONSENT_GRANT = 'consent_grant';

    public const ACTION_CONSENT_REVOKE = 'consent_revoke';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(
        ?int $userId,
        string $action,
        string $entityType,
        ?string $entityId = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = []
    ): self {
        return static::create([
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata,
        ]);
    }

    public static function logView(?int $userId, string $entityType, string $entityId): self
    {
        return static::log($userId, static::ACTION_VIEW, $entityType, $entityId);
    }

    public static function logSearch(int $userId, string $entityType, array $filters): self
    {
        return static::log(
            $userId,
            static::ACTION_SEARCH,
            $entityType,
            null,
            [],
            [],
            ['filters' => $filters]
        );
    }

    public static function logExport(int $userId, string $entityType, string $entityId): self
    {
        return static::log(
            $userId,
            static::ACTION_EXPORT,
            $entityType,
            $entityId,
            [],
            [],
            ['exported_at' => now()->toIso8601String()]
        );
    }
}

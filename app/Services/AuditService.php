<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public function log(
        ?int $userId,
        string $action,
        string $entityType,
        ?string $entityId = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = []
    ): AuditLog {
        return AuditLog::log(
            $userId ?? Auth::id(),
            $action,
            $entityType,
            $entityId,
            $this->sanitize($oldValues),
            $this->sanitize($newValues),
            $metadata
        );
    }

    public function logCandidateView(User $viewer, User $candidate): AuditLog
    {
        return $this->log(
            $viewer->id,
            AuditLog::ACTION_VIEW,
            'candidate',
            $candidate->id,
            [],
            [],
            [
                'viewed_profile_fields' => ['skills', 'experience', 'profile'],
                'candidate_visibility' => $candidate->profile_visibility,
            ]
        );
    }

    public function logCandidateSearch(User $employer, array $filters): AuditLog
    {
        return $this->log(
            $employer->id,
            AuditLog::ACTION_SEARCH,
            'candidate',
            null,
            [],
            [],
            [
                'filters_applied' => $filters,
                'results_count' => null,
            ]
        );
    }

    public function logCandidateExport(User $employer, User $candidate): AuditLog
    {
        return $this->log(
            $employer->id,
            AuditLog::ACTION_EXPORT,
            'candidate',
            $candidate->id,
            [],
            [],
            [
                'export_type' => 'candidate_profile',
                'exported_fields' => ['contact', 'profile', 'skills'],
            ]
        );
    }

    public function logApplicationView(User $employer, int $applicationId): AuditLog
    {
        return $this->log(
            $employer->id,
            AuditLog::ACTION_VIEW,
            'application',
            $applicationId
        );
    }

    public function logDataAccess(User $user, string $dataType): AuditLog
    {
        return $this->log(
            $user->id,
            AuditLog::ACTION_EXPORT,
            'personal_data',
            $user->id,
            [],
            [],
            [
                'data_type' => $dataType,
                'ip_address' => request()->ip(),
            ]
        );
    }

    public function getUserActivity(User $user, int $limit = 50)
    {
        return AuditLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getEntityHistory(string $entityType, string $entityId, int $limit = 50)
    {
        return AuditLog::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function sanitize(array $data): array
    {
        $sensitiveFields = ['password', 'token', 'secret', 'api_key', 'credit_card'];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }
}

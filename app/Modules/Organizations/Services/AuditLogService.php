<?php

declare(strict_types=1);

namespace App\Modules\Organizations\Services;

use App\Models\User;
use App\Modules\Organizations\Models\AuditLog;
use App\Modules\Organizations\Models\Organization;
use Illuminate\Database\Eloquent\Collection;

class AuditLogService
{
    /**
     * Record a tamper-evident audit log event.
     */
    public function record(
        string $action,
        ?User $user = null,
        ?Organization $organization = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $details = null,
        ?string $ipAddress = null
    ): AuditLog {
        return AuditLog::create([
            'organization_id' => $organization?->id,
            'user_id' => $user?->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'details' => $details,
            'ip_address' => $ipAddress ?? request()?->ip(),
            'created_at' => now(),
        ]);
    }

    /**
     * Retrieve audit logs for an organization.
     *
     * @return Collection<int, AuditLog>
     */
    public function getLogs(Organization $organization, int $limit = 50, ?string $action = null): Collection
    {
        $query = AuditLog::with('user:id,name,email')
            ->where('organization_id', $organization->id)
            ->orderByDesc('created_at');

        if ($action) {
            $query->where('action', $action);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Export organization audit logs to RFC 4180 compliant CSV string.
     */
    public function exportCsv(Organization $organization): string
    {
        $logs = $this->getLogs($organization, 500);

        $output = fopen('php://temp', 'r+');
        fputcsv($output, ['ID', 'Timestamp (UTC)', 'Action', 'User', 'User Email', 'Entity Type', 'Entity ID', 'Details', 'IP Address']);

        foreach ($logs as $log) {
            fputcsv($output, [
                $log->id,
                $log->created_at->toIso8601String(),
                $log->action,
                $log->user?->name ?? 'System',
                $log->user?->email ?? 'N/A',
                $log->entity_type ?? 'N/A',
                $log->entity_id ?? 'N/A',
                json_encode($log->details ?? []),
                $log->ip_address ?? '127.0.0.1',
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv ?: '';
    }
}

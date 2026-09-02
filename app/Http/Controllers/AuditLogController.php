<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Organizations\Models\Organization;
use App\Modules\Organizations\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogService
    ) {}

    /**
     * Display a list of audit logs for an organization.
     */
    public function index(Request $request, Organization $organization): JsonResponse
    {
        if (!$organization->hasMember($request->user())) {
            abort(403, 'Unauthorized. You are not a member of this organization.');
        }

        $action = $request->query('action');
        $logs = $this->auditLogService->getLogs(
            $organization,
            limit: min((int) ($request->query('limit', 50)), 100),
            action: is_string($action) ? $action : null
        );

        if ($request->wantsJson() && !$request->header('X-Inertia')) {
            return response()->json([
                'organization' => [
                    'id' => $organization->id,
                    'name' => $organization->name,
                ],
                'audit_logs' => $logs,
            ]);
        }

        return \Inertia\Inertia::render('Organizations/AuditLogs', [
            'organization' => $organization,
            'audit_logs' => $logs,
            'role' => $organization->getRole($request->user()),
        ]);
    }

    /**
     * Export audit logs as CSV for compliance audits.
     */
    public function export(Request $request, Organization $organization): Response
    {
        if (!$organization->hasRole($request->user(), ['owner', 'admin'])) {
            abort(403, 'Only organization owners and admins may export audit logs.');
        }

        $csv = $this->auditLogService->exportCsv($organization);
        $filename = "audit_log_{$organization->slug}_" . now()->format('Ymd_His') . ".csv";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}

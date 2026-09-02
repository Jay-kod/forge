<?php

declare(strict_types=1);

namespace Tests\Feature\Organizations;

use App\Models\User;
use App\Modules\Organizations\Models\AuditLog;
use App\Modules\Organizations\Services\AuditLogService;
use App\Modules\Organizations\Services\OrganizationService;
use App\Modules\Product\Enums\WorkflowStageType;
use App\Modules\Product\Models\Workflow;
use App\Modules\Product\Models\WorkflowStage;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_logs_are_recorded_on_member_lifecycle_actions(): void
    {
        $owner = User::factory()->create();
        $orgService = app(OrganizationService::class);
        $auditService = app(AuditLogService::class);

        $org = $orgService->createOrganization($owner, 'Starlight Defense');

        // 1. Invite a member
        $invitation = $orgService->inviteMember($org, $owner, 'cadet@starlight.com', 'member');
        $this->assertDatabaseHas('audit_logs', [
            'organization_id' => $org->id,
            'action' => 'member.invited',
        ]);

        // 2. Accept invite
        $cadet = User::factory()->create(['email' => 'cadet@starlight.com']);
        $orgService->acceptInvitation($invitation->token, $cadet);
        $this->assertDatabaseHas('audit_logs', [
            'organization_id' => $org->id,
            'user_id' => $cadet->id,
            'action' => 'member.joined',
        ]);

        // 3. Promote role
        $orgService->updateMemberRole($org, $owner, $cadet, 'admin');
        $this->assertDatabaseHas('audit_logs', [
            'organization_id' => $org->id,
            'action' => 'role.updated',
        ]);

        // 4. Remove member
        $orgService->removeMember($org, $owner, $cadet);
        $this->assertDatabaseHas('audit_logs', [
            'organization_id' => $org->id,
            'action' => 'member.removed',
        ]);
    }

    public function test_stage_approval_records_audit_log_for_organization_project(): void
    {
        $owner = User::factory()->create();
        $org = app(OrganizationService::class)->createOrganization($owner, 'Quantum Dynamics');

        $project = Project::create([
            'user_id' => $owner->id,
            'organization_id' => $org->id,
            'title' => 'Quantum Blueprint',
            'classification' => ProjectType::NEW_PRODUCT,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $workflow = Workflow::create(['project_id' => $project->id]);
        $stage = WorkflowStage::create([
            'workflow_id' => $workflow->id,
            'stage_type' => WorkflowStageType::UNDERSTANDING,
            'status' => 'completed',
            'order' => 1,
        ]);

        $this->actingAs($owner)->post(route('workflow.approve', [$project, $stage]));

        $this->assertDatabaseHas('audit_logs', [
            'organization_id' => $org->id,
            'user_id' => $owner->id,
            'action' => 'stage.approved',
            'entity_type' => 'workflow_stage',
            'entity_id' => $stage->id,
        ]);
    }

    public function test_audit_log_api_and_csv_export(): void
    {
        $owner = User::factory()->create();
        $org = app(OrganizationService::class)->createOrganization($owner, 'Vanguard Security');

        app(AuditLogService::class)->record(
            action: 'blueprint.exported',
            user: $owner,
            organization: $org,
            entityType: 'project',
            entityId: 99,
            details: ['format' => 'zip', 'files' => 14]
        );

        // JSON endpoint
        $res = $this->actingAs($owner)->getJson(route('organizations.audit-logs.index', $org));
        $res->assertOk()
            ->assertJsonPath('audit_logs.0.action', 'blueprint.exported');

        // CSV export endpoint
        $csvRes = $this->actingAs($owner)->get(route('organizations.audit-logs.export', $org));
        $csvRes->assertOk();
        $csvRes->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('blueprint.exported', $csvRes->getContent());
        $this->assertStringContainsString('Vanguard Security', $org->name);
    }
}

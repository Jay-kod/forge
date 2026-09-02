<?php

declare(strict_types=1);

namespace Tests\Feature\Workflow;

use App\Models\User;
use App\Modules\Credits\Services\CreditService;
use App\Modules\Product\Actions\ExecuteStageAction;
use App\Modules\Projects\Actions\CreateProjectAction;
use App\Modules\Projects\Enums\ProjectType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase3WorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected CreateProjectAction $createProjectAction;
    protected ExecuteStageAction $executeStageAction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createProjectAction = app(CreateProjectAction::class);
        $this->executeStageAction = app(ExecuteStageAction::class);
    }

    public function test_software_optimization_workflow_generates_audit_and_refactor_roadmap(): void
    {
        $user = User::factory()->create();
        app(CreditService::class)->grant($user, 100, 'test_grant');

        $project = $this->createProjectAction->execute(
            $user,
            'I want to refactor my legacy codebase and remove performance bottlenecks and reduce latency'
        );

        $this->assertEquals(ProjectType::SOFTWARE_OPTIMIZATION, $project->classification);

        $stages = $project->workflow->stages()->orderBy('order')->get();
        $stageTypes = $stages->pluck('stage_type.value')->toArray();

        $this->assertContains('repo_inspection', $stageTypes);
        $this->assertContains('code_audit', $stageTypes);
        $this->assertContains('refactor_roadmap', $stageTypes);

        // Execute repo inspection
        $repoStage = $stages->firstWhere('stage_type.value', 'repo_inspection');
        $this->executeStageAction->execute($user, $project, $repoStage);
        $this->assertDatabaseHas('product_documents', [
            'project_id' => $project->id,
            'type' => 'repo_inspection',
        ]);

        // Execute code audit
        $codeAuditStage = $stages->firstWhere('stage_type.value', 'code_audit');
        $this->executeStageAction->execute($user, $project, $codeAuditStage);
        $this->assertDatabaseHas('product_documents', [
            'project_id' => $project->id,
            'type' => 'code_audit',
        ]);

        // Execute refactor roadmap
        $roadmapStage = $stages->firstWhere('stage_type.value', 'refactor_roadmap');
        $this->executeStageAction->execute($user, $project, $roadmapStage);
        $this->assertDatabaseHas('product_documents', [
            'project_id' => $project->id,
            'type' => 'refactor_roadmap',
        ]);
    }

    public function test_technical_audit_workflow_generates_security_and_code_reviews(): void
    {
        $user = User::factory()->create();
        app(CreditService::class)->grant($user, 100, 'test_grant');

        $project = $this->createProjectAction->execute(
            $user,
            'Perform a comprehensive technical audit and security review of our codebase before investment'
        );

        $this->assertEquals(ProjectType::TECHNICAL_AUDIT, $project->classification);

        $stages = $project->workflow->stages()->orderBy('order')->get();
        $stageTypes = $stages->pluck('stage_type.value')->toArray();

        $this->assertContains('repo_inspection', $stageTypes);
        $this->assertContains('code_audit', $stageTypes);
        $this->assertContains('security_audit', $stageTypes);

        // Execute security audit stage
        $secStage = $stages->firstWhere('stage_type.value', 'security_audit');
        $this->executeStageAction->execute($user, $project, $secStage);
        $this->assertDatabaseHas('product_documents', [
            'project_id' => $project->id,
            'type' => 'security_audit',
        ]);
    }
}

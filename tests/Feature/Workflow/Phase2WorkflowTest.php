<?php

declare(strict_types=1);

namespace Tests\Feature\Workflow;

use App\Models\User;
use App\Modules\Billing\Contracts\EntitlementServiceInterface;
use App\Modules\Credits\Services\CreditService;
use App\Modules\Product\Actions\ExecuteStageAction;
use App\Modules\Product\Models\ProductDocument;
use App\Modules\Projects\Actions\CreateProjectAction;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Services\ClassificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase2WorkflowTest extends TestCase
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

    public function test_business_growth_workflow_generates_and_executes_growth_plan(): void
    {
        $user = User::factory()->create();
        app(CreditService::class)->grant($user, 100, 'test_grant');

        $project = $this->createProjectAction->execute(
            $user,
            'I run an established boutique fitness gym and want to grow customers and scale business'
        );

        $this->assertEquals(ProjectType::BUSINESS_GROWTH, $project->classification);

        $stages = $project->workflow->stages()->orderBy('order')->get();
        $this->assertCount(4, $stages);
        $this->assertEquals('understanding', $stages[0]->stage_type->value);
        $this->assertEquals('business_analysis', $stages[1]->stage_type->value);
        $this->assertEquals('research', $stages[2]->stage_type->value);
        $this->assertEquals('growth_plan', $stages[3]->stage_type->value);

        // Execute stages sequentially
        foreach ($stages as $stage) {
            $this->executeStageAction->execute($user, $project, $stage);
        }

        // Verify Growth Plan document generated
        $growthDoc = ProductDocument::where('project_id', $project->id)
            ->where('type', 'growth_plan')
            ->first();

        $this->assertNotNull($growthDoc);
        $this->assertStringContainsString('Comprehensive Growth Plan', $growthDoc->content);
        $this->assertStringContainsString('90-Day Execution Sprints', $growthDoc->content);
    }

    public function test_website_improvement_workflow_generates_audit_and_roadmap(): void
    {
        $user = User::factory()->create();
        app(CreditService::class)->grant($user, 100, 'test_grant');

        $project = $this->createProjectAction->execute(
            $user,
            'My B2B landing page gets traffic but is not converting visitors and bounce rate is high'
        );

        $this->assertEquals(ProjectType::WEBSITE_IMPROVEMENT, $project->classification);

        $stages = $project->workflow->stages()->orderBy('order')->get();
        $this->assertCount(5, $stages);
        $this->assertEquals('understanding', $stages[0]->stage_type->value);
        $this->assertEquals('website_audit', $stages[1]->stage_type->value);
        $this->assertEquals('ux_analysis', $stages[2]->stage_type->value);
        $this->assertEquals('competitor_comparison', $stages[3]->stage_type->value);
        $this->assertEquals('improvement_plan', $stages[4]->stage_type->value);

        // Execute all stages
        foreach ($stages as $stage) {
            $this->executeStageAction->execute($user, $project, $stage);
        }

        // Verify Website Audit stage has structured audit content
        $auditStage = $stages[1]->fresh();
        $this->assertNotNull($auditStage->content['audit']);
        $this->assertArrayHasKey('overall_health_score', $auditStage->content['audit']);

        // Verify Improvement Plan document generated
        $planDoc = ProductDocument::where('project_id', $project->id)
            ->where('type', 'improvement_plan')
            ->first();

        $this->assertNotNull($planDoc);
        $this->assertStringContainsString('Optimization & Improvement Roadmap', $planDoc->content);
    }
}

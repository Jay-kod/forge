<?php

declare(strict_types=1);

namespace Tests\Feature\Workflow;

use App\Models\User;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Credits\Actions\GrantCreditsAction;
use App\Modules\Product\Actions\ExecuteStageAction;
use App\Modules\Product\Jobs\RunAutomaticWorkflowJob;
use App\Modules\Projects\Actions\CreateProjectAction;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\WorkflowMode;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use RuntimeException;
use Tests\TestCase;

class AutomaticWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_free_user_cannot_create_automatic_workflow(): void
    {
        $user = User::factory()->create();
        $freePlan = Plan::where('slug', 'free')->first();

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $freePlan->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Upgraded plan required for automatic workflow execution.');

        app(CreateProjectAction::class)->execute(
            user: $user,
            userInput: 'Auto platform build',
            mode: WorkflowMode::AUTOMATIC
        );
    }

    public function test_pro_user_creating_automatic_workflow_dispatches_job(): void
    {
        Queue::fake();

        $user = User::factory()->create();
        $proPlan = Plan::where('slug', 'pro')->first();

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $proPlan->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        $project = app(CreateProjectAction::class)->execute(
            user: $user,
            userInput: 'Building automated workflow software',
            title: 'Auto Pipeline',
            mode: WorkflowMode::AUTOMATIC
        );

        Queue::assertPushed(RunAutomaticWorkflowJob::class, function ($job) use ($project) {
            return $job->project->id === $project->id;
        });
    }

    public function test_run_automatic_workflow_job_executes_all_stages(): void
    {
        $user = User::factory()->create();
        $proPlan = Plan::where('slug', 'pro')->first();

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $proPlan->id,
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        // Grant enough credits for multiple stages
        app(GrantCreditsAction::class)->execute($user, 200, 'test_grant');

        // Create project in page-by-page so we can run the job directly
        $project = app(CreateProjectAction::class)->execute(
            user: $user,
            userInput: 'Campus event booking and ticketing portal',
            title: 'Campus Ticketing Project',
            mode: WorkflowMode::PAGE_BY_PAGE
        );

        $job = new RunAutomaticWorkflowJob($project);
        $job->handle(app(ExecuteStageAction::class));

        $project->refresh();

        // Project should be marked as completed
        $this->assertEquals(ProjectStatus::COMPLETED, $project->status);

        // Discovery, evidence, competitors should be created
        $this->assertDatabaseHas('discoveries', ['project_id' => $project->id]);
        $this->assertDatabaseHas('competitors', ['project_id' => $project->id]);

        // All stages should be completed
        $pendingCount = $project->workflow->stages()->where('status', '!=', 'completed')->count();
        $this->assertEquals(0, $pendingCount);
    }
}

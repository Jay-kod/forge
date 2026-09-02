<?php

declare(strict_types=1);

namespace Tests\Feature\ContinuousIntelligence;

use App\Models\User;
use App\Modules\ContinuousIntelligence\Services\CompetitorDriftMonitor;
use App\Modules\Discovery\Models\Competitor;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitorDriftMonitorTest extends TestCase
{
    use RefreshDatabase;

    public function test_drift_monitor_detects_competitor_moves_and_dispatches_alerts(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'CRM Next Gen',
            'classification' => ProjectType::NEW_PRODUCT,
            'status' => ProjectStatus::ACTIVE,
        ]);

        // Competitor 1: Freemium pricing drift
        Competitor::create([
            'project_id' => $project->id,
            'name' => 'FastCRM Rival',
            'url' => 'https://fastcrm.example.com',
            'pricing' => ['model' => 'Freemium with tier 1 free'],
        ]);

        // Competitor 2: AI feature expansion
        Competitor::create([
            'project_id' => $project->id,
            'name' => 'SmartLead AI',
            'url' => 'https://smartlead.example.com',
            'strengths' => ['AI agent pipeline', 'Automation workflows'],
        ]);

        $monitor = app(CompetitorDriftMonitor::class);
        $result = $monitor->monitorProject($project);

        $this->assertEquals(2, $result['analyzed_competitors']);
        $this->assertEquals(2, $result['drifts_detected']);
        $this->assertEquals(2, $result['alerts_dispatched']);

        $this->assertDatabaseHas('alerts', [
            'project_id' => $project->id,
            'type' => 'competitor_drift',
            'severity' => 'warning',
            'title' => 'Aggressive Pricing Shift by FastCRM Rival',
        ]);

        $this->assertDatabaseHas('alerts', [
            'project_id' => $project->id,
            'type' => 'competitor_drift',
            'severity' => 'info',
            'title' => 'SmartLead AI Pushing AI & Automation Capabilities',
        ]);
    }

    public function test_artisan_command_scans_active_projects(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'SupplyChain Intelligence',
            'classification' => ProjectType::NEW_PRODUCT,
            'status' => ProjectStatus::ACTIVE,
        ]);

        Competitor::create([
            'project_id' => $project->id,
            'name' => 'OmniChain Logistics',
            'url' => 'https://omnichain.example.com',
            'pricing' => ['model' => 'Freemium tier available'],
        ]);

        $this->artisan('forge:monitor-competitors', ['--project' => (string) $project->id])
            ->assertSuccessful()
            ->expectsOutputToContain('OmniChain Logistics');

        $this->assertDatabaseHas('alerts', [
            'project_id' => $project->id,
            'type' => 'competitor_drift',
        ]);
    }
}

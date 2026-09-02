<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Discovery;

use App\Models\User;
use App\Modules\Discovery\Enums\DiscoveryVerdict;
use App\Modules\Discovery\Services\CompetitorAnalysisService;
use App\Modules\Discovery\Services\DiscoveryService;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoveryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DiscoveryService $discoveryService;
    protected CompetitorAnalysisService $competitorService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->discoveryService = new DiscoveryService();
        $this->competitorService = new CompetitorAnalysisService();
    }

    public function test_maps_competitors_for_project(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Campus Events',
            'description' => 'I want to build an app for university campus events and student ticket resale',
            'classification' => ProjectType::NEW_PRODUCT,
        ]);

        $competitors = $this->competitorService->analyzeCompetitors($project);

        $this->assertNotEmpty($competitors);
        $this->assertDatabaseHas('competitors', [
            'project_id' => $project->id,
            'name' => 'Eventbrite',
        ]);
    }

    public function test_evaluates_strategic_verdict_and_opportunities(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Campus Events',
            'description' => 'I want to build an app for university campus events and student ticket resale',
            'classification' => ProjectType::NEW_PRODUCT,
        ]);

        $discovery = $this->discoveryService->evaluateDiscovery($project);

        $this->assertEquals(DiscoveryVerdict::BUILD_WITH_MODIFICATIONS, $discovery->verdict);
        $this->assertNotEmpty($discovery->summary);
        $this->assertDatabaseHas('discoveries', [
            'project_id' => $project->id,
        ]);
        $this->assertDatabaseHas('opportunities', [
            'project_id' => $project->id,
        ]);
        $this->assertDatabaseHas('recommendations', [
            'project_id' => $project->id,
        ]);
    }
}

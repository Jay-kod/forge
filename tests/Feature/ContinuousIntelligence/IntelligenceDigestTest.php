<?php

declare(strict_types=1);

namespace Tests\Feature\ContinuousIntelligence;

use App\Models\User;
use App\Modules\ContinuousIntelligence\Services\IntelligenceDigestService;
use App\Modules\Discovery\Models\Competitor;
use App\Modules\GitHub\Models\RepositoryAudit;
use App\Modules\Opportunity\Models\Opportunity;
use App\Modules\Opportunity\Models\Recommendation;
use App\Modules\Product\Models\ProductDocument;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntelligenceDigestTest extends TestCase
{
    use RefreshDatabase;

    public function test_digest_service_synthesizes_markdown_document_and_alerts(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Autonomous Drone Fleet Manager',
            'classification' => ProjectType::NEW_PRODUCT,
            'status' => ProjectStatus::ACTIVE,
        ]);

        // Add Quick Win Opportunity
        $opp = Opportunity::create([
            'project_id' => $project->id,
            'title' => 'Geofencing Flight Zones',
            'description' => 'Automatic airspace avoidance',
            'category' => 'feature',
            'impact' => 'high',
            'difficulty' => 'low',
        ]);

        Recommendation::create([
            'project_id' => $project->id,
            'opportunity_id' => $opp->id,
            'title' => 'Connect FAA OpenGIS Geofences',
            'description' => 'Stream live NOTAM and restriction coordinates',
            'suggested_action' => 'Subscribe to FAA API',
            'why_it_matters' => 'Guarantees regulatory safety',
            'potential_impact' => 'high',
            'difficulty' => 'low',
        ]);

        // Add Competitor
        Competitor::create([
            'project_id' => $project->id,
            'name' => 'SkyRoute Dynamics',
            'pricing' => ['model' => 'Usage-based per flight hour'],
            'category' => 'direct',
        ]);

        // Add Repo Audit
        RepositoryAudit::create([
            'project_id' => $project->id,
            'repo_full_name' => 'drones/fleet-controller',
            'primary_language' => 'Go',
            'detected_framework' => 'Standard Library',
            'code_health_score' => 92,
            'technical_debt_score' => 12,
            'security_score' => 98,
        ]);

        $service = app(IntelligenceDigestService::class);
        $doc = $service->generateForProject($project);

        $this->assertInstanceOf(ProductDocument::class, $doc);
        $this->assertEquals('intelligence_digest', $doc->type);
        $this->assertEquals(1, $doc->version);
        $this->assertStringContainsString('Strategic Intelligence Digest', $doc->content);
        $this->assertStringContainsString('Geofencing Flight Zones', $doc->content);
        $this->assertStringContainsString('SkyRoute Dynamics', $doc->content);
        $this->assertStringContainsString('drones/fleet-controller', $doc->content);

        // Verify In-App Alert dispatched
        $this->assertDatabaseHas('alerts', [
            'project_id' => $project->id,
            'type' => 'digest_published',
            'severity' => 'info',
        ]);

        // Generating a second time increments version to 2
        $doc2 = $service->generateForProject($project);
        $this->assertEquals(2, $doc2->version);
    }

    public function test_artisan_command_generates_digest(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'EdTech LMS Engine',
            'classification' => ProjectType::NEW_PRODUCT,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $this->artisan('forge:generate-digest', ['--project' => (string) $project->id])
            ->assertSuccessful()
            ->expectsOutputToContain('Generated Digest');

        $this->assertDatabaseHas('product_documents', [
            'project_id' => $project->id,
            'type' => 'intelligence_digest',
        ]);
    }
}

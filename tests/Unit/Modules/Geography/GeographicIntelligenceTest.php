<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Geography;

use App\Models\User;
use App\Modules\Geography\Models\Location;
use App\Modules\Geography\Models\Market;
use App\Modules\Geography\Services\GeographicIntelligenceService;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;
use App\Modules\Research\Services\ResearchEngine;
use App\Modules\Research\Services\WebSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeographicIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    protected GeographicIntelligenceService $geoService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->geoService = new GeographicIntelligenceService();
    }

    public function test_detects_geographic_hub_and_initializes_market(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Cross-Border Logistics Network',
            'description' => 'I want to expand our parcel delivery operations to Lagos and across Nigeria',
            'classification' => ProjectType::MARKET_EXPANSION,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $market = $this->geoService->detectAndInitializeMarket($project, $project->description);

        $this->assertNotNull($market);
        $this->assertInstanceOf(Market::class, $market);
        $this->assertEquals('Lagos, Nigeria', $market->target_geography);
        $this->assertNotEmpty($market->tam_estimate);
        $this->assertNotEmpty($market->key_drivers);

        // Verify Location record created
        $location = Location::where('country_code', 'NGA')->where('city', 'Lagos')->first();
        $this->assertNotNull($location);
        $this->assertEquals('NGN', $location->currency_code);
        $this->assertTrue($location->payment_methods['mobile_money']);
    }

    public function test_research_engine_incorporates_geographic_market_suffix(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Artisan Bakery Chain Expansion',
            'description' => 'Expand artisan bakery chain to London UK',
            'classification' => ProjectType::MARKET_EXPANSION,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $this->geoService->detectAndInitializeMarket($project, $project->description);

        $engine = new ResearchEngine(new WebSearchService());
        $result = $engine->conductResearch($project, 'competitor');

        $this->assertNotNull($result);
        $this->assertStringContainsString('London, United Kingdom', $result->findings['topic']);
    }
}

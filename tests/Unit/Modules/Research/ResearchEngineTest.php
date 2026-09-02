<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Research;

use App\Models\User;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;
use App\Modules\Research\Enums\SourceType;
use App\Modules\Research\Services\ResearchEngine;
use App\Modules\Research\Services\WebSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResearchEngineTest extends TestCase
{
    use RefreshDatabase;

    protected ResearchEngine $researchEngine;
    protected WebSearchService $searchService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->searchService = new WebSearchService();
        $this->researchEngine = new ResearchEngine($this->searchService);
    }

    public function test_classifies_source_domains_correctly(): void
    {
        [$govType, $govScore] = $this->searchService->classifySource('https://www.statista.com/report/1', 'Statista');
        $this->assertEquals(SourceType::GOVERNMENT, $govType);
        $this->assertEquals(0.95, $govScore);

        [$pubType, $pubScore] = $this->searchService->classifySource('https://techcrunch.com/article/1', 'TechCrunch');
        $this->assertEquals(SourceType::PUBLICATION, $pubType);
        $this->assertEquals(0.80, $pubScore);

        [$commType, $commScore] = $this->searchService->classifySource('https://news.ycombinator.com/item?id=123', 'HN');
        $this->assertEquals(SourceType::COMMUNITY, $commType);
        $this->assertEquals(0.55, $commScore);
    }

    public function test_conducts_research_and_stores_sources(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Campus Events Ticketing',
            'description' => 'I want to build an app for university campus events and ticket resale',
            'classification' => ProjectType::NEW_PRODUCT,
        ]);

        $result = $this->researchEngine->conductResearch($project, 'market');

        $this->assertNotNull($result->session);
        $this->assertEquals('completed', $result->session->status);
        $this->assertGreaterThan(0, count($result->sources));
        $this->assertDatabaseHas('research_sessions', [
            'project_id' => $project->id,
            'type' => 'market',
        ]);
        $this->assertDatabaseHas('research_sources', [
            'research_session_id' => $result->session->id,
        ]);
    }
}

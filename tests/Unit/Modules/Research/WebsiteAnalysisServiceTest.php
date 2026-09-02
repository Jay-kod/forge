<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Research;

use App\Models\User;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;
use App\Modules\Research\Services\WebsiteAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WebsiteAnalysisServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WebsiteAnalysisService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WebsiteAnalysisService();
    }

    public function test_analyzes_website_and_persists_audit_record(): void
    {
        Http::fake([
            'https://testapp.dev*' => Http::response(
                '<!DOCTYPE html><html><head><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>TestApp - AI Agent Platform</title><meta name="description" content="The leading platform for autonomous AI agent pipelines."></head><body><h1>Transform Your Workflow with AI</h1><h2>Trusted by 10,000+ teams</h2><a href="/signup">Start Free Trial</a></body></html>',
                200
            ),
        ]);

        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'TestApp Conversion Audit',
            'description' => 'Audit testapp.dev for higher conversion and better UX',
            'classification' => ProjectType::WEBSITE_IMPROVEMENT,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $analysis = $this->service->analyze($project, 'https://testapp.dev');

        $this->assertNotNull($analysis);
        $this->assertEquals('https://testapp.dev', $analysis->url);
        $this->assertEquals('TestApp - AI Agent Platform', $analysis->meta_title);
        $this->assertEquals('The leading platform for autonomous AI agent pipelines.', $analysis->meta_description);
        $this->assertCount(1, $analysis->headings['h1']);
        $this->assertEquals('Transform Your Workflow with AI', $analysis->headings['h1'][0]);

        $this->assertTrue($analysis->performance_hints['has_ssl']);
        $this->assertTrue($analysis->performance_hints['has_viewport']);
        $this->assertGreaterThanOrEqual(70, $analysis->seo_score);
        $this->assertGreaterThanOrEqual(70, $analysis->ux_score);
        $this->assertNotEmpty($analysis->recommendations);

        // Verify relationship on Project
        $this->assertNotNull($project->fresh()->websiteAnalysis);
        $this->assertEquals($analysis->id, $project->fresh()->websiteAnalysis->id);
    }

    public function test_handles_unreachable_domains_gracefully(): void
    {
        Http::fake([
            '*' => Http::response(null, 500),
        ]);

        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'Offline Site Audit',
            'description' => 'Audit unreachable.example',
            'classification' => ProjectType::WEBSITE_IMPROVEMENT,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $analysis = $this->service->analyze($project, 'unreachable.example');

        $this->assertNotNull($analysis);
        $this->assertEquals('https://unreachable.example', $analysis->url);
        $this->assertEquals('completed', $analysis->status);
        $this->assertNotEmpty($analysis->recommendations);
    }
}

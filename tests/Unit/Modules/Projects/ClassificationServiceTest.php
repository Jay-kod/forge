<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Projects;

use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Services\ClassificationService;
use PHPUnit\Framework\TestCase;

class ClassificationServiceTest extends TestCase
{
    protected ClassificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ClassificationService();
    }

    public function test_classifies_new_product_idea(): void
    {
        $result = $this->service->classify('I want to build a SaaS app for university campus events');

        $this->assertEquals(ProjectType::NEW_PRODUCT, $result->classification);
        $this->assertGreaterThanOrEqual(0.8, $result->confidence);
    }

    public function test_classifies_business_growth(): void
    {
        $result = $this->service->classify('I run a local laundry business and want more customers');

        $this->assertEquals(ProjectType::BUSINESS_GROWTH, $result->classification);
        $this->assertEquals(['understanding', 'business_analysis', 'research', 'growth_plan'], $result->suggestedStages);
    }

    public function test_classifies_website_improvement(): void
    {
        $result = $this->service->classify('My B2B landing page gets traffic but is not converting visitors');

        $this->assertEquals(ProjectType::WEBSITE_IMPROVEMENT, $result->classification);
        $this->assertEquals(['understanding', 'website_audit', 'ux_analysis', 'competitor_comparison', 'improvement_plan'], $result->suggestedStages);
    }

    public function test_classifies_software_optimization(): void
    {
        $result = $this->service->classify('I have a GitHub repo that needs refactoring and architectural improvement');

        $this->assertEquals(ProjectType::SOFTWARE_OPTIMIZATION, $result->classification);
    }

    public function test_classifies_market_expansion(): void
    {
        $result = $this->service->classify('I want to expand my logistics operations to Lagos');

        $this->assertEquals(ProjectType::MARKET_EXPANSION, $result->classification);
        $this->assertEquals(['understanding', 'geographic_research', 'market_comparison', 'expansion_strategy'], $result->suggestedStages);
    }

    public function test_classifies_strategic_planning(): void
    {
        $result = $this->service->classify('We need a comprehensive strategic planning roadmap for next year');

        $this->assertEquals(ProjectType::STRATEGIC_PLANNING, $result->classification);
        $this->assertEquals(['understanding', 'situation_analysis', 'research', 'strategy', 'roadmap'], $result->suggestedStages);
    }

    public function test_classifies_process_automation(): void
    {
        $result = $this->service->classify('We have a manual spreadsheet paperwork process I want to digitize and automate');

        $this->assertEquals(ProjectType::PROCESS_AUTOMATION, $result->classification);
    }

    public function test_classifies_market_validation(): void
    {
        $result = $this->service->classify('I have an idea for therapists but I do not know if there is a market or if it makes sense');

        $this->assertEquals(ProjectType::MARKET_VALIDATION, $result->classification);
    }
}

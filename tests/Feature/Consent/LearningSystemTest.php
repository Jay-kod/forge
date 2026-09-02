<?php

declare(strict_types=1);

namespace Tests\Feature\Consent;

use App\Models\User;
use App\Modules\Consent\Enums\ConsentType;
use App\Modules\Consent\Models\LearningSignal;
use App\Modules\Consent\Services\ConsentService;
use App\Modules\Consent\Services\LearningSystem;
use App\Modules\Projects\Actions\CreateProjectAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_learning_system_respects_user_consent(): void
    {
        $user = User::factory()->create();
        $learningSystem = app(LearningSystem::class);

        // 1. Without consent: rejected and zero telemetry saved
        $unconsented = $learningSystem->recordSignal(
            $user,
            'competitor_analysis',
            'quality_feedback',
            ['rating' => 1]
        );

        $this->assertNull($unconsented);
        $this->assertEquals(0, LearningSignal::count());

        // 2. With explicit consent: recorded successfully
        $consentService = app(ConsentService::class);
        $consentService->recordConsent($user, ConsentType::AI_IMPROVEMENT, true);

        $consented = $learningSystem->recordSignal(
            $user,
            'competitor_analysis',
            'quality_feedback',
            ['rating' => 1]
        );

        $this->assertNotNull($consented);
        $this->assertEquals(1, LearningSignal::count());
    }

    public function test_metadata_is_deeply_scrubbed_of_pii(): void
    {
        $user = User::factory()->create();
        app(ConsentService::class)->recordConsent($user, ConsentType::AI_IMPROVEMENT, true);

        $learningSystem = app(LearningSystem::class);

        $signal = $learningSystem->recordSignal(
            $user,
            'pricing_strategy',
            'recommendation_accepted',
            [
                'user_id' => 999,
                'email' => 'adaeze@startup.ng',
                'name' => 'Adaeze Developer',
                'workflow_mode' => 'new_product',
                'reason' => 'viable_tiering',
            ]
        );

        $this->assertNotNull($signal);
        $this->assertArrayNotHasKey('user_id', $signal->context_meta);
        $this->assertArrayNotHasKey('email', $signal->context_meta);
        $this->assertArrayNotHasKey('name', $signal->context_meta);
        $this->assertEquals('new_product', $signal->context_meta['workflow_mode']);
        $this->assertEquals('viable_tiering', $signal->context_meta['reason']);
    }

    public function test_category_metrics_and_weight_modifier(): void
    {
        $user = User::factory()->create();
        app(ConsentService::class)->recordConsent($user, ConsentType::AI_IMPROVEMENT, true);
        $learningSystem = app(LearningSystem::class);

        // 4 accepts, 1 reject = 80% acceptance -> 1.25 boost
        for ($i = 0; $i < 4; $i++) {
            $learningSystem->recordSignal($user, 'growth_tactics', 'recommendation_accepted');
        }
        $learningSystem->recordSignal($user, 'growth_tactics', 'recommendation_rejected');

        $metrics = $learningSystem->getCategoryMetrics('growth_tactics');

        $this->assertEquals(4, $metrics['accepted_count']);
        $this->assertEquals(1, $metrics['rejected_count']);
        $this->assertEquals(80.0, $metrics['acceptance_rate_percent']);
        $this->assertEquals(1.25, $metrics['weight_modifier']);
    }

    public function test_feedback_controller_web_endpoint(): void
    {
        $user = User::factory()->create();
        app(ConsentService::class)->recordConsent($user, ConsentType::AI_IMPROVEMENT, true);

        $project = app(CreateProjectAction::class)->execute($user, 'Target marketing engine', 'Marketing Platform');

        $response = $this->actingAs($user)->postJson("/projects/{$project->id}/feedback", [
            'category' => 'architecture',
            'signal_type' => 'quality_feedback',
            'rating' => 1,
            'stage_type' => 'architecture',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('recorded', true);

        $this->assertDatabaseHas('learning_signals', [
            'category' => 'architecture',
            'signal_type' => 'quality_feedback',
        ]);
    }
}

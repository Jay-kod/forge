<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Modules\Notifications\Models\Alert;
use App\Modules\Notifications\Services\AlertService;
use App\Modules\Projects\Enums\ProjectStatus;
use App\Modules\Projects\Enums\ProjectType;
use App\Modules\Projects\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertTest extends TestCase
{
    use RefreshDatabase;

    public function test_alert_service_dispatches_and_deduplicates_alerts(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'DevOps Automation Suite',
            'classification' => ProjectType::NEW_PRODUCT,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $alertService = app(AlertService::class);

        // 1. Dispatch first alert
        $alert1 = $alertService->dispatch(
            $project,
            'drift_detected',
            'warning',
            'Competitor Price Change Detected',
            'Competitor X dropped pricing by 25%'
        );

        $this->assertInstanceOf(Alert::class, $alert1);
        $this->assertEquals(1, Alert::count());
        $this->assertEquals(1, $alertService->getUnreadCount($user));

        // 2. Dispatch identical alert within 24 hours (should de-duplicate)
        $alert2 = $alertService->dispatch(
            $project,
            'drift_detected',
            'warning',
            'Competitor Price Change Detected',
            'Competitor X dropped pricing by 30% (revised)'
        );

        $this->assertEquals($alert1->id, $alert2->id);
        $this->assertEquals(1, Alert::count());
        $this->assertEquals('Competitor X dropped pricing by 30% (revised)', $alert2->message);
    }

    public function test_alerts_endpoints_and_read_state(): void
    {
        $user = User::factory()->create();
        $project = Project::create([
            'user_id' => $user->id,
            'title' => 'E-Commerce Marketplace',
            'classification' => ProjectType::NEW_PRODUCT,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $alertService = app(AlertService::class);
        $alert1 = $alertService->dispatch($project, 'drift_detected', 'critical', 'API Drift', 'Deprecations noted');
        $alert2 = $alertService->dispatch($project, 'opportunity_emerged', 'success', 'New Quick Win', 'Feature win');

        // Check index endpoint
        $response = $this->actingAs($user)->getJson(route('alerts.index'));
        $response->assertOk()
            ->assertJsonPath('unread_count', 2)
            ->assertJsonCount(2, 'alerts');

        // Mark single alert as read
        $markResponse = $this->actingAs($user)->postJson(route('alerts.read', $alert1));
        $markResponse->assertOk()
            ->assertJsonPath('unread_count', 1);

        $this->assertNotNull($alert1->fresh()->read_at);

        // Mark all as read
        $allResponse = $this->actingAs($user)->postJson(route('alerts.read_all'));
        $allResponse->assertOk()
            ->assertJsonPath('unread_count', 0);

        $this->assertEquals(0, $alertService->getUnreadCount($user));
    }

    public function test_user_cannot_read_another_users_alert(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $project = Project::create([
            'user_id' => $userA->id,
            'title' => 'Private FinTech App',
            'classification' => ProjectType::NEW_PRODUCT,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $alert = app(AlertService::class)->dispatch(
            $project,
            'drift_detected',
            'warning',
            'Drift Alert',
            'Message'
        );

        $response = $this->actingAs($userB)->postJson(route('alerts.read', $alert));
        $response->assertForbidden();
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Consent;

use App\Models\User;
use App\Modules\Consent\Enums\ConsentType;
use App\Modules\Consent\Services\ConsentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_record_and_update_consent(): void
    {
        $user = User::factory()->create();
        $service = app(ConsentService::class);

        // Initially no consent
        $this->assertFalse($service->hasConsent($user, ConsentType::ANALYTICS));

        // Grant consent
        $record = $service->recordConsent($user, ConsentType::ANALYTICS, true, '1.0', '192.168.1.1');
        $this->assertTrue($service->hasConsent($user, ConsentType::ANALYTICS));
        $this->assertTrue($record->granted);
        $this->assertNotNull($record->granted_at);
        $this->assertNull($record->revoked_at);

        // Revoke consent
        $service->revokeConsent($user, ConsentType::ANALYTICS);
        $this->assertFalse($service->hasConsent($user, ConsentType::ANALYTICS));
    }

    public function test_consent_service_returns_all_categories(): void
    {
        $user = User::factory()->create();
        $service = app(ConsentService::class);

        $service->recordConsent($user, ConsentType::AI_IMPROVEMENT, true);

        $consents = $service->getUserConsents($user);

        $this->assertArrayHasKey('analytics', $consents);
        $this->assertArrayHasKey('product_improvement', $consents);
        $this->assertArrayHasKey('ai_improvement', $consents);
        $this->assertArrayHasKey('marketing', $consents);

        $this->assertTrue($consents['ai_improvement']['granted']);
        $this->assertFalse($consents['marketing']['granted']);
    }

    public function test_privacy_controller_web_endpoints(): void
    {
        $user = User::factory()->create();

        // 1. Get privacy settings
        $response = $this->actingAs($user)->getJson('/settings/privacy');
        $response->assertOk()
            ->assertJsonStructure([
                'consents',
                'audit_history',
            ]);

        // 2. Update consent preference
        $updateResponse = $this->actingAs($user)->postJson('/settings/privacy/consent', [
            'consent_type' => 'product_improvement',
            'granted' => true,
            'version' => '1.0',
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('record.consent_type', 'product_improvement')
            ->assertJsonPath('record.granted', true);

        // 3. Validation rejection
        $invalidResponse = $this->actingAs($user)->postJson('/settings/privacy/consent', [
            'consent_type' => 'invalid_category',
            'granted' => true,
        ]);
        $invalidResponse->assertStatus(422);
    }
}

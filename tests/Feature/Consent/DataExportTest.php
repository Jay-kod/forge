<?php

declare(strict_types=1);

namespace Tests\Feature\Consent;

use App\Models\User;
use App\Modules\Consent\Enums\ConsentType;
use App\Modules\Consent\Services\ConsentService;
use App\Modules\Credits\Services\CreditService;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_export_full_data_package(): void
    {
        $user = User::factory()->create(['name' => 'Folake Adebayo', 'email' => 'folake@example.com']);

        // 1. Credit setup
        $creditService = app(CreditService::class);
        $creditService->grant($user, 150, description: 'Starter credit grant');

        // 2. Project setup
        $projectAction = app(\App\Modules\Projects\Actions\CreateProjectAction::class);
        $project = $projectAction->execute(
            $user,
            'Real-time optimization of inter-state freight in West Africa.',
            'Supply Chain Logistics Engine'
        );

        // 3. Privacy consent setup
        $consentService = app(ConsentService::class);
        $consentService->recordConsent($user, ConsentType::AI_IMPROVEMENT, true);

        // 4. Request GDPR data export
        $response = $this->actingAs($user)->get('/settings/privacy/export-data');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/json');
        $this->assertStringContainsString('attachment; filename="forge_user_data_export_', $response->headers->get('content-disposition'));

        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('export_metadata', $data);
        $this->assertArrayHasKey('user_profile', $data);
        $this->assertArrayHasKey('privacy_consents', $data);
        $this->assertArrayHasKey('credits_and_billing', $data);
        $this->assertArrayHasKey('projects', $data);

        $this->assertEquals($user->id, $data['user_profile']['id']);
        $this->assertEquals('Supply Chain Logistics Engine', $data['projects'][0]['title']);
        $this->assertEquals(150, $data['credits_and_billing']['current_balance']);
        $this->assertCount(1, $data['privacy_consents']);
    }

    public function test_guest_cannot_export_data(): void
    {
        $response = $this->get('/settings/privacy/export-data');
        $response->assertRedirect('/login');
    }
}

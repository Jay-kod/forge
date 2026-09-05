<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_route_is_rate_limited_to_5_per_minute(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $response = $this->get('/login');
            $response->assertStatus(200);
        }

        // 6th attempt should be throttled
        $response = $this->get('/login');
        $response->assertStatus(429);
    }

    public function test_export_route_is_rate_limited(): void
    {
        $user = User::factory()->create();
        $project = \App\Modules\Projects\Models\Project::create([
            'user_id' => $user->id,
            'title' => 'Export Rate Limit Project',
            'type' => 'new_product',
            'status' => \App\Modules\Projects\Enums\ProjectStatus::ACTIVE,
        ]);

        for ($i = 0; $i < 10; $i++) {
            $response = $this->actingAs($user)->get("/projects/{$project->id}/export/pdf");
            // Could be 200 or stream, but not 429
            $this->assertNotEquals(429, $response->getStatusCode());
        }

        // 11th should be throttled
        $response = $this->actingAs($user)->get("/projects/{$project->id}/export/pdf");
        $response->assertStatus(429);
    }
}

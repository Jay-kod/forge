<?php

declare(strict_types=1);

namespace Tests\Feature\System;

use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_healthz_endpoint_returns_ok_and_checks(): void
    {
        $response = $this->get('/healthz');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'checks' => [
                'database',
                'cache',
                'storage',
                'ai',
            ],
        ]);

        $this->assertEquals('ok', $response->json('status'));
        $this->assertEquals('ok', $response->json('checks.database'));
        $this->assertEquals('ok', $response->json('checks.cache'));
    }
}

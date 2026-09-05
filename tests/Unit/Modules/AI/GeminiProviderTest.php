<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\AI;

use App\Models\User;
use App\Modules\AI\DTOs\AIRequest;
use App\Modules\AI\Enums\WorkloadClass;
use App\Modules\AI\Providers\GeminiProvider;
use Tests\TestCase;

class GeminiProviderTest extends TestCase
{
    public function test_gemini_provider_identifier_and_availability(): void
    {
        $providerWithoutKey = new GeminiProvider('');
        $this->assertFalse($providerWithoutKey->isAvailable());

        $providerWithKey = new GeminiProvider('test-gemini-key-12345');
        $this->assertTrue($providerWithKey->isAvailable());
        $this->assertEquals('gemini', $providerWithKey->identifier());
        $this->assertTrue($providerWithKey->supports(WorkloadClass::LIGHT));
        $this->assertTrue($providerWithKey->supports(WorkloadClass::STANDARD));
        $this->assertTrue($providerWithKey->supports(WorkloadClass::DEEP));
        $this->assertTrue($providerWithKey->supports(WorkloadClass::EXTREME));
    }

    public function test_gemini_provider_estimate_and_completion(): void
    {
        $provider = new GeminiProvider('test-key');
        $user = new User();

        $reqLight = new AIRequest($user, 'Summarize user intent', 'intent.summary', WorkloadClass::LIGHT);
        $estLight = $provider->estimate($reqLight);
        $this->assertEquals(WorkloadClass::LIGHT->defaultCredits(), $estLight->credits);
        $this->assertEquals('gemini-2.5-pro', $estLight->recommendedModel);

        $resLight = $provider->complete($reqLight);
        $this->assertEquals('gemini', $resLight->provider);
        $this->assertEquals('gemini-2.5-flash', $resLight->model);
        $this->assertStringContainsString('Summarize user intent', $resLight->content);

        $reqStandard = new AIRequest($user, 'Synthesize PRD', 'prd.generate', WorkloadClass::STANDARD);
        $resStandard = $provider->complete($reqStandard);
        $this->assertEquals('gemini-2.5-pro', $resStandard->model);
    }
}

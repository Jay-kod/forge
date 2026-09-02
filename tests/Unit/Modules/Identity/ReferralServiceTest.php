<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Identity;

use App\Models\User;
use App\Modules\Credits\Actions\ConfirmCreditsAction;
use App\Modules\Credits\Actions\GrantCreditsAction;
use App\Modules\Credits\Actions\ReleaseCreditsAction;
use App\Modules\Credits\Actions\ReserveCreditsAction;
use App\Modules\Credits\Services\CreditService;
use App\Modules\Identity\Models\Referral;
use App\Modules\Identity\Services\ReferralService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CreditService $creditService;
    protected ReferralService $referralService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creditService = new CreditService(
            new GrantCreditsAction(),
            new ReserveCreditsAction(),
            new ConfirmCreditsAction(),
            new ReleaseCreditsAction()
        );
        $this->referralService = new ReferralService($this->creditService);
    }

    public function test_user_gets_auto_generated_referral_code(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->referral_code);
        $this->assertEquals(8, strlen($user->referral_code));
    }

    public function test_can_apply_valid_referral_code(): void
    {
        $referrer = User::factory()->create();
        $referee = User::factory()->create();

        $success = $this->referralService->applyReferralCode($referee, $referrer->referral_code);

        $this->assertTrue($success);
        $this->assertEquals($referrer->id, $referee->fresh()->referred_by_id);

        $referral = Referral::where('referrer_id', $referrer->id)
            ->where('referred_id', $referee->id)
            ->first();

        $this->assertNotNull($referral);
        $this->assertEquals('pending', $referral->status);
    }

    public function test_cannot_refer_oneself(): void
    {
        $user = User::factory()->create();

        $success = $this->referralService->applyReferralCode($user, $user->referral_code);

        $this->assertFalse($success);
        $this->assertNull($user->fresh()->referred_by_id);
    }

    public function test_activating_referral_grants_atomic_credits_to_both_parties(): void
    {
        $referrer = User::factory()->create();
        $referee = User::factory()->create();

        $this->creditService->grant($referrer, 10, 'seed');
        $this->creditService->grant($referee, 10, 'seed');

        $this->referralService->applyReferralCode($referee, $referrer->referral_code);

        // First project creation triggers activation
        $this->referralService->activateReferralOnFirstProject($referee);

        // Both parties should have received 50 bonus credits (10 + 50 = 60)
        $this->assertEquals(60, $this->creditService->getBalance($referrer));
        $this->assertEquals(60, $this->creditService->getBalance($referee));

        $referral = Referral::where('referrer_id', $referrer->id)
            ->where('referred_id', $referee->id)
            ->first();

        $this->assertEquals('activated', $referral->status);
        $this->assertNotNull($referral->activated_at);

        // Calling again should be idempotent and not grant additional credits
        $this->referralService->activateReferralOnFirstProject($referee);
        $this->assertEquals(60, $this->creditService->getBalance($referrer));
        $this->assertEquals(60, $this->creditService->getBalance($referee));
    }
}

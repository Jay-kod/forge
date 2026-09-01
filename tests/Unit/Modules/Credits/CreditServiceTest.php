<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Credits;

use App\Models\User;
use App\Modules\Credits\Actions\ConfirmCreditsAction;
use App\Modules\Credits\Actions\GrantCreditsAction;
use App\Modules\Credits\Actions\ReleaseCreditsAction;
use App\Modules\Credits\Actions\ReserveCreditsAction;
use App\Modules\Credits\Enums\TransactionType;
use App\Modules\Credits\Services\CreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CreditService $creditService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->creditService = new CreditService(
            new GrantCreditsAction(),
            new ReserveCreditsAction(),
            new ConfirmCreditsAction(),
            new ReleaseCreditsAction()
        );
    }

    public function test_can_grant_credits_to_user(): void
    {
        $user = User::factory()->create();

        $tx = $this->creditService->grant($user, 50, 'bonus', null, 'Welcome bonus');

        $this->assertEquals(TransactionType::GRANT, $tx->type);
        $this->assertEquals(50, $tx->amount);
        $this->assertEquals(50, $this->creditService->getBalance($user));
    }

    public function test_can_reserve_and_confirm_credits(): void
    {
        $user = User::factory()->create();
        $this->creditService->grant($user, 100);

        $reservation = $this->creditService->reserve($user, 20, 'ai_operation');

        $this->assertEquals(TransactionType::RESERVATION, $reservation->type);
        $this->assertEquals(-20, $reservation->amount);
        $this->assertEquals(80, $this->creditService->getBalance($user));

        $confirmation = $this->creditService->confirm($reservation);

        $this->assertEquals(TransactionType::CONSUMPTION, $confirmation->type);
        $this->assertEquals(80, $this->creditService->getBalance($user));
    }

    public function test_can_reserve_and_release_credits_on_failure(): void
    {
        $user = User::factory()->create();
        $this->creditService->grant($user, 100);

        $reservation = $this->creditService->reserve($user, 30, 'failed_operation');
        $this->assertEquals(70, $this->creditService->getBalance($user));

        $release = $this->creditService->release($reservation, 'Provider timeout');

        $this->assertEquals(TransactionType::RELEASE, $release->type);
        $this->assertEquals(30, $release->amount);
        $this->assertEquals(100, $this->creditService->getBalance($user));
    }
}

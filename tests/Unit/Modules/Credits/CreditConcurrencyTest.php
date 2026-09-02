<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Credits;

use App\Models\User;
use App\Modules\Credits\Actions\ConfirmCreditsAction;
use App\Modules\Credits\Actions\GrantCreditsAction;
use App\Modules\Credits\Actions\ReleaseCreditsAction;
use App\Modules\Credits\Actions\ReserveCreditsAction;
use App\Modules\Credits\Services\CreditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class CreditConcurrencyTest extends TestCase
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

    public function test_cannot_overdraw_credit_balance(): void
    {
        $user = User::factory()->create();
        $this->creditService->grant($user, 50, 'seed_grant');

        // First reservation of 30 succeeds (balance: 20)
        $res1 = $this->creditService->reserve($user, 30, 'job_1');
        $this->assertEquals(20, $this->creditService->getBalance($user));

        // Second reservation of 30 must fail because only 20 credits remain
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Insufficient credits balance');

        $this->creditService->reserve($user, 30, 'job_2');
    }

    public function test_multiple_rapid_reservations_maintain_balance_integrity(): void
    {
        $user = User::factory()->create();
        $this->creditService->grant($user, 100, 'batch_grant');

        $reservations = [];
        for ($i = 1; $i <= 5; $i++) {
            $reservations[] = $this->creditService->reserve($user, 20, "rapid_op_{$i}");
        }

        // 100 - (5 * 20) = 0
        $this->assertEquals(0, $this->creditService->getBalance($user));

        // Confirm 3, release 2
        $this->creditService->confirm($reservations[0]);
        $this->creditService->confirm($reservations[1]);
        $this->creditService->confirm($reservations[2]);

        $this->creditService->release($reservations[3], 'Failure op 4');
        $this->creditService->release($reservations[4], 'Failure op 5');

        // Balance should have refunded the 2 released reservations (2 * 20 = 40)
        $this->assertEquals(40, $this->creditService->getBalance($user));
    }
}

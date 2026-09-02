<?php

declare(strict_types=1);

namespace Tests\Feature\Billing;

use App\Models\User;
use App\Modules\Billing\Models\BillingEvent;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_checkout_session_completed_provisions_subscription_and_credits(): void
    {
        $user = User::factory()->create();
        $proPlan = Plan::where('slug', 'pro')->first();

        $eventId = 'evt_test_checkout_' . uniqid();
        $payload = [
            'id' => $eventId,
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_123',
                    'client_reference_id' => (string) $user->id,
                    'customer' => 'cus_test_abc',
                    'subscription' => 'sub_test_xyz',
                    'customer_email' => $user->email,
                    'metadata' => [
                        'user_id' => (string) $user->id,
                        'plan_id' => (string) $proPlan->id,
                    ],
                ],
            ],
        ];

        $response = $this->postJson(route('webhook.stripe'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'plan_id' => $proPlan->id,
            'stripe_customer_id' => 'cus_test_abc',
            'stripe_subscription_id' => 'sub_test_xyz',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('billing_events', [
            'stripe_event_id' => $eventId,
            'event_type' => 'checkout.session.completed',
        ]);

        // User should have received Pro plan monthly credits (200)
        $this->assertEquals(200, $user->creditAccount->fresh()->balance);
    }

    public function test_webhook_is_idempotent(): void
    {
        $user = User::factory()->create();
        $proPlan = Plan::where('slug', 'pro')->first();

        $eventId = 'evt_idempotent_test';
        $payload = [
            'id' => $eventId,
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_123',
                    'client_reference_id' => (string) $user->id,
                    'customer' => 'cus_test_abc',
                    'subscription' => 'sub_test_xyz',
                    'metadata' => ['user_id' => (string) $user->id, 'plan_id' => (string) $proPlan->id],
                ],
            ],
        ];

        // First call
        $res1 = $this->postJson(route('webhook.stripe'), $payload);
        $res1->assertStatus(200);
        $res1->assertJson(['status' => 'success']);

        // Second call with same event ID
        $res2 = $this->postJson(route('webhook.stripe'), $payload);
        $res2->assertStatus(200);
        $res2->assertJson(['status' => 'already_processed']);

        // Credits should only be granted once (200, not 400)
        $this->assertEquals(200, $user->creditAccount->fresh()->balance);
    }

    public function test_customer_subscription_deleted_marks_subscription_canceled(): void
    {
        $user = User::factory()->create();
        $proPlan = Plan::where('slug', 'pro')->first();

        $sub = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $proPlan->id,
            'status' => 'active',
            'stripe_customer_id' => 'cus_test_del',
            'stripe_subscription_id' => 'sub_test_del',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        $payload = [
            'id' => 'evt_del_' . uniqid(),
            'type' => 'customer.subscription.deleted',
            'data' => [
                'object' => [
                    'id' => 'sub_test_del',
                    'customer' => 'cus_test_del',
                    'status' => 'canceled',
                ],
            ],
        ];

        $response = $this->postJson(route('webhook.stripe'), $payload);
        $response->assertStatus(200);

        $sub->refresh();
        $this->assertEquals('canceled', $sub->status);
        $this->assertNotNull($sub->canceled_at);
    }

    public function test_invoice_payment_succeeded_allocates_monthly_credits(): void
    {
        $user = User::factory()->create();
        $proPlan = Plan::where('slug', 'pro')->first();

        Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $proPlan->id,
            'status' => 'active',
            'stripe_customer_id' => 'cus_test_inv',
            'stripe_subscription_id' => 'sub_test_inv',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);

        $payload = [
            'id' => 'evt_inv_' . uniqid(),
            'type' => 'invoice.payment_succeeded',
            'data' => [
                'object' => [
                    'id' => 'in_test_123',
                    'customer' => 'cus_test_inv',
                    'subscription' => 'sub_test_inv',
                    'paid' => true,
                ],
            ],
        ];

        $response = $this->postJson(route('webhook.stripe'), $payload);
        $response->assertStatus(200);

        // Credits granted for invoice payment (200)
        $this->assertEquals(200, $user->creditAccount->fresh()->balance);
    }
}

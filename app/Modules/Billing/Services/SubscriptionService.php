<?php

declare(strict_types=1);

namespace App\Modules\Billing\Services;

use App\Models\User;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Credits\Contracts\CreditServiceInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\BillingPortal\Session as PortalSession;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Stripe;

class SubscriptionService
{
    public function __construct(
        protected CreditServiceInterface $creditService
    ) {}

    /**
     * Auto-provision free tier plan and welcome credits on user signup.
     */
    public function provisionFreePlan(User $user): Subscription
    {
        return DB::transaction(function () use ($user) {
            $existing = Subscription::where('user_id', $user->id)
                ->whereIn('status', ['active', 'trialing'])
                ->first();

            if ($existing) {
                return $existing;
            }

            $freePlan = Plan::where('slug', 'free')->first();

            if (!$freePlan) {
                $freePlan = Plan::create([
                    'slug' => 'free',
                    'name' => 'Free Explorer',
                    'price_monthly' => 0.00,
                    'price_annual' => 0.00,
                    'credits_monthly' => 25,
                    'is_active' => true,
                    'features' => ['1 active workspace', 'Basic PRD generator'],
                ]);
            }

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $freePlan->id,
                'status' => 'active',
                'current_period_start' => now(),
                'current_period_end' => now()->addYear(),
            ]);

            // Grant initial free tier welcome credits if user has no credits
            if ($this->creditService->getBalance($user) === 0) {
                $this->creditService->grant(
                    user: $user,
                    amount: $freePlan->credits_monthly ?: 25,
                    referenceType: 'welcome_grant',
                    description: 'Initial Free Explorer allocation credits'
                );
            }

            return $subscription;
        });
    }

    /**
     * Create Stripe Checkout session for a subscription plan.
     */
    public function createCheckoutSession(User $user, Plan $plan, string $billingCycle = 'monthly'): string
    {
        $secret = config('services.stripe.secret');

        if (empty($secret)) {
            // Local fallback / test mode URL
            return route('projects.index', ['checkout' => 'mock_success', 'plan' => $plan->slug]);
        }

        Stripe::setApiKey($secret);

        $priceId = $billingCycle === 'annual'
            ? $plan->stripe_price_id_annual
            : $plan->stripe_price_id_monthly;

        $lineItems = [];

        if (!empty($priceId)) {
            $lineItems[] = [
                'price' => $priceId,
                'quantity' => 1,
            ];
        } else {
            // Fallback to inline price data if explicit Stripe Price ID is not pre-created
            $amount = $billingCycle === 'annual'
                ? (int) ($plan->price_annual * 100)
                : (int) ($plan->price_monthly * 100);

            $interval = $billingCycle === 'annual' ? 'year' : 'month';

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => "FORGE {$plan->name}",
                        'description' => "{$plan->credits_monthly} Intelligence Credits / month",
                    ],
                    'unit_amount' => $amount,
                    'recurring' => [
                        'interval' => $interval,
                    ],
                ],
                'quantity' => 1,
            ];
        }

        $session = CheckoutSession::create([
            'payment_method_types' => ['card'],
            'customer_email' => $user->email,
            'client_reference_id' => (string) $user->id,
            'line_items' => $lineItems,
            'mode' => 'subscription',
            'success_url' => route('projects.index') . '?checkout=success&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('pricing') . '?checkout=canceled',
            'metadata' => [
                'user_id' => (string) $user->id,
                'plan_id' => (string) $plan->id,
                'billing_cycle' => $billingCycle,
            ],
        ]);

        return $session->url ?? route('projects.index');
    }

    /**
     * Create Stripe Customer Billing Portal session for managing subscription.
     */
    public function createCustomerPortalSession(User $user): string
    {
        $secret = config('services.stripe.secret');
        $customerId = $user->subscription?->stripe_customer_id;

        if (empty($secret) || empty($customerId)) {
            return route('pricing');
        }

        Stripe::setApiKey($secret);

        $portalSession = PortalSession::create([
            'customer' => $customerId,
            'return_url' => route('pricing'),
        ]);

        return $portalSession->url ?? route('pricing');
    }

    /**
     * Process checkout.session.completed event.
     */
    public function handleCheckoutSessionCompleted(array $session): ?Subscription
    {
        $userId = $session['client_reference_id'] ?? $session['metadata']['user_id'] ?? null;
        $planId = $session['metadata']['plan_id'] ?? null;
        $customerId = $session['customer'] ?? null;
        $subscriptionId = $session['subscription'] ?? null;

        if (!$userId) {
            $user = User::where('email', $session['customer_email'] ?? '')->first();
            $userId = $user?->id;
        }

        if (!$userId) {
            Log::warning('Stripe checkout completed without identifiable user', ['session' => $session]);
            return null;
        }

        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        $plan = $planId ? Plan::find($planId) : Plan::where('slug', 'pro')->first();
        if (!$plan) {
            return null;
        }

        return DB::transaction(function () use ($user, $plan, $customerId, $subscriptionId) {
            $subscription = Subscription::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'stripe_customer_id' => $customerId,
                    'stripe_subscription_id' => $subscriptionId,
                    'current_period_start' => now(),
                    'current_period_end' => now()->addMonth(),
                    'canceled_at' => null,
                    'ends_at' => null,
                ]
            );

            // Grant initial credits for paid subscription
            $this->creditService->grant(
                user: $user,
                amount: $plan->credits_monthly,
                referenceType: 'subscription.initial_grant',
                referenceId: (string) $subscriptionId,
                description: "Monthly subscription credits for {$plan->name}"
            );

            return $subscription;
        });
    }

    /**
     * Process customer.subscription.created / updated event.
     */
    public function handleSubscriptionUpdated(array $data): ?Subscription
    {
        $subId = $data['id'] ?? null;
        if (!$subId) {
            return null;
        }

        $subscription = Subscription::where('stripe_subscription_id', $subId)->first();

        if (!$subscription) {
            // Attempt lookup by customer ID
            $customerId = $data['customer'] ?? null;
            if ($customerId) {
                $subscription = Subscription::where('stripe_customer_id', $customerId)->first();
            }
        }

        if (!$subscription) {
            return null;
        }

        $status = $data['status'] ?? $subscription->status;
        $periodStart = isset($data['current_period_start']) ? Carbon::createFromTimestamp($data['current_period_start']) : $subscription->current_period_start;
        $periodEnd = isset($data['current_period_end']) ? Carbon::createFromTimestamp($data['current_period_end']) : $subscription->current_period_end;
        $canceledAt = isset($data['canceled_at']) ? Carbon::createFromTimestamp($data['canceled_at']) : null;
        $endsAt = isset($data['cancel_at']) ? Carbon::createFromTimestamp($data['cancel_at']) : null;

        $subscription->update([
            'status' => $status,
            'current_period_start' => $periodStart,
            'current_period_end' => $periodEnd,
            'canceled_at' => $canceledAt,
            'ends_at' => $endsAt,
        ]);

        return $subscription;
    }

    /**
     * Process customer.subscription.deleted event.
     */
    public function handleSubscriptionDeleted(array $data): ?Subscription
    {
        $subId = $data['id'] ?? null;
        if (!$subId) {
            return null;
        }

        $subscription = Subscription::where('stripe_subscription_id', $subId)->first();
        if (!$subscription) {
            return null;
        }

        $freePlan = Plan::where('slug', 'free')->first();

        $subscription->update([
            'status' => 'canceled',
            'canceled_at' => now(),
            'ends_at' => now(),
            'plan_id' => $freePlan?->id ?? $subscription->plan_id,
        ]);

        return $subscription;
    }

    /**
     * Process invoice.payment_succeeded event for recurring credit allocation.
     */
    public function handleInvoicePaid(array $invoice): void
    {
        $subId = $invoice['subscription'] ?? null;
        if (!$subId) {
            return;
        }

        $subscription = Subscription::where('stripe_subscription_id', $subId)->first();
        if (!$subscription || !$subscription->user) {
            return;
        }

        $plan = $subscription->plan;
        if (!$plan || $plan->credits_monthly <= 0) {
            return;
        }

        // Avoid duplicate credit grants for the same invoice
        $invoiceId = $invoice['id'] ?? 'inv_' . uniqid();

        $this->creditService->grant(
            user: $subscription->user,
            amount: $plan->credits_monthly,
            referenceType: 'subscription.monthly_grant',
            referenceId: (string) $invoiceId,
            description: "Monthly recurring credit allocation for {$plan->name}"
        );
    }
}

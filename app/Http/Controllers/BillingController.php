<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Billing\Actions\StripeWebhookHandler;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class BillingController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected StripeWebhookHandler $stripeWebhookHandler
    ) {}

    public function pricing(Request $request): Response
    {
        $plans = Plan::where('is_active', true)
            ->with('entitlements')
            ->get();

        return Inertia::render('Billing/Pricing', [
            'plans' => $plans,
            'currentSubscription' => $request->user()?->subscription?->load('plan'),
        ]);
    }

    public function checkout(Request $request): SymfonyResponse
    {
        $validated = $request->validate([
            'plan_id' => ['required', 'exists:plans,id'],
            'billing_cycle' => ['nullable', 'string', 'in:monthly,annual'],
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $cycle = $validated['billing_cycle'] ?? 'monthly';

        $checkoutUrl = $this->subscriptionService->createCheckoutSession(
            $request->user(),
            $plan,
            $cycle
        );

        return Inertia::location($checkoutUrl);
    }

    public function portal(Request $request): SymfonyResponse
    {
        $portalUrl = $this->subscriptionService->createCustomerPortalSession($request->user());

        return Inertia::location($portalUrl);
    }

    public function webhook(Request $request): JsonResponse
    {
        $result = $this->stripeWebhookHandler->execute($request);

        return response()->json(['status' => $result['status']], $result['code']);
    }
}

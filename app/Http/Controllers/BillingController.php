<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Billing\Models\BillingEvent;
use App\Modules\Billing\Models\Plan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillingController extends Controller
{
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

    public function webhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $eventId = $payload['id'] ?? $request->header('stripe-signature') ?? 'evt_' . uniqid();

        // 1. Check idempotency
        if (BillingEvent::where('stripe_event_id', $eventId)->exists()) {
            return response()->json(['status' => 'already_processed'], 200);
        }

        // 2. Record billing event
        BillingEvent::create([
            'user_id' => null,
            'event_type' => $payload['type'] ?? 'unknown',
            'stripe_event_id' => $eventId,
            'payload' => $payload,
            'processed_at' => now(),
        ]);

        return response()->json(['status' => 'received'], 200);
    }
}

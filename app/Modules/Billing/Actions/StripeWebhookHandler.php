<?php

declare(strict_types=1);

namespace App\Modules\Billing\Actions;

use App\Modules\Billing\Models\BillingEvent;
use App\Modules\Billing\Services\SubscriptionService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookHandler
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    /**
     * Process Stripe Webhook payload with signature verification & idempotency.
     *
     * @return array{status: string, code: int}
     */
    public function execute(Request $request): array
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('stripe-signature') ?? '';
        $webhookSecret = config('services.stripe.webhook_secret');

        $event = null;

        if (!empty($webhookSecret) && !empty($sigHeader)) {
            try {
                $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
            } catch (SignatureVerificationException $e) {
                Log::warning('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
                return ['status' => 'invalid_signature', 'code' => 400];
            } catch (Exception $e) {
                Log::warning('Stripe webhook parsing failed', ['error' => $e->getMessage()]);
                return ['status' => 'invalid_payload', 'code' => 400];
            }
            $eventType = $event->type;
            $eventId = $event->id;
            $eventData = $event->data->object->toArray();
        } else {
            // Fallback for local dev/testing without signature enforcement
            $parsed = json_decode($payload, true) ?: $request->all();
            $eventType = $parsed['type'] ?? 'unknown';
            $eventId = $parsed['id'] ?? 'evt_' . uniqid();
            $eventData = $parsed['data']['object'] ?? $parsed;
        }

        // 1. Idempotency verification: deduplicate by Stripe event ID
        if (BillingEvent::where('stripe_event_id', $eventId)->exists()) {
            return ['status' => 'already_processed', 'code' => 200];
        }

        // 2. Record billing event
        BillingEvent::create([
            'user_id' => $eventData['metadata']['user_id'] ?? null,
            'event_type' => $eventType,
            'stripe_event_id' => $eventId,
            'payload' => $eventData,
            'processed_at' => now(),
        ]);

        // 3. Handle lifecycle based on event type
        try {
            switch ($eventType) {
                case 'checkout.session.completed':
                    $this->subscriptionService->handleCheckoutSessionCompleted($eventData);
                    break;

                case 'customer.subscription.created':
                case 'customer.subscription.updated':
                    $this->subscriptionService->handleSubscriptionUpdated($eventData);
                    break;

                case 'customer.subscription.deleted':
                    $this->subscriptionService->handleSubscriptionDeleted($eventData);
                    break;

                case 'invoice.payment_succeeded':
                    $this->subscriptionService->handleInvoicePaid($eventData);
                    break;

                default:
                    Log::info('Unhandled Stripe webhook event received', ['type' => $eventType]);
                    break;
            }
        } catch (Exception $e) {
            Log::error('Error processing Stripe webhook event', [
                'type' => $eventType,
                'event_id' => $eventId,
                'error' => $e->getMessage(),
            ]);
            return ['status' => 'processing_error', 'code' => 500];
        }

        return ['status' => 'success', 'code' => 200];
    }
}

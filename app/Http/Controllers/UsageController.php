<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Billing\Contracts\EntitlementServiceInterface;
use App\Modules\Credits\Models\CreditAccount;
use App\Modules\Credits\Models\CreditTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UsageController extends Controller
{
    public function __construct(
        protected EntitlementServiceInterface $entitlements
    ) {}

    /**
     * Display credit balance, usage history, and entitlement capacity.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $account = CreditAccount::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 25, 'lifetime_granted' => 25, 'lifetime_consumed' => 0]
        );

        $transactions = CreditTransaction::where('credit_account_id', $account->id)
            ->latest()
            ->paginate(15);

        $subscription = $user->subscription?->load('plan.entitlements');

        // Calculate usage breakdown by type or reference
        $consumedByReference = CreditTransaction::where('credit_account_id', $account->id)
            ->where('amount', '<', 0)
            ->selectRaw('reference_type, sum(abs(amount)) as total')
            ->groupBy('reference_type')
            ->pluck('total', 'reference_type');

        return Inertia::render('Usage/Index', [
            'account' => [
                'balance' => $account->balance,
                'lifetime_granted' => $account->lifetime_granted,
                'lifetime_consumed' => $account->lifetime_consumed,
            ],
            'transactions' => $transactions,
            'consumedByReference' => $consumedByReference,
            'subscription' => $subscription,
            'planName' => $subscription?->plan?->name ?? 'Free Tier',
            'entitlements' => [
                'project_limit' => $this->entitlements->getLimit($user, 'project.create'),
                'can_export_package' => $this->entitlements->can($user, 'export.package'),
                'can_export_growth' => $this->entitlements->can($user, 'export.growth_plan'),
                'can_automatic_workflow' => $this->entitlements->can($user, 'workflow.automatic'),
            ],
        ]);
    }
}

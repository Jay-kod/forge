<?php

declare(strict_types=1);

namespace App\Modules\Billing\Services;

use App\Models\User;
use App\Modules\Billing\Contracts\EntitlementServiceInterface;
use App\Modules\Billing\Models\Plan;
use Illuminate\Support\Facades\Cache;

class EntitlementService implements EntitlementServiceInterface
{
    public function can(User $user, string $capability): bool
    {
        $plan = $this->resolveUserPlan($user);

        if (!$plan) {
            return $this->checkDefaultFreePlan($capability);
        }

        $entitlement = $plan->entitlements()->where('capability', $capability)->first();

        if (!$entitlement) {
            return false;
        }

        return $entitlement->value === 'true' || str_starts_with($entitlement->value, 'limit:') || $entitlement->value === 'unlimited';
    }

    public function getLimit(User $user, string $capability): ?int
    {
        $plan = $this->resolveUserPlan($user);

        if (!$plan) {
            return $capability === 'project.create' ? 1 : null;
        }

        $entitlement = $plan->entitlements()->where('capability', $capability)->first();

        if (!$entitlement) {
            return 0;
        }

        if ($entitlement->value === 'unlimited' || $entitlement->value === 'true') {
            return null; // No limit
        }

        if (str_starts_with($entitlement->value, 'limit:')) {
            return (int) substr($entitlement->value, 6);
        }

        return 0;
    }

    protected function resolveUserPlan(User $user): ?Plan
    {
        $subscription = $user->subscription;

        if ($subscription && $subscription->isActive()) {
            return $subscription->plan;
        }

        return Plan::where('slug', 'free')->first();
    }

    protected function checkDefaultFreePlan(string $capability): bool
    {
        $freeCapabilities = [
            'project.create',
            'project.archive',
            'research.basic',
            'prd.generate',
            'workflow.page_by_page',
        ];

        return in_array($capability, $freeCapabilities, true);
    }
}

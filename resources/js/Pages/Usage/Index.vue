<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps<{
    account: {
        balance: number;
        lifetime_granted: number;
        lifetime_consumed: number;
    };
    transactions: {
        data: Array<{
            id: number;
            type: string;
            amount: number;
            balance_after: number;
            reference_type: string | null;
            description: string | null;
            created_at: string;
        }>;
        links: any[];
    };
    consumedByReference: Record<string, number>;
    subscription: any;
    planName: string;
    entitlements: {
        project_limit: number | null;
        can_export_package: boolean;
        can_export_growth: boolean;
        can_automatic_workflow: boolean;
    };
}>();
</script>

<template>
    <AppLayout>
        <Head title="Usage & Capacity — FORGE" />

        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-primary">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-display font-extrabold tracking-tight text-text-primary">
                        Usage & Capacity
                    </h1>
                    <p class="text-xs sm:text-sm text-text-secondary mt-1">
                        Track atomic credit consumption, workload breakdown, and your current plan entitlements.
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <Link
                        :href="route('billing.index')"
                        class="px-4 py-2 rounded-xl brand-button text-xs font-bold shadow-xs inline-flex items-center gap-2"
                    >
                        <span>Manage Subscription &rarr;</span>
                    </Link>
                </div>
            </div>

            <!-- KPI Metric Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6">
                <div class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-xs">
                    <span class="text-xs font-mono uppercase tracking-wider text-text-tertiary block mb-1">Available Balance</span>
                    <div class="text-3xl font-display font-extrabold text-emerald-500 dark:text-emerald-400 font-mono">
                        ⚡ {{ account.balance }}
                    </div>
                    <span class="text-[11px] font-mono text-text-secondary mt-1 block">
                        Estimated capacity: ~{{ Math.floor(account.balance / 15) }} stage analyses
                    </span>
                </div>

                <div class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-xs">
                    <span class="text-xs font-mono uppercase tracking-wider text-text-tertiary block mb-1">Lifetime Consumed</span>
                    <div class="text-3xl font-display font-extrabold text-indigo-400 font-mono">
                        {{ account.lifetime_consumed }}
                    </div>
                    <span class="text-[11px] font-mono text-text-secondary mt-1 block">
                        Deducted on verified execution
                    </span>
                </div>

                <div class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-xs">
                    <span class="text-xs font-mono uppercase tracking-wider text-text-tertiary block mb-1">Active Plan</span>
                    <div class="text-2xl font-display font-extrabold text-text-primary truncate">
                        {{ planName }}
                    </div>
                    <span class="text-[11px] font-mono text-text-secondary mt-1 block">
                        Total Granted: {{ account.lifetime_granted }} credits
                    </span>
                </div>
            </div>

            <!-- Plan Entitlements vs Credit Consumption Distinction -->
            <div class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-primary">
                    <h2 class="text-sm font-bold text-text-primary">Plan Capabilities & Entitlements</h2>
                    <span class="text-xs font-mono text-text-tertiary">Server-Enforced</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="p-4 rounded-xl bg-surface-primary border border-primary space-y-1">
                        <span class="text-[10px] font-mono uppercase text-text-tertiary block">Active Projects Limit</span>
                        <div class="text-sm font-bold text-text-primary">
                            {{ entitlements.project_limit === null ? 'Unlimited' : `${entitlements.project_limit} Projects` }}
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-surface-primary border border-primary space-y-1">
                        <span class="text-[10px] font-mono uppercase text-text-tertiary block">Autonomous Workflows</span>
                        <div class="text-sm font-bold" :class="entitlements.can_automatic_workflow ? 'text-emerald-400' : 'text-text-tertiary'">
                            {{ entitlements.can_automatic_workflow ? '✓ Enabled' : 'Requires Pro' }}
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-surface-primary border border-primary space-y-1">
                        <span class="text-[10px] font-mono uppercase text-text-tertiary block">AI Package Zip Export</span>
                        <div class="text-sm font-bold" :class="entitlements.can_export_package ? 'text-emerald-400' : 'text-text-tertiary'">
                            {{ entitlements.can_export_package ? '✓ Full Zip Package' : 'Requires Pro' }}
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-surface-primary border border-primary space-y-1">
                        <span class="text-[10px] font-mono uppercase text-text-tertiary block">Growth Plan PDF</span>
                        <div class="text-sm font-bold" :class="entitlements.can_export_growth ? 'text-emerald-400' : 'text-text-tertiary'">
                            {{ entitlements.can_export_growth ? '✓ Executive PDF' : 'Requires Pro' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transactions Ledger -->
            <div class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-primary">
                    <h2 class="text-sm font-bold text-text-primary">Transaction History</h2>
                    <span class="text-xs font-mono text-text-tertiary">Atomic Operations</span>
                </div>

                <div v-if="transactions.data.length > 0" class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-primary/50 text-text-tertiary font-mono uppercase text-[10px]">
                                <th class="pb-2">Date</th>
                                <th class="pb-2">Operation / Description</th>
                                <th class="pb-2">Reference</th>
                                <th class="pb-2 text-right">Amount</th>
                                <th class="pb-2 text-right">Balance After</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary/40 font-mono">
                            <tr v-for="tx in transactions.data" :key="tx.id" class="hover:bg-surface-tertiary/40">
                                <td class="py-2.5 text-text-secondary whitespace-nowrap">
                                    {{ new Date(tx.created_at).toLocaleDateString() }}
                                </td>
                                <td class="py-2.5 text-text-primary font-sans font-medium">
                                    {{ tx.description || tx.type }}
                                </td>
                                <td class="py-2.5 text-text-tertiary">
                                    {{ tx.reference_type || 'N/A' }}
                                </td>
                                <td class="py-2.5 text-right font-bold" :class="tx.amount > 0 ? 'text-emerald-400' : 'text-text-primary'">
                                    {{ tx.amount > 0 ? `+${tx.amount}` : tx.amount }}
                                </td>
                                <td class="py-2.5 text-right text-text-secondary">
                                    {{ tx.balance_after }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="text-center py-8 text-xs text-text-secondary">
                    No transactions recorded yet.
                </div>
            </div>
        </div>
    </AppLayout>
</template>

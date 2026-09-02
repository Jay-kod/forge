<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { SharedProps } from '@/types';

interface Plan {
    id: number;
    slug: string;
    name: string;
    price_monthly: number | string;
    price_annual: number | string;
    credits_monthly: number;
    features: string[];
    is_active: boolean;
}

const props = defineProps<{
    plans?: Plan[];
    currentSubscription?: {
        id: number;
        status: string;
        plan?: Plan;
    } | null;
}>();

const page = usePage<SharedProps>();
const billingCycle = ref<'monthly' | 'annual'>('monthly');
const isCheckingOut = ref<number | null>(null);

const handleCheckout = (plan: Plan) => {
    if (!page.props.auth.user) {
        router.get(route('login'));
        return;
    }

    if (plan.slug === 'free') {
        return;
    }

    isCheckingOut.value = plan.id;
    router.post(route('billing.checkout'), {
        plan_id: plan.id,
        billing_cycle: billingCycle.value,
    }, {
        onFinish: () => { isCheckingOut.value = null; },
    });
};

const openPortal = () => {
    window.location.href = route('billing.portal');
};

const creditPacks = [
    { credits: 50, price: '$5', perCredit: '$0.10 / credit' },
    { credits: 200, price: '$18', perCredit: '$0.09 / credit', popular: true },
    { credits: 500, price: '$40', perCredit: '$0.08 / credit' },
];
</script>

<template>
    <AppLayout>
        <Head title="Plans & Credits — FORGE" />

        <!-- Header -->
        <div class="text-center max-w-3xl mx-auto mb-10">
            <span class="text-xs font-mono font-bold uppercase tracking-widest text-indigo-400 mb-2 block">
                Transparent SaaS Billing
            </span>
            <h1 class="text-3xl sm:text-4xl font-display font-extrabold tracking-tight text-text-primary mb-3">
                Plans Control Access. Credits Control Intelligence.
            </h1>
            <p class="text-xs sm:text-sm text-text-secondary max-w-xl mx-auto leading-relaxed">
                No hidden surprises. Every AI operation displays estimated credits before execution with guaranteed atomic refunds on failure.
            </p>

            <!-- Billing Cycle Switcher -->
            <div class="inline-flex items-center p-1 rounded-xl bg-surface-secondary border border-primary mt-6">
                <button
                    type="button"
                    @click="billingCycle = 'monthly'"
                    class="px-4 py-1.5 rounded-lg text-xs font-mono font-semibold transition-all"
                    :class="billingCycle === 'monthly' ? 'bg-surface-elevated text-text-primary shadow-xs border border-primary' : 'text-text-tertiary hover:text-text-primary'"
                >
                    Monthly Billing
                </button>
                <button
                    type="button"
                    @click="billingCycle = 'annual'"
                    class="px-4 py-1.5 rounded-lg text-xs font-mono font-semibold transition-all flex items-center gap-1.5"
                    :class="billingCycle === 'annual' ? 'bg-surface-elevated text-text-primary shadow-xs border border-primary' : 'text-text-tertiary hover:text-text-primary'"
                >
                    <span>Annual Billing</span>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                        Save 20%
                    </span>
                </button>
            </div>

            <!-- Manage Subscription Button if active paid user -->
            <div v-if="currentSubscription?.plan && currentSubscription.plan.slug !== 'free'" class="mt-4">
                <button
                    @click="openPortal"
                    class="text-xs font-mono text-indigo-400 hover:underline inline-flex items-center gap-1"
                >
                    <span>Manage Active Subscription (Stripe Portal) ↗</span>
                </button>
            </div>
        </div>

        <!-- Plan Cards Matrix -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto mb-16">
            <div
                v-for="plan in (plans || [])"
                :key="plan.id"
                class="flex flex-col bg-surface-secondary border rounded-2xl p-8 relative transition-all"
                :class="plan.slug === 'pro' ? 'border-indigo-500 shadow-xl ring-1 ring-indigo-500/30' : 'border-primary shadow-md'"
            >
                <!-- Popular Badge -->
                <div
                    v-if="plan.slug === 'pro'"
                    class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full bg-indigo-600 text-white text-[10px] font-mono font-bold uppercase tracking-wider shadow-sm"
                >
                    Most Popular
                </div>

                <!-- Current Plan Badge -->
                <div
                    v-if="currentSubscription?.plan?.id === plan.id"
                    class="mb-2 inline-flex items-center gap-1 self-start px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase bg-emerald-500/15 text-emerald-400 border border-emerald-500/30"
                >
                    ✓ Current Plan
                </div>

                <h2 class="text-xl font-display font-bold text-text-primary mb-1">{{ plan.name }}</h2>

                <!-- Price -->
                <div class="my-4">
                    <span class="text-4xl font-display font-extrabold text-text-primary">
                        ${{ billingCycle === 'annual' ? plan.price_annual : plan.price_monthly }}
                    </span>
                    <span class="text-xs text-text-tertiary ml-1 font-mono">
                        / {{ billingCycle === 'annual' ? 'year' : 'month' }}
                    </span>
                </div>

                <!-- Credits Badge -->
                <div class="px-3 py-1.5 rounded-xl bg-surface-primary border border-primary text-xs font-mono text-indigo-400 font-bold mb-6 flex items-center gap-2">
                    <span>⚡</span>
                    <span>{{ plan.credits_monthly }} Credits / month</span>
                </div>

                <!-- Features List -->
                <ul class="space-y-3 text-xs text-text-secondary mb-8 flex-1">
                    <li v-for="feat in plan.features" :key="feat" class="flex items-start gap-2">
                        <span class="text-emerald-400 font-bold shrink-0">✓</span>
                        <span class="leading-relaxed">{{ feat }}</span>
                    </li>
                </ul>

                <!-- CTA Button -->
                <button
                    v-if="currentSubscription?.plan?.id === plan.id"
                    disabled
                    class="w-full py-2.5 rounded-xl text-xs font-mono font-bold bg-surface-tertiary text-text-tertiary border border-primary cursor-default"
                >
                    Current Plan Active
                </button>
                <button
                    v-else-if="plan.slug === 'free'"
                    disabled
                    class="w-full py-2.5 rounded-xl text-xs font-mono font-bold bg-surface-tertiary text-text-tertiary border border-primary"
                >
                    Included Default
                </button>
                <button
                    v-else
                    @click="handleCheckout(plan)"
                    :disabled="isCheckingOut === plan.id"
                    class="w-full py-2.5 rounded-xl text-xs font-bold transition-all shadow-md"
                    :class="plan.slug === 'pro' ? 'brand-button' : 'bg-surface-tertiary border border-primary hover:bg-surface-elevated text-text-primary'"
                >
                    <span v-if="isCheckingOut === plan.id">Connecting to Stripe...</span>
                    <span v-else>Upgrade to {{ plan.name }}</span>
                </button>
            </div>
        </div>

        <!-- Credit Top-Ups Section -->
        <div class="max-w-4xl mx-auto bg-surface-secondary border border-primary rounded-2xl p-8 shadow-sm">
            <div class="text-center mb-6">
                <h2 class="text-xl font-display font-bold text-text-primary mb-1">Need Extra AI Credits?</h2>
                <p class="text-xs text-text-secondary">
                    Purchase on-demand credit packs for deep intelligence workloads without changing your tier.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div
                    v-for="pack in creditPacks"
                    :key="pack.credits"
                    class="p-5 rounded-xl bg-surface-primary border border-primary flex flex-col items-center text-center relative"
                >
                    <span class="text-2xl font-display font-extrabold text-text-primary mb-1">
                        {{ pack.credits }} Credits
                    </span>
                    <span class="text-lg font-bold text-indigo-400 mb-1">{{ pack.price }}</span>
                    <span class="text-[11px] font-mono text-text-tertiary mb-4">{{ pack.perCredit }}</span>
                    <button class="w-full py-1.5 rounded-lg bg-surface-tertiary hover:bg-surface-elevated border border-primary text-xs font-mono font-semibold transition-colors text-text-primary">
                        + Add Credits
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps<{
    plans?: any[];
    currentSubscription?: any;
}>();

const tiers = [
    {
        name: 'Free Explorer',
        price: '$0',
        period: 'forever',
        credits: '25 credits / month',
        description: 'Ideal for testing ideas and exploring the FORGE intelligence loop.',
        features: [
            '1 active workspace',
            'Basic situation classification',
            'Core market discovery report',
            'Basic PRD generator',
            'Watermarked PDF export',
            'Community support',
        ],
        cta: 'Current Plan',
        highlight: false,
    },
    {
        name: 'Pro Builder',
        price: '$39',
        period: 'per month',
        credits: '200 credits / month',
        description: 'For serious founders, vibe coders, and developers seeking validated strategy & architecture.',
        features: [
            'Unlimited active workspaces',
            'Deep multi-source market research',
            'Full competitor intelligence matrix',
            'Evidence-linked PRD & Architecture',
            'AI Development Package export (AGENTS.md, CLAUDE.md)',
            'Clean PDF blueprint exports',
            'Continuous opportunity tracking',
            'Priority AI reasoning models',
        ],
        cta: 'Upgrade to Pro',
        highlight: true,
    },
    {
        name: 'Business & Team',
        price: '$99',
        period: 'per month',
        credits: '500 credits / month',
        description: 'For startups, digital agencies, and product teams building multiple ventures.',
        features: [
            'Everything in Pro Builder',
            'Up to 5 collaborative team seats',
            'Shared workspace & credit pools',
            'Website & codebase audit workflows',
            'Digital transformation roadmaps',
            'Export to GitHub repositories',
            'Priority email & Slack support',
        ],
        cta: 'Upgrade to Business',
        highlight: false,
    },
];

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
        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="text-xs font-mono font-bold uppercase tracking-widest text-indigo-400 mb-2 block">
                Transparent SaaS Billing
            </span>
            <h1 class="text-3xl sm:text-4xl font-display font-extrabold tracking-tight text-text-primary mb-3">
                Plans Control Access. Credits Control Intelligence.
            </h1>
            <p class="text-sm text-text-secondary max-w-xl mx-auto">
                No hidden costs. Every expensive AI operation displays estimated credits before execution with automatic failure refunds.
            </p>
        </div>

        <!-- Plan Cards Matrix -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto mb-16">
            <div
                v-for="tier in tiers"
                :key="tier.name"
                class="flex flex-col bg-surface-secondary border rounded-2xl p-8 relative transition-all"
                :class="tier.highlight ? 'border-indigo-500 shadow-xl ring-1 ring-indigo-500/30' : 'border-primary shadow-md'"
            >
                <div v-if="tier.highlight" class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full bg-indigo-600 text-white text-[10px] font-mono font-bold uppercase tracking-wider">
                    Most Popular
                </div>

                <h2 class="text-lg font-display font-bold text-text-primary mb-1">{{ tier.name }}</h2>
                <p class="text-xs text-text-secondary mb-6 min-h-[32px]">{{ tier.description }}</p>

                <div class="mb-4">
                    <span class="text-4xl font-display font-extrabold text-text-primary">{{ tier.price }}</span>
                    <span class="text-xs text-text-tertiary ml-1 font-mono">/ {{ tier.period }}</span>
                </div>

                <div class="px-3 py-1.5 rounded-lg bg-surface-primary border border-primary text-xs font-mono text-indigo-400 font-semibold mb-6 flex items-center gap-2">
                    <span>⚡</span>
                    <span>{{ tier.credits }}</span>
                </div>

                <!-- Features List -->
                <ul class="space-y-3 text-xs text-text-secondary mb-8 flex-1">
                    <li v-for="feat in tier.features" :key="feat" class="flex items-start gap-2">
                        <span class="text-emerald-400 font-bold shrink-0">✓</span>
                        <span>{{ feat }}</span>
                    </li>
                </ul>

                <button
                    class="w-full py-2.5 rounded-xl text-xs font-bold transition-all shadow-md"
                    :class="tier.highlight ? 'brand-button' : 'bg-surface-tertiary border border-primary hover:bg-surface-elevated text-text-primary'"
                >
                    {{ tier.cta }}
                </button>
            </div>
        </div>

        <!-- Credit Top-Ups Section -->
        <div class="max-w-4xl mx-auto bg-surface-secondary border border-primary rounded-2xl p-8">
            <div class="text-center mb-6">
                <h2 class="text-xl font-display font-bold text-text-primary mb-1">Need Extra AI Credits?</h2>
                <p class="text-xs text-text-secondary">
                    Purchase on-demand credit packs that never expire while your plan remains active.
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
                    <button class="w-full py-1.5 rounded-lg bg-surface-tertiary hover:bg-surface-elevated border border-primary text-xs font-semibold transition-colors text-text-primary">
                        Add Credits
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

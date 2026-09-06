<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const guides = [
    {
        title: 'Getting Started with FORGE',
        icon: '🚀',
        summary: 'Learn how to describe your idea, website, or existing codebase and let FORGE automatically classify the situation.',
        points: [
            'Input plain language: No technical PRD jargon required.',
            'Choose between Guided (stage-by-stage approval) and Autonomous execution.',
            'Context model is initialized with unified situation classification.',
        ],
    },
    {
        title: 'How Continuous Intelligence Works',
        icon: '⚡',
        summary: 'FORGE never creates content in a vacuum. Every recommendation is synthesized from verifiable web citations and codebase analysis.',
        points: [
            'Traceable Research: Citations are crawled and verified in real-time.',
            'Confidence Levels: Categorized from Verified to Conflicting.',
            'Opportunity Graph: Connects goals, competitors, risks, and technical debt.',
        ],
    },
    {
        title: 'Projects as Living Workspaces',
        icon: '📁',
        summary: 'Each project contains 10 progressive workflow stages from Understanding to Final Architecture packages.',
        points: [
            'Stages require review and approval before advancing.',
            'Versions preserve every run with full visual diff comparison.',
            'Continuous drift monitors alert when competitors or websites change.',
        ],
    },
    {
        title: 'GitHub Integration & Permissions',
        icon: '🐙',
        summary: 'Audit code quality, scan frameworks, and export blueprints directly to isolated branches.',
        points: [
            'Strict separation: Login identity does not grant repository write access.',
            'Zero destructive operations: Exports always target dedicated branches or PRs.',
            'Technical debt scores and architectural opportunities are mapped automatically.',
        ],
    },
    {
        title: 'Credits & Billing Model',
        icon: '💳',
        summary: 'Understand atomic credit reservation, refunds on failure, and plan entitlement tiers.',
        points: [
            'Reserve before consuming: No credits lost on network or model errors.',
            'Stage execution typically consumes 10-15 credits.',
            'Upgrading to Pro unlocks automated multi-stage pipelines and full zip packages.',
        ],
    },
    {
        title: 'Privacy & Data Governance',
        icon: '🔒',
        summary: 'Your intellectual property and codebase are protected under strict security controls.',
        points: [
            'Never train public models on your proprietary prompts or repository files.',
            'Full GDPR export and account deletion available in Settings.',
            'Bring Your Own Key (BYOK) allows routing workloads to your own API accounts.',
        ],
    },
];
</script>

<template>
    <AppLayout>
        <Head title="Help & Documentation — FORGE" />

        <div class="max-w-5xl mx-auto space-y-8">
            <!-- Header -->
            <div class="text-center space-y-2 pb-6 border-b border-primary">
                <span class="text-xs font-mono uppercase text-indigo-400 font-bold tracking-wider">
                    Knowledge Base & Architecture
                </span>
                <h1 class="text-3xl sm:text-4xl font-display font-extrabold tracking-tight text-text-primary">
                    How FORGE Works
                </h1>
                <p class="text-xs sm:text-sm text-text-secondary max-w-xl mx-auto leading-relaxed">
                    Guides, architectural principles, and answers to common questions about using FORGE.
                </p>
            </div>

            <!-- Guides Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div
                    v-for="guide in guides"
                    :key="guide.title"
                    class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-xs space-y-3"
                >
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">{{ guide.icon }}</span>
                        <h2 class="text-base font-bold text-text-primary">{{ guide.title }}</h2>
                    </div>

                    <p class="text-xs text-text-secondary leading-relaxed">
                        {{ guide.summary }}
                    </p>

                    <ul class="space-y-1.5 pt-2 border-t border-primary/50 text-xs text-text-secondary font-mono">
                        <li v-for="pt in guide.points" :key="pt" class="flex items-start gap-2">
                            <span class="text-emerald-500 shrink-0">✓</span>
                            <span>{{ pt }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Support / Contact Footer Banner -->
            <div class="p-6 rounded-2xl bg-surface-secondary border border-primary flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-sm font-bold text-text-primary">Need more guidance?</h3>
                    <p class="text-xs text-text-secondary mt-0.5">
                        Inspect our core architecture documents or contact engineering support.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('discover')" class="px-4 py-2 rounded-xl brand-button text-xs font-bold shadow-xs">
                        Start New Discovery &rarr;
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

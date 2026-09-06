<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { Opportunity, Recommendation } from '@/types';

const props = defineProps<{
    isDeveloper: boolean;
    technicalLevel: string;
    recommendations: (Recommendation & { opportunity?: { project?: { id: number; title: string } } })[];
    businessOpportunities: Opportunity[];
    technicalOpportunities: Opportunity[];
    repositoryAudits: any[];
    websiteAudits: any[];
}>();

const mode = ref<'business' | 'developer'>(props.isDeveloper ? 'developer' : 'business');
</script>

<template>
    <AppLayout>
        <Head title="Proactive Growth Center — FORGE" />

        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Header & Lens Toggle -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-primary">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-display font-extrabold tracking-tight text-text-primary">
                        Proactive Growth Center
                    </h1>
                    <p class="text-xs sm:text-sm text-text-secondary mt-1">
                        Continuous opportunities, technical debt reduction, and strategic growth tailored to your objective.
                    </p>
                </div>

                <!-- Strategic Lens Switcher -->
                <div class="flex items-center gap-1.5 p-1 rounded-2xl bg-surface-secondary border border-primary shrink-0">
                    <button
                        @click="mode = 'business'"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors flex items-center gap-1.5"
                        :class="mode === 'business' ? 'bg-emerald-600 text-white shadow-xs' : 'text-text-secondary hover:text-text-primary'"
                    >
                        <span>💼</span>
                        <span>Business & Market</span>
                    </button>
                    <button
                        @click="mode = 'developer'"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-colors flex items-center gap-1.5"
                        :class="mode === 'developer' ? 'bg-indigo-600 text-white shadow-xs' : 'text-text-secondary hover:text-text-primary'"
                    >
                        <span>⚡</span>
                        <span>Technical & Architecture</span>
                    </button>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- LENS 1: BUSINESS GROWTH & REVENUE                             -->
            <!-- ============================================================= -->
            <div v-if="mode === 'business'" class="space-y-8">
                <!-- KPI Focus -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-surface-secondary border border-primary rounded-2xl p-5">
                        <span class="text-xs font-mono uppercase text-text-tertiary block mb-1">Market Vectors</span>
                        <div class="text-2xl font-display font-bold text-text-primary">{{ businessOpportunities.length }}</div>
                        <span class="text-[11px] text-text-secondary mt-1 block">Active expansion vectors identified</span>
                    </div>
                    <div class="bg-surface-secondary border border-primary rounded-2xl p-5">
                        <span class="text-xs font-mono uppercase text-text-tertiary block mb-1">Conversion Audits</span>
                        <div class="text-2xl font-display font-bold text-emerald-400">{{ websiteAudits.length }}</div>
                        <span class="text-[11px] text-text-secondary mt-1 block">Website conversion analyses</span>
                    </div>
                    <div class="bg-surface-secondary border border-primary rounded-2xl p-5">
                        <span class="text-xs font-mono uppercase text-text-tertiary block mb-1">Target Persona</span>
                        <div class="text-2xl font-display font-bold text-indigo-400 capitalize">
                            {{ technicalLevel.replace('_', ' ') }}
                        </div>
                        <span class="text-[11px] text-text-secondary mt-1 block">Tailored recommendations</span>
                    </div>
                </div>

                <!-- Business Opportunities Grid -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-text-primary">Market & Revenue Opportunities</h2>
                        <Link :href="route('opportunities.index', { category: 'growth' })" class="text-xs font-mono text-indigo-400 hover:underline">
                            View all &rarr;
                        </Link>
                    </div>

                    <div v-if="businessOpportunities.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-for="opp in businessOpportunities"
                            :key="opp.id"
                            class="p-5 rounded-2xl bg-surface-secondary border border-primary space-y-2.5"
                        >
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                    {{ opp.category }}
                                </span>
                                <span class="text-xs font-mono text-text-tertiary">Impact: {{ opp.impact }}</span>
                            </div>
                            <h3 class="text-sm font-bold text-text-primary">{{ opp.title }}</h3>
                            <p class="text-xs text-text-secondary leading-relaxed line-clamp-2">
                                {{ opp.description }}
                            </p>
                        </div>
                    </div>

                    <div v-else class="p-8 text-center bg-surface-secondary border border-dashed border-primary rounded-2xl text-xs text-text-secondary">
                        No business growth vectors logged yet.
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- LENS 2: DEVELOPER & ARCHITECTURE HEALTH                       -->
            <!-- ============================================================= -->
            <div v-else class="space-y-8">
                <!-- KPI Focus -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-surface-secondary border border-primary rounded-2xl p-5">
                        <span class="text-xs font-mono uppercase text-text-tertiary block mb-1">Architecture Vectors</span>
                        <div class="text-2xl font-display font-bold text-text-primary">{{ technicalOpportunities.length }}</div>
                        <span class="text-[11px] text-text-secondary mt-1 block">Refactoring & performance items</span>
                    </div>
                    <div class="bg-surface-secondary border border-primary rounded-2xl p-5">
                        <span class="text-xs font-mono uppercase text-text-tertiary block mb-1">Audited Repositories</span>
                        <div class="text-2xl font-display font-bold text-indigo-400">{{ repositoryAudits.length }}</div>
                        <span class="text-[11px] text-text-secondary mt-1 block">Connected GitHub codebases</span>
                    </div>
                    <div class="bg-surface-secondary border border-primary rounded-2xl p-5">
                        <span class="text-xs font-mono uppercase text-text-tertiary block mb-1">Average Code Health</span>
                        <div class="text-2xl font-display font-bold text-emerald-400 font-mono">
                            {{ repositoryAudits.length > 0 ? Math.round(repositoryAudits.reduce((acc, a) => acc + (a.code_health_score || 85), 0) / repositoryAudits.length) : 85 }}/100
                        </div>
                        <span class="text-[11px] text-text-secondary mt-1 block">Quality & maintainability</span>
                    </div>
                </div>

                <!-- Technical Opportunities & Debt -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-text-primary">Technical Debt & Architecture Vectors</h2>
                        <Link :href="route('opportunities.index', { category: 'technical' })" class="text-xs font-mono text-indigo-400 hover:underline">
                            View all &rarr;
                        </Link>
                    </div>

                    <div v-if="technicalOpportunities.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div
                            v-for="opp in technicalOpportunities"
                            :key="opp.id"
                            class="p-5 rounded-2xl bg-surface-secondary border border-primary space-y-2.5"
                        >
                            <div class="flex items-center justify-between">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold bg-indigo-500/15 text-indigo-400 border border-indigo-500/30">
                                    {{ opp.category }}
                                </span>
                                <span class="text-xs font-mono text-text-tertiary">Difficulty: {{ opp.difficulty }}</span>
                            </div>
                            <h3 class="text-sm font-bold text-text-primary">{{ opp.title }}</h3>
                            <p class="text-xs text-text-secondary leading-relaxed line-clamp-2">
                                {{ opp.description }}
                            </p>
                        </div>
                    </div>

                    <div v-else class="p-8 text-center bg-surface-secondary border border-dashed border-primary rounded-2xl text-xs text-text-secondary">
                        No technical opportunities or code debt items recorded yet.
                    </div>
                </div>
            </div>

            <!-- Proactive Action Recommendations Feed (Shared) -->
            <div class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-xs space-y-4">
                <h2 class="text-base font-bold text-text-primary">Proactive Recommendations</h2>
                <div v-if="recommendations.length > 0" class="divide-y divide-primary/50">
                    <div
                        v-for="rec in recommendations"
                        :key="rec.id"
                        class="py-3.5 first:pt-0 last:pb-0 flex items-start justify-between gap-4"
                    >
                        <div class="space-y-1">
                            <div class="text-xs font-bold text-text-primary flex items-center gap-2">
                                <span class="text-indigo-400 font-mono">⚡</span>
                                <span>{{ rec.title }}</span>
                            </div>
                            <p class="text-xs text-text-secondary leading-relaxed">
                                {{ rec.suggested_action || rec.description }}
                            </p>
                        </div>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-surface-tertiary text-text-secondary border border-primary shrink-0">
                            {{ rec.potential_impact || 'Moderate' }}
                        </span>
                    </div>
                </div>
                <div v-else class="text-xs text-text-secondary py-4 text-center">
                    Recommendations will appear as stage execution completes.
                </div>
            </div>
        </div>
    </AppLayout>
</template>

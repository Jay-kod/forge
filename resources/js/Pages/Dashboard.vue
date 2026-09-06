<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfidenceBadge from '@/Components/ConfidenceBadge.vue';
import type { Project, Opportunity, Recommendation } from '@/types';

defineProps<{
    recentProjects: Project[];
    metrics: {
        totalProjects: number;
        completedProjects: number;
        totalOpportunities: number;
        totalSources: number;
        creditBalance: number;
        planName: string;
    };
    opportunities: Opportunity[];
    recommendations: Recommendation[];
    recentAlerts: any[];
}>();
</script>

<template>
    <AppLayout>
        <Head title="Overview & Intelligence Dashboard — FORGE" />

        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Strategic Header & Intelligence Prompt Banner -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 pb-6 border-b border-primary">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-mono font-bold bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 uppercase tracking-wide">
                            {{ metrics.planName }}
                        </span>
                        <span class="text-xs text-text-tertiary font-mono">
                            Platform Status: Nominal
                        </span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-display font-extrabold tracking-tight text-text-primary">
                        Discover what's possible.
                    </h1>
                    <p class="text-sm text-text-secondary mt-1 max-w-2xl leading-relaxed">
                        Continuous product intelligence, traceable market evidence, and autonomous opportunity discovery across your workspaces.
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <Link
                        :href="route('discover')"
                        class="px-4 py-2.5 rounded-xl brand-button text-xs font-bold shadow-sm inline-flex items-center gap-2 transition-all hover:scale-[1.02]"
                    >
                        <span>✨</span>
                        <span>Launch New Discovery</span>
                    </Link>
                    <Link
                        :href="route('projects.index')"
                        class="px-4 py-2.5 rounded-xl border border-primary bg-surface-secondary hover:bg-surface-tertiary text-text-secondary hover:text-text-primary text-xs font-semibold transition-colors inline-flex items-center gap-2"
                    >
                        <span>All Projects ({{ metrics.totalProjects }})</span>
                    </Link>
                </div>
            </div>

            <!-- KPI Metric Strip -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                <div class="bg-surface-secondary border border-primary rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between text-text-tertiary mb-2">
                        <span class="text-xs font-mono uppercase tracking-wider">Active Workspaces</span>
                        <span class="text-base">📁</span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-display font-extrabold text-text-primary">
                        {{ metrics.totalProjects }}
                    </div>
                    <div class="text-[11px] font-mono text-emerald-500 dark:text-emerald-400 mt-1 flex items-center gap-1">
                        <span>✓</span>
                        <span>{{ metrics.completedProjects }} completed blueprints</span>
                    </div>
                </div>

                <div class="bg-surface-secondary border border-primary rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between text-text-tertiary mb-2">
                        <span class="text-xs font-mono uppercase tracking-wider">Identified Opportunities</span>
                        <span class="text-base">💡</span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-display font-extrabold text-indigo-400">
                        {{ metrics.totalOpportunities }}
                    </div>
                    <div class="text-[11px] font-mono text-text-secondary mt-1">
                        Across active project contexts
                    </div>
                </div>

                <div class="bg-surface-secondary border border-primary rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between text-text-tertiary mb-2">
                        <span class="text-xs font-mono uppercase tracking-wider">Traceable Sources</span>
                        <span class="text-base">🔬</span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-display font-extrabold text-text-primary">
                        {{ metrics.totalSources }}
                    </div>
                    <div class="text-[11px] font-mono text-text-secondary mt-1">
                        Live citations & web audits
                    </div>
                </div>

                <div class="bg-surface-secondary border border-primary rounded-2xl p-5 shadow-xs">
                    <div class="flex items-center justify-between text-text-tertiary mb-2">
                        <span class="text-xs font-mono uppercase tracking-wider">Available Capacity</span>
                        <span class="text-base">⚡</span>
                    </div>
                    <div class="text-2xl sm:text-3xl font-display font-extrabold text-emerald-500 dark:text-emerald-400 font-mono">
                        {{ metrics.creditBalance }}
                    </div>
                    <div class="text-[11px] font-mono text-text-secondary mt-1">
                        <Link :href="route('usage.index')" class="hover:underline text-indigo-400">
                            View usage ledger &rarr;
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Two-Column Prioritized Information Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                <!-- Left 7 Cols: "What am I working on?" & "What needs attention?" -->
                <div class="lg:col-span-7 space-y-6">
                    <!-- Section: Active Projects -->
                    <div class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-xs">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-primary">
                            <div>
                                <h2 class="text-base font-display font-bold text-text-primary">
                                    Current Workspaces
                                </h2>
                                <p class="text-xs text-text-secondary mt-0.5">
                                    Living discovery streams and stage execution
                                </p>
                            </div>
                            <Link :href="route('projects.index')" class="text-xs font-mono text-indigo-400 hover:underline">
                                View all &rarr;
                            </Link>
                        </div>

                        <div v-if="recentProjects.length > 0" class="divide-y divide-primary/50">
                            <div
                                v-for="proj in recentProjects"
                                :key="proj.id"
                                class="py-4 first:pt-0 last:pb-0 flex items-center justify-between gap-4 group"
                            >
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-surface-tertiary text-text-secondary border border-primary">
                                            {{ proj.classification }}
                                        </span>
                                        <span class="text-[11px] font-mono text-text-tertiary">
                                            Stage: <span class="capitalize text-indigo-400">{{ proj.current_stage || 'understanding' }}</span>
                                        </span>
                                    </div>
                                    <Link
                                        :href="route('projects.show', proj.id)"
                                        class="text-sm font-bold text-text-primary group-hover:text-indigo-400 transition-colors truncate block"
                                    >
                                        {{ proj.title }}
                                    </Link>
                                    <p class="text-xs text-text-secondary line-clamp-1 mt-0.5">
                                        {{ proj.description || 'No description provided.' }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <Link
                                        :href="route('projects.show', proj.id)"
                                        class="px-3 py-1.5 rounded-lg border border-primary bg-surface-primary hover:bg-surface-tertiary text-xs font-mono text-text-secondary hover:text-text-primary transition-colors"
                                    >
                                        Resume &rarr;
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-12 px-4 border border-dashed border-primary rounded-xl">
                            <span class="text-3xl block mb-2">📁</span>
                            <h3 class="text-sm font-bold text-text-primary mb-1">No Active Workspaces Yet</h3>
                            <p class="text-xs text-text-secondary max-w-sm mx-auto mb-4">
                                Describe a product, website, or business problem to launch autonomous discovery.
                            </p>
                            <Link :href="route('discover')" class="px-4 py-2 rounded-xl brand-button text-xs font-bold shadow-xs inline-block">
                                ✨ Launch Discovery
                            </Link>
                        </div>
                    </div>

                    <!-- Section: Recent Alerts & System Signals -->
                    <div class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-xs">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-primary">
                            <div>
                                <h2 class="text-base font-display font-bold text-text-primary">
                                    Recent Intelligence Signals
                                </h2>
                                <p class="text-xs text-text-secondary mt-0.5">
                                    Opportunity alerts, website drift, and research updates
                                </p>
                            </div>
                            <Link :href="route('notifications.index')" class="text-xs font-mono text-indigo-400 hover:underline">
                                Notification Center &rarr;
                            </Link>
                        </div>

                        <div v-if="recentAlerts.length > 0" class="space-y-3">
                            <div
                                v-for="alert in recentAlerts"
                                :key="alert.id"
                                class="p-3 rounded-xl bg-surface-primary border border-primary flex items-start gap-3"
                            >
                                <span class="text-sm mt-0.5">
                                    {{ alert.severity === 'critical' ? '🔴' : (alert.severity === 'warning' ? '🟡' : '🔵') }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="text-xs font-bold text-text-primary">{{ alert.title }}</div>
                                    <div class="text-xs text-text-secondary mt-0.5 leading-relaxed">{{ alert.message }}</div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="text-center py-8 text-xs font-mono text-text-tertiary">
                            ✓ All signals acknowledged. No pending alerts.
                        </div>
                    </div>
                </div>

                <!-- Right 5 Cols: "What opportunities exist?" & "What should I do next?" -->
                <div class="lg:col-span-5 space-y-6">
                    <!-- Section: Priority Opportunities Radar -->
                    <div class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-xs">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-primary">
                            <div>
                                <h2 class="text-base font-display font-bold text-text-primary">
                                    High-Impact Opportunities
                                </h2>
                                <p class="text-xs text-text-secondary mt-0.5">
                                    Synthesized actionable vectors
                                </p>
                            </div>
                            <Link :href="route('opportunities.index')" class="text-xs font-mono text-indigo-400 hover:underline">
                                Full Radar &rarr;
                            </Link>
                        </div>

                        <div v-if="opportunities.length > 0" class="space-y-3">
                            <div
                                v-for="opp in opportunities"
                                :key="opp.id"
                                class="p-4 rounded-xl bg-surface-primary border border-primary hover:border-indigo-500/40 transition-colors"
                            >
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="text-[10px] font-mono uppercase px-2 py-0.5 rounded bg-surface-tertiary text-text-secondary border border-primary">
                                        {{ opp.category }}
                                    </span>
                                    <span
                                        class="text-[10px] font-mono px-2 py-0.5 rounded uppercase font-bold"
                                        :class="opp.impact === 'critical' || opp.impact === 'high' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'"
                                    >
                                        Impact: {{ opp.impact }}
                                    </span>
                                </div>
                                <h3 class="text-xs font-bold text-text-primary leading-snug">
                                    {{ opp.title }}
                                </h3>
                                <p class="text-xs text-text-secondary line-clamp-2 mt-1 leading-relaxed">
                                    {{ opp.description }}
                                </p>
                            </div>
                        </div>

                        <div v-else class="text-center py-10 px-4 text-xs text-text-secondary border border-dashed border-primary rounded-xl">
                            Opportunities will populate here as stage research progresses.
                        </div>
                    </div>

                    <!-- Section: Recommended Actions -->
                    <div class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-xs">
                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-primary">
                            <div>
                                <h2 class="text-base font-display font-bold text-text-primary">
                                    Suggested Next Actions
                                </h2>
                                <p class="text-xs text-text-secondary mt-0.5">
                                    Proactive execution directives
                                </p>
                            </div>
                            <Link :href="route('growth.index')" class="text-xs font-mono text-indigo-400 hover:underline">
                                Growth Center &rarr;
                            </Link>
                        </div>

                        <div v-if="recommendations.length > 0" class="space-y-3">
                            <div
                                v-for="rec in recommendations"
                                :key="rec.id"
                                class="p-3.5 rounded-xl bg-surface-primary border border-primary space-y-1.5"
                            >
                                <div class="text-xs font-bold text-text-primary flex items-center gap-1.5">
                                    <span class="text-emerald-500 dark:text-emerald-400">⚡</span>
                                    <span>{{ rec.title }}</span>
                                </div>
                                <p class="text-xs text-text-secondary leading-relaxed">
                                    {{ rec.suggested_action || rec.description }}
                                </p>
                            </div>
                        </div>

                        <div v-else class="text-center py-8 text-xs text-text-secondary">
                            No immediate recommendations pending.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

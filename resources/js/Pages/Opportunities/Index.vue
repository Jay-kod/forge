<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfidenceBadge from '@/Components/ConfidenceBadge.vue';
import type { Opportunity } from '@/types';

const props = defineProps<{
    opportunities: (Opportunity & {
        project?: { id: number; title: string; classification: string; status: string };
        recommendations?: any[];
    })[];
    categoriesCount: Record<string, number>;
    filters: {
        category?: string;
        impact?: string;
        difficulty?: string;
        search?: string;
    };
}>();

const search = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');
const selectedImpact = ref(props.filters.impact || '');
const selectedDifficulty = ref(props.filters.difficulty || '');

const categories = [
    { key: '', label: 'All Categories' },
    { key: 'product', label: 'Product' },
    { key: 'growth', label: 'Growth' },
    { key: 'market', label: 'Market' },
    { key: 'technical', label: 'Technical' },
    { key: 'architecture', label: 'Architecture' },
    { key: 'revenue', label: 'Revenue' },
    { key: 'expansion', label: 'Expansion' },
    { key: 'automation', label: 'Automation' },
];

const applyFilters = () => {
    router.get(route('opportunities.index'), {
        category: selectedCategory.value || undefined,
        impact: selectedImpact.value || undefined,
        difficulty: selectedDifficulty.value || undefined,
        search: search.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const filterByCategory = (cat: string) => {
    selectedCategory.value = cat;
    applyFilters();
};
</script>

<template>
    <AppLayout>
        <Head title="Cross-Project Opportunity Radar — FORGE" />

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Strategic Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-primary">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-display font-extrabold tracking-tight text-text-primary">
                        Opportunity Radar
                    </h1>
                    <p class="text-xs sm:text-sm text-text-secondary mt-1">
                        Synthesized high-leverage opportunities discovered across your active project contexts.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="text-xs font-mono text-text-tertiary">
                        Total Identified: <span class="font-bold text-text-primary">{{ opportunities.length }}</span>
                    </span>
                </div>
            </div>

            <!-- Filter Controls Bar -->
            <div class="space-y-4">
                <!-- Category Tabs -->
                <div class="flex items-center gap-2 overflow-x-auto pb-2 custom-scrollbar">
                    <button
                        v-for="cat in categories"
                        :key="cat.key"
                        type="button"
                        @click="filterByCategory(cat.key)"
                        class="px-3 py-1.5 rounded-xl text-xs font-medium whitespace-nowrap transition-colors flex items-center gap-1.5"
                        :class="selectedCategory === cat.key
                            ? 'bg-indigo-600 text-white shadow-xs font-semibold'
                            : 'bg-surface-secondary text-text-secondary hover:text-text-primary border border-primary'"
                    >
                        <span>{{ cat.label }}</span>
                        <span
                            v-if="cat.key && categoriesCount[cat.key]"
                            class="px-1.5 py-0.2 rounded-full text-[10px] font-mono"
                            :class="selectedCategory === cat.key ? 'bg-indigo-700 text-white' : 'bg-surface-tertiary text-text-tertiary'"
                        >
                            {{ categoriesCount[cat.key] }}
                        </span>
                    </button>
                </div>

                <!-- Search & Attributes Filters -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="relative flex-1">
                        <input
                            v-model="search"
                            @keyup.enter="applyFilters"
                            type="text"
                            placeholder="Search opportunities by title or description..."
                            class="w-full rounded-xl bg-surface-secondary border border-primary pl-9 pr-4 py-2 text-xs text-text-primary placeholder:text-text-tertiary focus:outline-hidden focus:border-indigo-500"
                        />
                        <span class="absolute left-3 top-2.5 text-text-tertiary text-xs">🔍</span>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <select
                            v-model="selectedImpact"
                            @change="applyFilters"
                            class="rounded-xl bg-surface-secondary border border-primary px-3 py-2 text-xs text-text-secondary focus:outline-hidden focus:border-indigo-500"
                        >
                            <option value="">All Impact Levels</option>
                            <option value="critical">Critical Impact</option>
                            <option value="high">High Impact</option>
                            <option value="medium">Medium Impact</option>
                            <option value="low">Low Impact</option>
                        </select>

                        <select
                            v-model="selectedDifficulty"
                            @change="applyFilters"
                            class="rounded-xl bg-surface-secondary border border-primary px-3 py-2 text-xs text-text-secondary focus:outline-hidden focus:border-indigo-500"
                        >
                            <option value="">All Difficulties</option>
                            <option value="low">Low Difficulty (Quick Win)</option>
                            <option value="medium">Medium Difficulty</option>
                            <option value="high">High Difficulty</option>
                            <option value="extreme">Extreme Difficulty</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Opportunities Catalog Grid -->
            <div v-if="opportunities.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                <div
                    v-for="opp in opportunities"
                    :key="opp.id"
                    class="bg-surface-secondary border border-primary hover:border-indigo-500/40 rounded-2xl p-6 shadow-xs transition-all flex flex-col justify-between"
                >
                    <div>
                        <!-- Header Badges -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold bg-surface-tertiary text-text-secondary border border-primary truncate">
                                    {{ opp.category }}
                                </span>
                                <Link
                                    v-if="opp.project"
                                    :href="route('projects.show', opp.project.id)"
                                    class="text-[11px] font-mono text-indigo-400 hover:underline truncate"
                                >
                                    📁 {{ opp.project.title }}
                                </Link>
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                <span
                                    class="text-[10px] font-mono px-2 py-0.5 rounded uppercase font-bold"
                                    :class="opp.impact === 'critical' || opp.impact === 'high' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'"
                                >
                                    {{ opp.impact }} impact
                                </span>
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-surface-tertiary text-text-secondary border border-primary">
                                    {{ opp.difficulty }} diff
                                </span>
                            </div>
                        </div>

                        <!-- Title & Description -->
                        <h2 class="text-base font-bold text-text-primary leading-snug">
                            {{ opp.title }}
                        </h2>
                        <p class="text-xs text-text-secondary mt-2 leading-relaxed">
                            {{ opp.description }}
                        </p>

                        <!-- Recommendations & Next Action (if attached) -->
                        <div v-if="opp.recommendations && opp.recommendations.length > 0" class="mt-4 p-3 rounded-xl bg-surface-primary border border-primary space-y-1.5">
                            <div class="text-[11px] font-mono uppercase text-text-tertiary font-bold flex items-center gap-1">
                                <span>⚡ Recommended Next Action:</span>
                            </div>
                            <div class="text-xs font-medium text-text-primary leading-relaxed">
                                {{ opp.recommendations[0].suggested_action || opp.recommendations[0].title }}
                            </div>
                            <div v-if="opp.recommendations[0].why_it_matters" class="text-[11px] text-text-secondary leading-normal">
                                <span class="text-text-tertiary">Why it matters:</span> {{ opp.recommendations[0].why_it_matters }}
                            </div>
                        </div>
                    </div>

                    <!-- Footer Controls -->
                    <div class="mt-5 pt-4 border-t border-primary flex items-center justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-mono text-text-tertiary">Confidence:</span>
                            <ConfidenceBadge :confidence="opp.confidence" :score="opp.confidence_score" />
                        </div>

                        <Link
                            v-if="opp.project"
                            :href="route('projects.show', opp.project.id)"
                            class="text-xs font-mono font-semibold text-indigo-400 hover:text-indigo-300 transition-colors"
                        >
                            Open Project Context &rarr;
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="text-center py-16 px-4 bg-surface-secondary border border-dashed border-primary rounded-2xl">
                <span class="text-3xl block mb-2">💡</span>
                <h3 class="text-sm font-bold text-text-primary mb-1">No Opportunities Matching Criteria</h3>
                <p class="text-xs text-text-secondary max-w-md mx-auto mb-4">
                    Clear filters or run automated stage research on your active projects to populate opportunity vectors.
                </p>
                <button
                    v-if="selectedCategory || selectedImpact || selectedDifficulty || search"
                    type="button"
                    @click="selectedCategory = ''; selectedImpact = ''; selectedDifficulty = ''; search = ''; applyFilters()"
                    class="px-4 py-2 rounded-xl border border-primary bg-surface-primary text-xs font-mono text-text-secondary hover:text-text-primary"
                >
                    Reset Filters
                </button>
            </div>
        </div>
    </AppLayout>
</template>

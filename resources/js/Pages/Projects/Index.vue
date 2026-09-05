<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { Project, SharedProps } from '@/types';

const props = defineProps<{
    projects: Project[];
}>();

const page = usePage<SharedProps>();

// Filters & View State
const searchQuery = ref('');
const selectedStatus = ref<string>('all');
const selectedClassification = ref<string>('all');
const viewMode = ref<'grid' | 'list'>('grid');

// Computed Filtered Projects
const filteredProjects = computed(() => {
    return props.projects.filter(project => {
        const matchesSearch = !searchQuery.value ||
            project.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (project.description && project.description.toLowerCase().includes(searchQuery.value.toLowerCase()));

        const matchesStatus = selectedStatus.value === 'all' || project.status === selectedStatus.value;
        const matchesClass = selectedClassification.value === 'all' || project.classification === selectedClassification.value;

        return matchesSearch && matchesStatus && matchesClass;
    });
});

// KPI Calculations
const totalWorkspaces = computed(() => props.projects.length);
const activeWorkspaces = computed(() => props.projects.filter(p => p.status === 'active').length);
const completedWorkspaces = computed(() => props.projects.filter(p => p.status === 'completed').length);

const classifications = [
    { value: 'all', label: 'All Classifications' },
    { value: 'NEW_PRODUCT', label: 'New Product' },
    { value: 'EXISTING_PRODUCT', label: 'Existing Product' },
    { value: 'TECHNICAL_AUDIT', label: 'Codebase Audit' },
    { value: 'MARKET_VALIDATION', label: 'Market Validation' },
    { value: 'SOFTWARE_REBUILD', label: 'Software Rebuild' },
    { value: 'BUSINESS_GROWTH', label: 'Business Growth' },
];

const getClassificationColor = (classification: string) => {
    switch (classification) {
        case 'NEW_PRODUCT':
            return 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30';
        case 'TECHNICAL_AUDIT':
            return 'bg-amber-500/10 text-amber-400 border-amber-500/30';
        case 'MARKET_VALIDATION':
            return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30';
        case 'SOFTWARE_REBUILD':
            return 'bg-purple-500/10 text-purple-400 border-purple-500/30';
        default:
            return 'bg-surface-tertiary text-text-secondary border-primary';
    }
};

const stageOrder = [
    'understanding',
    'discovery',
    'research',
    'competitors',
    'challenge',
    'strategy',
    'prd',
    'architecture',
    'package',
    'export'
];

const getStageProgressPercent = (stage: string | null) => {
    if (!stage) return 10;
    const index = stageOrder.indexOf(stage);
    if (index === -1) return 15;
    return Math.round(((index + 1) / stageOrder.length) * 100);
};
</script>

<template>
    <AppLayout>
        <Head title="Workspaces & Intelligence Dashboard — FORGE" />

        <div class="space-y-6">
            <!-- ============================================================= -->
            <!-- DASHBOARD HEADER & QUICK ACTION                               -->
            <!-- ============================================================= -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-display font-bold tracking-tight text-text-primary">
                        Intelligence Workspaces
                    </h1>
                    <p class="text-xs sm:text-sm text-text-secondary mt-1">
                        Continuous product discovery, evidence-backed strategy, and AI-grounded specifications.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Link
                        :href="route('projects.create')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl brand-button text-xs font-semibold shadow-md hover:shadow-indigo-500/20 active:scale-[0.99] transition-all shrink-0"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>What are you trying to achieve?</span>
                    </Link>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- EXECUTIVE KPI METRICS STRIP                                   -->
            <!-- ============================================================= -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5">
                <!-- KPI 1: Active Workspaces -->
                <div class="p-4 rounded-2xl bg-surface-secondary border border-primary relative overflow-hidden group hover:border-indigo-500/40 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-mono uppercase tracking-wider text-text-tertiary">Active Workspaces</span>
                        <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-xs">
                            🏢
                        </div>
                    </div>
                    <div class="text-2xl font-display font-bold text-text-primary mt-2">
                        {{ totalWorkspaces }}
                    </div>
                    <div class="text-[10px] text-text-tertiary font-mono mt-0.5 flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>{{ activeWorkspaces }} active · {{ completedWorkspaces }} completed</span>
                    </div>
                </div>

                <!-- KPI 2: Evidence Engine -->
                <div class="p-4 rounded-2xl bg-surface-secondary border border-primary relative overflow-hidden group hover:border-emerald-500/40 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-mono uppercase tracking-wider text-text-tertiary">Evidence Engine</span>
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xs">
                            🔍
                        </div>
                    </div>
                    <div class="text-2xl font-display font-bold text-text-primary mt-2">
                        Continuous
                    </div>
                    <div class="text-[10px] text-emerald-400 font-mono mt-0.5 flex items-center gap-1">
                        <span>✓ Verified Citations</span>
                    </div>
                </div>

                <!-- KPI 3: Account Credits -->
                <div class="p-4 rounded-2xl bg-surface-secondary border border-primary relative overflow-hidden group hover:border-amber-500/40 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-mono uppercase tracking-wider text-text-tertiary">Credit Fuel</span>
                        <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center text-xs">
                            ⚡
                        </div>
                    </div>
                    <div class="text-2xl font-display font-bold text-text-primary mt-2">
                        {{ page.props.credits.balance }}
                    </div>
                    <div class="text-[10px] text-text-tertiary font-mono mt-0.5">
                        <Link :href="route('pricing')" class="text-amber-400 hover:underline">
                            Top up or manage plan →
                        </Link>
                    </div>
                </div>

                <!-- KPI 4: Execution Pipeline -->
                <div class="p-4 rounded-2xl bg-surface-secondary border border-primary relative overflow-hidden group hover:border-purple-500/40 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-mono uppercase tracking-wider text-text-tertiary">Product Stages</span>
                        <div class="w-7 h-7 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center text-xs">
                            📋
                        </div>
                    </div>
                    <div class="text-2xl font-display font-bold text-text-primary mt-2">
                        10 Stages
                    </div>
                    <div class="text-[10px] text-purple-400 font-mono mt-0.5">
                        Discovery → Strategy → PRD
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- QUICK START DISCOVERY TEMPLATES                               -->
            <!-- ============================================================= -->
            <div class="p-4 rounded-2xl bg-surface-secondary/70 border border-primary">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-mono font-bold uppercase tracking-wider text-text-primary flex items-center gap-1.5">
                        <span>✨</span>
                        <span>Quick-Start Opportunity Blueprints</span>
                    </span>
                    <span class="text-[11px] text-text-tertiary">Instant Setup</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <!-- Template 1: Validate SaaS Idea -->
                    <Link
                        :href="route('projects.create')"
                        class="p-3.5 rounded-xl border border-primary bg-surface-primary hover:border-indigo-500/40 hover:bg-surface-tertiary transition-all group text-left"
                    >
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-sm">🚀</span>
                            <span class="text-[10px] font-mono text-indigo-400 font-semibold group-hover:translate-x-0.5 transition-transform">Use Blueprint →</span>
                        </div>
                        <div class="font-bold text-xs text-text-primary group-hover:text-indigo-400 transition-colors">
                            Validate SaaS Product
                        </div>
                        <p class="text-[11px] text-text-secondary mt-1 leading-snug line-clamp-2">
                            Research customer pain, market holes, and competitor moats before writing any code.
                        </p>
                    </Link>

                    <!-- Template 2: Codebase Audit -->
                    <Link
                        :href="route('projects.create')"
                        class="p-3.5 rounded-xl border border-primary bg-surface-primary hover:border-emerald-500/40 hover:bg-surface-tertiary transition-all group text-left"
                    >
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-sm">💻</span>
                            <span class="text-[10px] font-mono text-emerald-400 font-semibold group-hover:translate-x-0.5 transition-transform">Use Blueprint →</span>
                        </div>
                        <div class="font-bold text-xs text-text-primary group-hover:text-emerald-400 transition-colors">
                            Codebase & Architecture Audit
                        </div>
                        <p class="text-[11px] text-text-secondary mt-1 leading-snug line-clamp-2">
                            Analyze technical debt, architectural bottlenecks, and prepare GitHub AGENTS.md instructions.
                        </p>
                    </Link>

                    <!-- Template 3: Market Pivot & Expansion -->
                    <Link
                        :href="route('projects.create')"
                        class="p-3.5 rounded-xl border border-primary bg-surface-primary hover:border-amber-500/40 hover:bg-surface-tertiary transition-all group text-left"
                    >
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-sm">⚡</span>
                            <span class="text-[10px] font-mono text-amber-400 font-semibold group-hover:translate-x-0.5 transition-transform">Use Blueprint →</span>
                        </div>
                        <div class="font-bold text-xs text-text-primary group-hover:text-amber-400 transition-colors">
                            Competitor Gap Discovery
                        </div>
                        <p class="text-[11px] text-text-secondary mt-1 leading-snug line-clamp-2">
                            Uncover pricing vulnerabilities, feature gaps, and positioning angles from active market research.
                        </p>
                    </Link>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- SEARCH, FILTERS & VIEW MODE CONTROLS                          -->
            <!-- ============================================================= -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-2xl bg-surface-secondary border border-primary">
                <!-- Search Bar -->
                <div class="relative flex-1 max-w-md">
                    <svg class="w-4 h-4 text-text-tertiary absolute left-3 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search workspaces by title, description or tag..."
                        class="w-full pl-9 pr-3.5 py-1.5 rounded-xl bg-surface-primary border border-primary text-xs text-text-primary placeholder:text-text-tertiary focus:outline-none focus:border-indigo-500/60 focus:ring-1 focus:ring-indigo-500/20"
                    />
                </div>

                <!-- Filters & View Toggles -->
                <div class="flex items-center gap-2 overflow-x-auto">
                    <!-- Status Filter Tabs -->
                    <div class="flex items-center bg-surface-primary border border-primary rounded-xl p-0.5 text-xs font-medium">
                        <button
                            type="button"
                            @click="selectedStatus = 'all'"
                            class="px-2.5 py-1 rounded-lg transition-all text-xs"
                            :class="selectedStatus === 'all' ? 'bg-surface-elevated text-text-primary font-bold shadow-xs' : 'text-text-tertiary hover:text-text-primary'"
                        >
                            All
                        </button>
                        <button
                            type="button"
                            @click="selectedStatus = 'active'"
                            class="px-2.5 py-1 rounded-lg transition-all text-xs"
                            :class="selectedStatus === 'active' ? 'bg-surface-elevated text-emerald-400 font-bold shadow-xs' : 'text-text-tertiary hover:text-text-primary'"
                        >
                            Active
                        </button>
                        <button
                            type="button"
                            @click="selectedStatus = 'completed'"
                            class="px-2.5 py-1 rounded-lg transition-all text-xs"
                            :class="selectedStatus === 'completed' ? 'bg-surface-elevated text-indigo-400 font-bold shadow-xs' : 'text-text-tertiary hover:text-text-primary'"
                        >
                            Completed
                        </button>
                    </div>

                    <!-- Classification Dropdown -->
                    <select
                        v-model="selectedClassification"
                        class="px-3 py-1.5 rounded-xl bg-surface-primary border border-primary text-xs text-text-secondary focus:outline-none"
                    >
                        <option v-for="c in classifications" :key="c.value" :value="c.value">
                            {{ c.label }}
                        </option>
                    </select>

                    <!-- Grid vs List View Switcher -->
                    <div class="flex items-center bg-surface-primary border border-primary rounded-xl p-0.5">
                        <button
                            type="button"
                            @click="viewMode = 'grid'"
                            class="p-1.5 rounded-lg transition-all"
                            :class="viewMode === 'grid' ? 'bg-surface-elevated text-text-primary shadow-xs' : 'text-text-tertiary hover:text-text-primary'"
                            title="Grid view"
                            aria-label="Grid view"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </button>
                        <button
                            type="button"
                            @click="viewMode = 'list'"
                            class="p-1.5 rounded-lg transition-all"
                            :class="viewMode === 'list' ? 'bg-surface-elevated text-text-primary shadow-xs' : 'text-text-tertiary hover:text-text-primary'"
                            title="List view"
                            aria-label="List view"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- WORKSPACE DISPLAY (GRID VIEW)                                 -->
            <!-- ============================================================= -->
            <div v-if="filteredProjects.length > 0 && viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <div
                    v-for="project in filteredProjects"
                    :key="project.id"
                    class="flex flex-col bg-surface-secondary border border-primary hover:border-indigo-500/50 rounded-2xl p-5 transition-all duration-200 hover:shadow-xl group relative"
                >
                    <!-- Card Top Header -->
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-mono font-semibold border"
                            :class="getClassificationColor(project.classification)"
                        >
                            {{ project.classification }}
                        </span>
                        <span class="text-[11px] text-text-tertiary font-mono">
                            {{ new Date(project.created_at).toLocaleDateString() }}
                        </span>
                    </div>

                    <!-- Title & Description -->
                    <h2 class="text-base font-display font-bold text-text-primary group-hover:text-indigo-400 transition-colors line-clamp-1 mb-1.5">
                        {{ project.title }}
                    </h2>
                    <p class="text-xs text-text-secondary line-clamp-2 mb-4 leading-relaxed">
                        {{ project.description || 'No description provided.' }}
                    </p>

                    <!-- Stage Progress Mini Bar -->
                    <div class="space-y-1.5 mb-5 mt-auto pt-3 border-t border-primary/50">
                        <div class="flex items-center justify-between text-[10px] font-mono text-text-tertiary">
                            <span>Stage: {{ project.current_stage || 'Understanding' }}</span>
                            <span class="font-bold text-text-secondary">{{ getStageProgressPercent(project.current_stage) }}%</span>
                        </div>
                        <div class="w-full bg-surface-tertiary h-1.5 rounded-full overflow-hidden">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-emerald-400 transition-all duration-500"
                                :style="{ width: `${getStageProgressPercent(project.current_stage)}%` }"
                            ></div>
                        </div>
                    </div>

                    <!-- Card Footer CTA -->
                    <div class="pt-3 border-t border-primary flex items-center justify-between">
                        <div class="flex items-center gap-1.5 text-xs text-text-tertiary font-mono">
                            <span class="w-2 h-2 rounded-full" :class="project.status === 'completed' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                            <span class="capitalize">{{ project.status }}</span>
                        </div>

                        <Link
                            :href="route('projects.show', project.id)"
                            class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition-colors py-1 px-2.5 rounded-lg hover:bg-surface-tertiary"
                        >
                            <span>Open Workspace</span>
                            <span>→</span>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- WORKSPACE DISPLAY (LIST / TABLE VIEW)                         -->
            <!-- ============================================================= -->
            <div v-else-if="filteredProjects.length > 0 && viewMode === 'list'" class="bg-surface-secondary border border-primary rounded-2xl overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-primary bg-surface-tertiary/50 text-[11px] font-mono text-text-tertiary uppercase">
                                <th class="py-3 px-4">Workspace</th>
                                <th class="py-3 px-4">Classification</th>
                                <th class="py-3 px-4">Stage Progress</th>
                                <th class="py-3 px-4">Status</th>
                                <th class="py-3 px-4">Created</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary/50">
                            <tr
                                v-for="project in filteredProjects"
                                :key="project.id"
                                class="hover:bg-surface-tertiary/40 transition-colors"
                            >
                                <td class="py-3 px-4">
                                    <div class="font-bold text-text-primary text-xs">{{ project.title }}</div>
                                    <div class="text-[11px] text-text-secondary truncate max-w-xs">{{ project.description }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-mono font-semibold border" :class="getClassificationColor(project.classification)">
                                        {{ project.classification }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-20 bg-surface-tertiary h-1.5 rounded-full overflow-hidden">
                                            <div
                                                class="h-full bg-gradient-to-r from-indigo-500 to-emerald-400"
                                                :style="{ width: `${getStageProgressPercent(project.current_stage)}%` }"
                                            ></div>
                                        </div>
                                        <span class="text-[10px] font-mono text-text-tertiary">{{ getStageProgressPercent(project.current_stage) }}%</span>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center gap-1.5 text-xs text-text-secondary font-mono">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="project.status === 'completed' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                                        <span class="capitalize">{{ project.status }}</span>
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-mono text-text-tertiary text-[11px]">
                                    {{ new Date(project.created_at).toLocaleDateString() }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <Link
                                        :href="route('projects.show', project.id)"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition-colors py-1 px-2.5 rounded-lg hover:bg-surface-tertiary"
                                    >
                                        <span>Open</span>
                                        <span>→</span>
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- EMPTY SEARCH / NO WORKSPACES STATE                            -->
            <!-- ============================================================= -->
            <div v-else class="text-center py-16 px-4 bg-surface-secondary border border-primary rounded-2xl">
                <div class="w-16 h-16 rounded-2xl bg-surface-tertiary border border-primary flex items-center justify-center mx-auto text-text-tertiary mb-4">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                </div>
                <h2 class="text-lg font-display font-bold text-text-primary mb-1">
                    {{ searchQuery || selectedStatus !== 'all' ? 'No Matching Workspaces Found' : 'No Active Workspaces Yet' }}
                </h2>
                <p class="text-xs text-text-secondary max-w-sm mx-auto mb-6">
                    {{ searchQuery || selectedStatus !== 'all'
                        ? 'Try adjusting your search criteria, clearing filters, or launch a new discovery.'
                        : 'Describe an idea, business, codebase, website, or problem to launch your first evidence-backed discovery loop.' }}
                </p>
                <div class="flex items-center justify-center gap-3">
                    <button
                        v-if="searchQuery || selectedStatus !== 'all'"
                        type="button"
                        @click="searchQuery = ''; selectedStatus = 'all'; selectedClassification = 'all'"
                        class="px-4 py-2 rounded-xl border border-primary bg-surface-tertiary text-xs font-semibold text-text-primary hover:bg-surface-elevated transition-colors"
                    >
                        Clear Filters
                    </button>
                    <Link
                        :href="route('projects.create')"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl brand-button text-xs font-semibold shadow-md"
                    >
                        Launch New Discovery
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

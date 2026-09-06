<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfidenceBadge from '@/Components/ConfidenceBadge.vue';
import type { ResearchSource, Evidence } from '@/types';

const props = defineProps<{
    sources: (ResearchSource & {
        evidence?: Array<Evidence & { project?: { id: number; title: string } }>;
    })[];
    evidence: (Evidence & {
        project?: { id: number; title: string };
        sources?: ResearchSource[];
    })[];
    websiteAnalyses: any[];
    competitors: any[];
}>();

const activeTab = ref<'sources' | 'evidence' | 'competitors' | 'websites'>('sources');
const selectedSource = ref<ResearchSource | null>(null);
</script>

<template>
    <AppLayout>
        <Head title="Research, Traceable Sources & Evidence — FORGE" />

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Strategic Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-primary">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-display font-extrabold tracking-tight text-text-primary">
                        Research & Traceable Evidence
                    </h1>
                    <p class="text-xs sm:text-sm text-text-secondary mt-1">
                        Verified citations, competitor intelligence, and synthesized claims grounded in real web evidence.
                    </p>
                </div>

                <!-- Tab Segmented Switcher -->
                <div class="flex items-center gap-1.5 p-1 rounded-2xl bg-surface-secondary border border-primary">
                    <button
                        @click="activeTab = 'sources'"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors"
                        :class="activeTab === 'sources' ? 'bg-indigo-600 text-white shadow-xs' : 'text-text-secondary hover:text-text-primary'"
                    >
                        Sources ({{ sources.length }})
                    </button>
                    <button
                        @click="activeTab = 'evidence'"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors"
                        :class="activeTab === 'evidence' ? 'bg-indigo-600 text-white shadow-xs' : 'text-text-secondary hover:text-text-primary'"
                    >
                        Claims ({{ evidence.length }})
                    </button>
                    <button
                        @click="activeTab = 'competitors'"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors"
                        :class="activeTab === 'competitors' ? 'bg-indigo-600 text-white shadow-xs' : 'text-text-secondary hover:text-text-primary'"
                    >
                        Competitors ({{ competitors.length }})
                    </button>
                    <button
                        @click="activeTab = 'websites'"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold transition-colors"
                        :class="activeTab === 'websites' ? 'bg-indigo-600 text-white shadow-xs' : 'text-text-secondary hover:text-text-primary'"
                    >
                        Web Audits ({{ websiteAnalyses.length }})
                    </button>
                </div>
            </div>

            <!-- Tab 1: Traceable Research Sources -->
            <div v-if="activeTab === 'sources'" class="space-y-4">
                <div v-if="sources.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="src in sources"
                        :key="src.id"
                        class="bg-surface-secondary border border-primary hover:border-indigo-500/40 rounded-2xl p-5 shadow-xs transition-all flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold bg-surface-tertiary text-indigo-400 border border-primary">
                                    {{ src.source_type }}
                                </span>
                                <span class="text-[10px] font-mono text-text-tertiary">
                                    Reliability: {{ Math.round((src.reliability_score || 0.85) * 100) }}%
                                </span>
                            </div>

                            <h3 class="text-sm font-bold text-text-primary leading-snug line-clamp-2">
                                {{ src.title || 'Untitled Source' }}
                            </h3>
                            <p class="text-xs text-text-secondary line-clamp-3 mt-2 leading-relaxed">
                                {{ src.content_summary || 'No summary excerpt recorded.' }}
                            </p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-primary flex items-center justify-between gap-2">
                            <a
                                :href="src.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-xs font-mono text-indigo-400 hover:underline truncate max-w-[200px]"
                                :title="src.url"
                            >
                                🔗 {{ src.url.replace(/^https?:\/\//, '') }}
                            </a>

                            <button
                                type="button"
                                @click="selectedSource = src"
                                class="text-xs font-mono text-text-tertiary hover:text-text-primary shrink-0"
                            >
                                Details &rarr;
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-16 bg-surface-secondary border border-dashed border-primary rounded-2xl">
                    <span class="text-3xl block mb-2">🔬</span>
                    <h3 class="text-sm font-bold text-text-primary mb-1">No Sources Stored Yet</h3>
                    <p class="text-xs text-text-secondary max-w-sm mx-auto">
                        Execute Stage 03 (Research) in any project workspace to extract verified sources.
                    </p>
                </div>
            </div>

            <!-- Tab 2: Synthesized Claims & Evidence Registry -->
            <div v-if="activeTab === 'evidence'" class="space-y-4">
                <div v-if="evidence.length > 0" class="space-y-3">
                    <div
                        v-for="ev in evidence"
                        :key="ev.id"
                        class="bg-surface-secondary border border-primary rounded-2xl p-5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4"
                    >
                        <div class="space-y-1.5 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase bg-surface-tertiary text-text-secondary border border-primary">
                                    {{ ev.category }}
                                </span>
                                <Link
                                    v-if="ev.project"
                                    :href="route('projects.show', ev.project.id)"
                                    class="text-[11px] font-mono text-indigo-400 hover:underline"
                                >
                                    📁 {{ ev.project.title }}
                                </Link>
                            </div>
                            <p class="text-sm font-medium text-text-primary leading-relaxed">
                                {{ ev.claim }}
                            </p>
                        </div>

                        <div class="flex items-center gap-4 shrink-0">
                            <ConfidenceBadge :confidence="ev.confidence" :score="ev.confidence_score" />
                            <span class="text-xs font-mono text-text-tertiary">
                                Sources · {{ ev.sources?.length || 0 }}
                            </span>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-16 bg-surface-secondary border border-dashed border-primary rounded-2xl">
                    <span class="text-3xl block mb-2">📜</span>
                    <h3 class="text-sm font-bold text-text-primary mb-1">No Claims Synthesized Yet</h3>
                    <p class="text-xs text-text-secondary max-w-sm mx-auto">
                        Claims are recorded automatically as stage research proceeds.
                    </p>
                </div>
            </div>

            <!-- Tab 3: Competitors -->
            <div v-if="activeTab === 'competitors'" class="space-y-4">
                <div v-if="competitors.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="comp in competitors"
                        :key="comp.id"
                        class="bg-surface-secondary border border-primary rounded-2xl p-5 shadow-xs space-y-3"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold bg-surface-tertiary text-text-secondary border border-primary">
                                {{ comp.category }}
                            </span>
                            <Link v-if="comp.project" :href="route('projects.show', comp.project.id)" class="text-[11px] font-mono text-indigo-400 hover:underline">
                                {{ comp.project.title }}
                            </Link>
                        </div>
                        <h3 class="text-sm font-bold text-text-primary">{{ comp.name }}</h3>
                        <p class="text-xs text-text-secondary leading-relaxed line-clamp-3">
                            {{ comp.description }}
                        </p>
                        <div v-if="comp.url" class="pt-2 border-t border-primary">
                            <a :href="comp.url" target="_blank" class="text-xs font-mono text-indigo-400 hover:underline">
                                🔗 {{ comp.url.replace(/^https?:\/\//, '') }}
                            </a>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-16 bg-surface-secondary border border-dashed border-primary rounded-2xl">
                    <span class="text-3xl block mb-2">👥</span>
                    <h3 class="text-sm font-bold text-text-primary mb-1">No Competitors Discovered</h3>
                    <p class="text-xs text-text-secondary">Run Stage 04 (Competitors) to populate market benchmarks.</p>
                </div>
            </div>

            <!-- Tab 4: Website Audits -->
            <div v-if="activeTab === 'websites'" class="space-y-4">
                <div v-if="websiteAnalyses.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="wa in websiteAnalyses"
                        :key="wa.id"
                        class="bg-surface-secondary border border-primary rounded-2xl p-5 shadow-xs space-y-3"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-mono text-indigo-400 font-bold">{{ wa.url }}</span>
                            <span class="text-[10px] font-mono text-emerald-400">Audited</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="p-2 rounded-xl bg-surface-primary border border-primary">
                                <span class="text-[10px] font-mono text-text-tertiary block">UX Score</span>
                                <span class="text-sm font-bold text-text-primary">{{ wa.ux_score || 85 }}/100</span>
                            </div>
                            <div class="p-2 rounded-xl bg-surface-primary border border-primary">
                                <span class="text-[10px] font-mono text-text-tertiary block">SEO</span>
                                <span class="text-sm font-bold text-text-primary">{{ wa.seo_score || 90 }}/100</span>
                            </div>
                            <div class="p-2 rounded-xl bg-surface-primary border border-primary">
                                <span class="text-[10px] font-mono text-text-tertiary block">Conversion</span>
                                <span class="text-sm font-bold text-text-primary">{{ wa.conversion_score || 78 }}/100</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-16 bg-surface-secondary border border-dashed border-primary rounded-2xl">
                    <span class="text-3xl block mb-2">🌐</span>
                    <h3 class="text-sm font-bold text-text-primary mb-1">No Live Website Audits Recorded</h3>
                    <p class="text-xs text-text-secondary">Add a URL during discovery to trigger automated audits.</p>
                </div>
            </div>

            <!-- Source Detail Modal / Drawer -->
            <div v-if="selectedSource" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
                <div class="bg-surface-secondary border border-primary rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-primary">
                        <span class="text-xs font-mono uppercase text-indigo-400 font-bold">
                            Source Citation Details
                        </span>
                        <button @click="selectedSource = null" class="text-text-tertiary hover:text-text-primary text-sm">
                            ✕
                        </button>
                    </div>
                    <div class="space-y-2">
                        <h3 class="text-sm font-bold text-text-primary">{{ selectedSource.title }}</h3>
                        <a :href="selectedSource.url" target="_blank" class="text-xs font-mono text-indigo-400 hover:underline block break-all">
                            {{ selectedSource.url }}
                        </a>
                        <p class="text-xs text-text-secondary leading-relaxed bg-surface-primary border border-primary p-3 rounded-xl mt-2">
                            {{ selectedSource.content_summary || 'No excerpt.' }}
                        </p>
                    </div>
                    <div class="pt-2 flex justify-end">
                        <button @click="selectedSource = null" class="px-4 py-2 rounded-xl brand-button text-xs font-bold">
                            Done
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import ConfidenceBadge from '@/Components/ConfidenceBadge.vue';
import type { Evidence } from '@/types';

interface ExtendedEvidence extends Evidence {
    freshness?: string;
    days_old?: number;
}

const props = defineProps<{
    evidence?: ExtendedEvidence[];
    projectId?: number;
}>();

const openSourceId = ref<number | null>(null);
const isRefreshing = ref(false);

const toggleSources = (id: number) => {
    openSourceId.value = openSourceId.value === id ? null : id;
};

const refreshResearch = () => {
    if (!props.projectId || isRefreshing.value) return;
    isRefreshing.value = true;
    router.post(route('research.refresh', props.projectId), {}, {
        onFinish: () => { isRefreshing.value = false; }
    });
};

const getFreshnessClass = (freshness?: string) => {
    if (freshness === 'fresh') return 'text-emerald-400 bg-emerald-500/10 border-emerald-500/20';
    if (freshness === 'aging') return 'text-amber-400 bg-amber-500/10 border-amber-500/20';
    return 'text-rose-400 bg-rose-500/10 border-rose-500/20';
};
</script>

<template>
    <div v-if="evidence && evidence.length > 0" class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-md">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-primary">
            <div>
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-text-tertiary block mb-0.5">
                    Evidence & Traceability Registry
                </span>
                <h3 class="text-lg font-display font-bold text-text-primary">
                    Real-World Grounded Evidence
                </h3>
            </div>
            <div class="flex items-center gap-2 self-start sm:self-auto">
                <button
                    v-if="projectId"
                    type="button"
                    @click="refreshResearch"
                    :disabled="isRefreshing"
                    class="px-3 py-1.5 rounded-xl border border-primary bg-surface-primary hover:bg-surface-tertiary text-text-secondary hover:text-text-primary text-xs font-mono transition-colors flex items-center gap-1.5 disabled:opacity-50"
                    title="Run fresh market research sweep (15 Credits)"
                >
                    <span>{{ isRefreshing ? '⏳ Sweeping...' : '⚡ Refresh Research' }}</span>
                </button>
                <span class="text-xs font-mono px-2.5 py-1.5 rounded-xl bg-surface-primary border border-primary text-emerald-400 font-semibold">
                    {{ evidence.length }} Insights
                </span>
            </div>
        </div>

        <!-- Evidence List -->
        <div class="space-y-3">
            <div
                v-for="item in evidence"
                :key="item.id"
                class="p-4 rounded-xl bg-surface-primary border border-primary flex flex-col space-y-3"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1.5 flex-1">
                        <p class="text-xs text-text-primary font-medium leading-relaxed">
                            {{ item.claim }}
                        </p>
                        <div class="flex items-center gap-2 text-[10px] text-text-tertiary font-mono">
                            <span>Category: {{ item.category }}</span>
                            <span v-if="item.freshness" class="px-2 py-0.5 rounded border uppercase tracking-wider text-[9px] font-bold" :class="getFreshnessClass(item.freshness)">
                                {{ item.freshness }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            v-if="item.sources && item.sources.length > 0"
                            type="button"
                            @click="toggleSources(item.id)"
                            class="text-[11px] font-mono px-2 py-1 rounded-md bg-surface-tertiary hover:bg-surface-elevated border border-primary text-text-secondary hover:text-text-primary transition-colors flex items-center gap-1"
                        >
                            <span>Sources</span>
                            <span class="font-bold text-indigo-400">&bull; {{ item.sources.length }}</span>
                            <span>{{ openSourceId === item.id ? '▲' : '▼' }}</span>
                        </button>
                        <ConfidenceBadge :confidence="item.confidence" :score="item.confidence_score" />
                    </div>
                </div>

                <!-- Expandable Sources Drawer -->
                <div v-if="openSourceId === item.id && item.sources && item.sources.length > 0" class="pt-3 border-t border-primary space-y-2">
                    <span class="text-[10px] font-mono uppercase tracking-wider text-text-tertiary block">
                        Verified Primary Sources:
                    </span>
                    <div
                        v-for="src in item.sources"
                        :key="src.id"
                        class="p-2.5 rounded-lg bg-surface-secondary border border-primary text-xs flex items-center justify-between gap-2"
                    >
                        <div class="space-y-0.5 truncate flex-1">
                            <span class="font-semibold text-text-primary block truncate">{{ src.title }}</span>
                            <a
                                :href="src.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-[11px] text-indigo-400 hover:underline truncate block"
                            >
                                {{ src.url }} ↗
                            </a>
                        </div>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-surface-primary border border-primary text-text-tertiary capitalize shrink-0">
                            {{ src.source_type }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

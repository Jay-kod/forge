<script setup lang="ts">
import { ref } from 'vue';
import ConfidenceBadge from '@/Components/ConfidenceBadge.vue';
import type { Evidence } from '@/types';

defineProps<{
    evidence?: Evidence[];
}>();

const openSourceId = ref<number | null>(null);

const toggleSources = (id: number) => {
    openSourceId.value = openSourceId.value === id ? null : id;
};
</script>

<template>
    <div v-if="evidence && evidence.length > 0" class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-md">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-text-tertiary block mb-0.5">
                    Evidence & Traceability Registry
                </span>
                <h3 class="text-lg font-display font-bold text-text-primary">
                    Real-World Grounded Evidence
                </h3>
            </div>
            <span class="text-xs font-mono px-2.5 py-1 rounded-md bg-surface-tertiary border border-primary text-emerald-400 font-semibold">
                {{ evidence.length }} Verified Insights
            </span>
        </div>

        <!-- Evidence List -->
        <div class="space-y-3">
            <div
                v-for="item in evidence"
                :key="item.id"
                class="p-4 rounded-xl bg-surface-primary border border-primary flex flex-col space-y-3"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-1 flex-1">
                        <p class="text-xs text-text-primary font-medium leading-relaxed">
                            {{ item.claim }}
                        </p>
                        <span class="text-[10px] text-text-tertiary font-mono block">
                            Category: {{ item.category }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <button
                            v-if="item.sources && item.sources.length > 0"
                            type="button"
                            @click="toggleSources(item.id)"
                            class="text-[11px] font-mono px-2 py-1 rounded-md bg-surface-tertiary hover:bg-surface-elevated border border-primary text-text-secondary hover:text-text-primary transition-colors flex items-center gap-1"
                        >
                            <span>Sources</span>
                            <span class="font-bold text-indigo-400">· {{ item.sources.length }}</span>
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

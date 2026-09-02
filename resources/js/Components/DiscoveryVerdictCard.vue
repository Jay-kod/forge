<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    discovery?: {
        verdict: 'build_as_proposed' | 'build_with_modifications' | 'consider_alternative' | 'do_not_build_yet' | string;
        summary: string;
        rationale: string | null;
    } | null;
}>();

const verdictColorClass = computed(() => {
    switch (props.discovery?.verdict) {
        case 'build_as_proposed': return 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30';
        case 'build_with_modifications': return 'bg-indigo-500/15 text-indigo-400 border-indigo-500/30';
        case 'consider_alternative': return 'bg-amber-500/15 text-amber-400 border-amber-500/30';
        case 'do_not_build_yet': return 'bg-red-500/15 text-red-400 border-red-500/30';
        default: return 'bg-surface-tertiary text-text-primary border-primary';
    }
});

const verdictLabel = computed(() => {
    switch (props.discovery?.verdict) {
        case 'build_as_proposed': return 'BUILD AS PROPOSED';
        case 'build_with_modifications': return 'BUILD WITH MODIFICATIONS';
        case 'consider_alternative': return 'CONSIDER AN ALTERNATIVE';
        case 'do_not_build_yet': return 'DO NOT BUILD THIS VERSION YET';
        default: return 'ANALYZING VERDICT';
    }
});
</script>

<template>
    <div v-if="discovery" class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-md">
        <!-- Header with Badge -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <span class="text-xs font-mono font-bold uppercase tracking-wider text-text-tertiary">
                Existence & Discovery Verdict
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold border tracking-wide" :class="verdictColorClass">
                <span class="w-2 h-2 rounded-full bg-current"></span>
                <span>{{ verdictLabel }}</span>
            </span>
        </div>

        <!-- Summary -->
        <p class="text-sm font-semibold text-text-primary mb-3 leading-relaxed">
            {{ discovery.summary }}
        </p>

        <!-- Rationale -->
        <div v-if="discovery.rationale" class="p-4 rounded-xl bg-surface-primary border border-primary text-xs text-text-secondary leading-relaxed space-y-1">
            <span class="font-bold text-text-primary font-mono text-[11px] block uppercase">
                Strategic Rationale:
            </span>
            <p>{{ discovery.rationale }}</p>
        </div>
    </div>
</template>

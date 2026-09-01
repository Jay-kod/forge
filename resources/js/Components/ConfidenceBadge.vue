<script setup lang="ts">
import { computed } from 'vue';
import type { ConfidenceLevel } from '@/types';

const props = defineProps<{
    confidence: ConfidenceLevel | string;
    score?: number | null;
}>();

const badgeClass = computed(() => {
    switch (props.confidence) {
        case 'verified': return 'confidence-badge-verified';
        case 'strongly_supported': return 'confidence-badge-strong';
        case 'probable': return 'confidence-badge-probable';
        case 'inferred': return 'confidence-badge-inferred';
        case 'assumption': return 'confidence-badge-assumption';
        case 'conflicting': return 'confidence-badge-conflicting';
        default: return 'bg-surface-tertiary text-text-tertiary border-primary';
    }
});

const label = computed(() => {
    switch (props.confidence) {
        case 'verified': return 'Verified';
        case 'strongly_supported': return 'Strongly Supported';
        case 'probable': return 'Probable';
        case 'inferred': return 'Inferred';
        case 'assumption': return 'Assumption';
        case 'conflicting': return 'Conflicting';
        default: return 'Unknown';
    }
});
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-mono font-medium tracking-tight"
        :class="badgeClass"
    >
        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
        <span>{{ label }}</span>
        <span v-if="score" class="opacity-75">({{ Math.round(score * 100) }}%)</span>
    </span>
</template>

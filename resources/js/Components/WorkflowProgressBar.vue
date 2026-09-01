<script setup lang="ts">
import type { WorkflowStage } from '@/types';

defineProps<{
    stages: WorkflowStage[];
    currentStageType?: string | null;
}>();
</script>

<template>
    <div class="w-full bg-surface-secondary border border-primary rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-mono font-semibold uppercase tracking-wider text-text-tertiary">
                Intelligence Loop Progress
            </span>
            <span class="text-xs text-text-secondary font-mono">
                {{ stages.filter(s => s.status === 'completed').length }} of {{ stages.length }} Completed
            </span>
        </div>

        <!-- Progress step indicator -->
        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-8 gap-2">
            <div
                v-for="stage in stages"
                :key="stage.id"
                class="flex flex-col p-2.5 rounded-lg border text-xs transition-all"
                :class="{
                    'bg-emerald-500/10 border-emerald-500/30 text-emerald-400': stage.status === 'completed',
                    'bg-indigo-500/10 border-indigo-500/40 text-indigo-400 ring-1 ring-indigo-500/30': stage.status === 'active',
                    'bg-surface-tertiary border-primary text-text-tertiary': stage.status === 'pending',
                }"
            >
                <div class="flex items-center justify-between font-mono text-[10px] mb-1">
                    <span>#0{{ stage.order }}</span>
                    <span v-if="stage.status === 'completed'">✓</span>
                    <span v-else-if="stage.status === 'active'" class="animate-pulse">●</span>
                </div>
                <span class="font-medium capitalize truncate" :title="stage.stage_type">
                    {{ stage.stage_type }}
                </span>
            </div>
        </div>
    </div>
</template>

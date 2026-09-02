<script setup lang="ts">
import type { Competitor } from '@/types';

defineProps<{
    competitors?: Competitor[];
}>();
</script>

<template>
    <div v-if="competitors && competitors.length > 0" class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-md">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-text-tertiary block mb-0.5">
                    Competitive Intelligence Matrix
                </span>
                <h3 class="text-lg font-display font-bold text-text-primary">
                    Market Landscape & Alternatives
                </h3>
            </div>
            <span class="text-xs font-mono px-2.5 py-1 rounded-md bg-surface-tertiary border border-primary text-text-secondary">
                {{ competitors.length }} Mapped Competitors
            </span>
        </div>

        <!-- Competitor Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
                v-for="comp in competitors"
                :key="comp.id"
                class="p-4 rounded-xl bg-surface-primary border border-primary flex flex-col space-y-3 hover:border-indigo-500/40 transition-colors"
            >
                <!-- Title & Category -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-sm text-text-primary font-display">{{ comp.name }}</span>
                        <a
                            v-if="comp.url"
                            :href="comp.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-xs text-indigo-400 hover:underline inline-flex items-center"
                            title="Visit source website"
                        >
                            ↗
                        </a>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase"
                        :class="{
                            'bg-red-500/15 text-red-400 border border-red-500/30': comp.category === 'direct',
                            'bg-amber-500/15 text-amber-400 border border-amber-500/30': comp.category === 'adjacent',
                            'bg-blue-500/15 text-blue-400 border border-blue-500/30': comp.category === 'indirect',
                        }"
                    >
                        {{ comp.category }}
                    </span>
                </div>

                <!-- Description -->
                <p class="text-xs text-text-secondary leading-relaxed">
                    {{ comp.description }}
                </p>

                <!-- Strengths & Weaknesses -->
                <div class="grid grid-cols-2 gap-2 text-[11px] pt-2 border-t border-primary">
                    <div>
                        <span class="font-bold text-emerald-400 block mb-1">Strengths:</span>
                        <ul class="space-y-0.5 text-text-secondary">
                            <li v-for="s in comp.strengths" :key="s" class="line-clamp-1">• {{ s }}</li>
                        </ul>
                    </div>
                    <div>
                        <span class="font-bold text-red-400 block mb-1">Weaknesses:</span>
                        <ul class="space-y-0.5 text-text-secondary">
                            <li v-for="w in comp.weaknesses" :key="w" class="line-clamp-1">• {{ w }}</li>
                        </ul>
                    </div>
                </div>

                <!-- Differentiation Wedge -->
                <div v-if="comp.differentiation" class="p-2.5 rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-[11px] text-indigo-300">
                    <span class="font-bold font-mono text-[10px] uppercase block mb-0.5 text-indigo-400">
                        FORGE Differentiation Angle:
                    </span>
                    <p class="leading-relaxed">{{ comp.differentiation }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

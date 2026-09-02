<script setup lang="ts">
import { computed } from 'vue';

interface PerformanceHints {
    response_time_ms?: number;
    page_size_kb?: number;
    has_ssl?: boolean;
    has_viewport?: boolean;
}

interface WebsiteAnalysis {
    id?: number;
    url: string;
    status: string;
    meta_title?: string;
    meta_description?: string;
    ux_score: number;
    seo_score: number;
    conversion_score: number;
    performance_hints?: PerformanceHints;
    conversion_findings?: {
        bottlenecks?: string[];
        primary_headline?: string;
        cta_detected?: boolean;
        social_proof_detected?: boolean;
    };
    recommendations?: string[];
}

const props = defineProps<{
    analysis?: WebsiteAnalysis | null;
}>();

const overallScore = computed(() => {
    if (!props.analysis) return 0;
    return Math.round((props.analysis.ux_score + props.analysis.seo_score + props.analysis.conversion_score) / 3);
});

const getScoreColor = (score: number) => {
    if (score >= 80) return 'text-emerald-400 bg-emerald-500/15 border-emerald-500/30';
    if (score >= 65) return 'text-amber-400 bg-amber-500/15 border-amber-500/30';
    return 'text-rose-400 bg-rose-500/15 border-rose-500/30';
};
</script>

<template>
    <div v-if="analysis" class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-md">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-primary mb-6">
            <div>
                <span class="text-[11px] font-mono uppercase text-indigo-400 font-semibold block mb-0.5">
                    Live Audit & Optimization Intelligence
                </span>
                <h3 class="text-xl font-display font-bold text-text-primary flex items-center gap-2">
                    <span>🌐 Website Performance & UX Audit</span>
                </h3>
            </div>
            <a
                :href="analysis.url"
                target="_blank"
                rel="noopener noreferrer"
                class="px-3 py-1.5 rounded-xl border border-primary bg-surface-primary hover:bg-surface-tertiary text-text-secondary hover:text-text-primary text-xs font-mono transition-colors flex items-center gap-1.5 self-start sm:self-auto"
            >
                <span>🔗 {{ analysis.url }}</span>
                <span class="text-text-tertiary">↗</span>
            </a>
        </div>

        <!-- Metric Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="p-4 rounded-xl bg-surface-primary border border-primary text-center">
                <span class="text-[11px] font-mono text-text-tertiary uppercase block mb-1">Overall Health</span>
                <div class="text-2xl font-display font-black tracking-tight" :class="getScoreColor(overallScore).split(' ')[0]">
                    {{ overallScore }}<span class="text-xs text-text-tertiary font-normal">/100</span>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-surface-primary border border-primary text-center">
                <span class="text-[11px] font-mono text-text-tertiary uppercase block mb-1">UX & Readability</span>
                <div class="text-2xl font-display font-black tracking-tight" :class="getScoreColor(analysis.ux_score).split(' ')[0]">
                    {{ analysis.ux_score }}<span class="text-xs text-text-tertiary font-normal">/100</span>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-surface-primary border border-primary text-center">
                <span class="text-[11px] font-mono text-text-tertiary uppercase block mb-1">SEO Structure</span>
                <div class="text-2xl font-display font-black tracking-tight" :class="getScoreColor(analysis.seo_score).split(' ')[0]">
                    {{ analysis.seo_score }}<span class="text-xs text-text-tertiary font-normal">/100</span>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-surface-primary border border-primary text-center">
                <span class="text-[11px] font-mono text-text-tertiary uppercase block mb-1">Conversion Readiness</span>
                <div class="text-2xl font-display font-black tracking-tight" :class="getScoreColor(analysis.conversion_score).split(' ')[0]">
                    {{ analysis.conversion_score }}<span class="text-xs text-text-tertiary font-normal">/100</span>
                </div>
            </div>
        </div>

        <!-- Technical Signals & Performance Badges -->
        <div class="flex flex-wrap items-center gap-2 mb-6 p-3 rounded-xl bg-surface-primary border border-primary text-xs font-mono">
            <span class="text-text-tertiary mr-1">Signals:</span>
            <span
                class="px-2.5 py-0.5 rounded-md border text-[11px]"
                :class="analysis.performance_hints?.has_ssl ? 'text-emerald-400 border-emerald-500/30 bg-emerald-500/10' : 'text-rose-400 border-rose-500/30 bg-rose-500/10'"
            >
                {{ analysis.performance_hints?.has_ssl ? '🔒 SSL Encrypted' : '⚠ No SSL' }}
            </span>
            <span
                class="px-2.5 py-0.5 rounded-md border text-[11px]"
                :class="analysis.performance_hints?.has_viewport ? 'text-emerald-400 border-emerald-500/30 bg-emerald-500/10' : 'text-rose-400 border-rose-500/30 bg-rose-500/10'"
            >
                {{ analysis.performance_hints?.has_viewport ? '📱 Mobile Viewport' : '⚠ Mobile Unfriendly' }}
            </span>
            <span v-if="analysis.performance_hints?.response_time_ms" class="px-2.5 py-0.5 rounded-md border border-primary text-[11px] text-text-secondary bg-surface-secondary">
                ⚡ {{ analysis.performance_hints.response_time_ms }}ms Response
            </span>
            <span v-if="analysis.performance_hints?.page_size_kb" class="px-2.5 py-0.5 rounded-md border border-primary text-[11px] text-text-secondary bg-surface-secondary">
                📦 {{ analysis.performance_hints.page_size_kb }} KB Payload
            </span>
        </div>

        <!-- Findings & Recommendations Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Bottlenecks -->
            <div class="p-4 rounded-xl bg-surface-primary border border-primary">
                <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-rose-400 mb-3 flex items-center gap-1.5">
                    <span>⚠ Conversion Leaks & Bottlenecks</span>
                </h4>
                <ul class="space-y-2 text-xs text-text-secondary leading-relaxed">
                    <li
                        v-for="(item, idx) in (analysis.conversion_findings?.bottlenecks || ['Hero headline lacks clear outcome statement.', 'No social proof or customer reviews visible above the fold.'])"
                        :key="idx"
                        class="flex items-start gap-2"
                    >
                        <span class="text-rose-400 font-bold">&bull;</span>
                        <span>{{ item }}</span>
                    </li>
                </ul>
            </div>

            <!-- Priority Recommendations -->
            <div class="p-4 rounded-xl bg-surface-primary border border-primary">
                <h4 class="text-xs font-mono font-bold uppercase tracking-wider text-emerald-400 mb-3 flex items-center gap-1.5">
                    <span>✓ Recommended Action Steps</span>
                </h4>
                <ul class="space-y-2 text-xs text-text-secondary leading-relaxed">
                    <li
                        v-for="(item, idx) in (analysis.recommendations || ['Clarify value proposition in primary H1.', 'Add persistent CTA button on mobile.', 'Embed trust signals below hero.'])"
                        :key="idx"
                        class="flex items-start gap-2"
                    >
                        <span class="text-emerald-400 font-bold">&bull;</span>
                        <span>{{ item }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

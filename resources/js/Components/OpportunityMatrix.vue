<script setup lang="ts">
import { computed } from 'vue';

interface Opportunity {
    id: number;
    title: string;
    description: string;
    category: string;
    impact: string;
    difficulty: string;
    confidence: string;
    confidence_score?: number;
    quadrant?: string;
    priority_score?: number;
}

const props = defineProps<{
    opportunities?: Opportunity[];
}>();

const quickWins = computed(() => {
    return (props.opportunities || []).filter(o => o.quadrant === 'quick_wins' || (!o.quadrant && (o.impact === 'high' || o.impact === 'critical') && (o.difficulty === 'low' || o.difficulty === 'medium')));
});

const majorProjects = computed(() => {
    return (props.opportunities || []).filter(o => o.quadrant === 'major_projects' || (!o.quadrant && (o.impact === 'high' || o.impact === 'critical') && (o.difficulty === 'high' || o.difficulty === 'extreme')));
});

const fillIns = computed(() => {
    return (props.opportunities || []).filter(o => o.quadrant === 'fill_ins' || (!o.quadrant && (o.impact === 'low' || o.impact === 'medium') && (o.difficulty === 'low' || o.difficulty === 'medium')));
});

const thanklessTasks = computed(() => {
    return (props.opportunities || []).filter(o => o.quadrant === 'thankless_tasks' || (!o.quadrant && (o.impact === 'low' || o.impact === 'medium') && (o.difficulty === 'high' || o.difficulty === 'extreme')));
});
</script>

<template>
    <div v-if="opportunities && opportunities.length > 0" class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-md">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-primary mb-6">
            <div>
                <span class="text-[11px] font-mono uppercase text-indigo-400 font-semibold block mb-0.5">
                    Growth Levers & Prioritization
                </span>
                <h3 class="text-xl font-display font-bold text-text-primary flex items-center gap-2">
                    <span>🎯 Opportunity & Growth Action Matrix</span>
                </h3>
            </div>
            <span class="px-3 py-1 rounded-xl text-xs font-mono bg-surface-primary border border-primary text-text-secondary self-start sm:self-auto">
                {{ opportunities.length }} Opportunities Mapped
            </span>
        </div>

        <!-- 2x2 Quadrant Matrix -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Quadrant 1: Quick Wins (High Impact, Low Effort) -->
            <div class="p-5 rounded-xl bg-surface-primary border border-emerald-500/30">
                <div class="flex items-center justify-between pb-3 border-b border-primary mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">⚡</span>
                        <h4 class="text-sm font-display font-bold text-emerald-400">Quick Wins</h4>
                    </div>
                    <span class="text-[10px] font-mono uppercase tracking-wider text-emerald-400/80 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20 font-semibold">
                        High Impact &bull; Low Effort
                    </span>
                </div>

                <div v-if="quickWins.length > 0" class="space-y-3">
                    <div
                        v-for="item in quickWins"
                        :key="item.id"
                        class="p-3 rounded-lg bg-surface-secondary border border-primary"
                    >
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-xs font-bold text-text-primary">{{ item.title }}</span>
                            <span v-if="item.priority_score" class="text-[10px] font-mono font-bold text-emerald-400 px-1.5 py-0.5 rounded bg-emerald-500/10">
                                Score {{ item.priority_score }}
                            </span>
                        </div>
                        <p class="text-[11px] text-text-secondary leading-relaxed line-clamp-2">
                            {{ item.description }}
                        </p>
                    </div>
                </div>
                <div v-else class="text-xs text-text-tertiary italic py-4 text-center font-mono">
                    No immediate quick wins identified in this sweep.
                </div>
            </div>

            <!-- Quadrant 2: Major Projects (High Impact, High Effort) -->
            <div class="p-5 rounded-xl bg-surface-primary border border-indigo-500/30">
                <div class="flex items-center justify-between pb-3 border-b border-primary mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🚀</span>
                        <h4 class="text-sm font-display font-bold text-indigo-400">Major Projects</h4>
                    </div>
                    <span class="text-[10px] font-mono uppercase tracking-wider text-indigo-400/80 bg-indigo-500/10 px-2 py-0.5 rounded border border-indigo-500/20 font-semibold">
                        High Impact &bull; High Effort
                    </span>
                </div>

                <div v-if="majorProjects.length > 0" class="space-y-3">
                    <div
                        v-for="item in majorProjects"
                        :key="item.id"
                        class="p-3 rounded-lg bg-surface-secondary border border-primary"
                    >
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-xs font-bold text-text-primary">{{ item.title }}</span>
                            <span v-if="item.priority_score" class="text-[10px] font-mono font-bold text-indigo-400 px-1.5 py-0.5 rounded bg-indigo-500/10">
                                Score {{ item.priority_score }}
                            </span>
                        </div>
                        <p class="text-[11px] text-text-secondary leading-relaxed line-clamp-2">
                            {{ item.description }}
                        </p>
                    </div>
                </div>
                <div v-else class="text-xs text-text-tertiary italic py-4 text-center font-mono">
                    No major infrastructure projects in this backlog.
                </div>
            </div>

            <!-- Quadrant 3: Fill-ins (Low Impact, Low Effort) -->
            <div class="p-5 rounded-xl bg-surface-primary border border-amber-500/30">
                <div class="flex items-center justify-between pb-3 border-b border-primary mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🔧</span>
                        <h4 class="text-sm font-display font-bold text-amber-400">Fill-ins & Enhancements</h4>
                    </div>
                    <span class="text-[10px] font-mono uppercase tracking-wider text-amber-400/80 bg-amber-500/10 px-2 py-0.5 rounded border border-amber-500/20 font-semibold">
                        Low Impact &bull; Low Effort
                    </span>
                </div>

                <div v-if="fillIns.length > 0" class="space-y-3">
                    <div
                        v-for="item in fillIns"
                        :key="item.id"
                        class="p-3 rounded-lg bg-surface-secondary border border-primary"
                    >
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-xs font-bold text-text-primary">{{ item.title }}</span>
                            <span v-if="item.priority_score" class="text-[10px] font-mono font-bold text-amber-400 px-1.5 py-0.5 rounded bg-amber-500/10">
                                Score {{ item.priority_score }}
                            </span>
                        </div>
                        <p class="text-[11px] text-text-secondary leading-relaxed line-clamp-2">
                            {{ item.description }}
                        </p>
                    </div>
                </div>
                <div v-else class="text-xs text-text-tertiary italic py-4 text-center font-mono">
                    No minor fill-in items identified.
                </div>
            </div>

            <!-- Quadrant 4: Thankless Tasks (Low Impact, High Effort) -->
            <div class="p-5 rounded-xl bg-surface-primary border border-primary opacity-80">
                <div class="flex items-center justify-between pb-3 border-b border-primary mb-3">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">⛔</span>
                        <h4 class="text-sm font-display font-bold text-text-secondary">Low Priority / Deprioritize</h4>
                    </div>
                    <span class="text-[10px] font-mono uppercase tracking-wider text-text-tertiary bg-surface-secondary px-2 py-0.5 rounded border border-primary font-semibold">
                        Low Impact &bull; High Effort
                    </span>
                </div>

                <div v-if="thanklessTasks.length > 0" class="space-y-3">
                    <div
                        v-for="item in thanklessTasks"
                        :key="item.id"
                        class="p-3 rounded-lg bg-surface-secondary border border-primary"
                    >
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-xs font-medium text-text-secondary">{{ item.title }}</span>
                        </div>
                        <p class="text-[11px] text-text-tertiary leading-relaxed line-clamp-2">
                            {{ item.description }}
                        </p>
                    </div>
                </div>
                <div v-else class="text-xs text-text-tertiary italic py-4 text-center font-mono">
                    Zero high-friction low-value tasks identified.
                </div>
            </div>
        </div>
    </div>
</template>

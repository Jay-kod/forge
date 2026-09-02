<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    audit: {
        type: Object,
        required: true,
    },
    project: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['open-export', 'rescan'])

const showAllFindings = ref(false)

const findings = computed(() => {
    return props.audit.raw_metrics?.findings || []
})

const visibleFindings = computed(() => {
    return showAllFindings.value ? findings.value : findings.value.slice(0, 3)
})

const healthColor = computed(() => {
    const score = props.audit.code_health_score || 0
    if (score >= 80) return 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800'
    if (score >= 60) return 'text-amber-500 bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800'
    return 'text-rose-500 bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800'
})

const debtColor = computed(() => {
    const score = props.audit.technical_debt_score || 0
    if (score <= 25) return 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800'
    if (score <= 50) return 'text-amber-500 bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800'
    return 'text-rose-500 bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800'
})

const securityColor = computed(() => {
    const score = props.audit.security_score || 0
    if (score >= 85) return 'text-emerald-500 bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-800'
    if (score >= 70) return 'text-amber-500 bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-800'
    return 'text-rose-500 bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800'
})

const getSeverityClass = (severity) => {
    switch (severity?.toLowerCase()) {
        case 'critical':
            return 'bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300 border-rose-300'
        case 'high':
            return 'bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300 border-amber-300'
        case 'medium':
            return 'bg-blue-100 dark:bg-blue-900/60 text-blue-700 dark:text-blue-300 border-blue-300'
        default:
            return 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-300'
    }
}
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-6">
        <!-- Card Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-slate-800">
            <div class="space-y-1">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xl font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>🐙</span>
                        <span>{{ audit.repo_full_name }}</span>
                    </span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                        branch: {{ audit.default_branch || 'main' }}
                    </span>
                    <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                        {{ audit.primary_language || 'Codebase' }}
                    </span>
                    <span v-if="audit.detected_framework" class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-emerald-50 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                        {{ audit.detected_framework }}
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Architecture Pattern: <span class="font-medium text-slate-700 dark:text-slate-300">{{ audit.architecture_pattern || 'Standard' }}</span> • Tracked Files: {{ audit.file_count || 0 }}
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <button
                    @click="$emit('rescan')"
                    class="px-3 py-1.5 text-xs font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-lg transition-colors"
                >
                    ⚡ Re-scan
                </button>
                <button
                    @click="$emit('open-export')"
                    class="px-3.5 py-1.5 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 rounded-lg shadow-sm transition-all flex items-center gap-1.5"
                >
                    <span>🐙</span>
                    <span>Export to GitHub PR</span>
                </button>
            </div>
        </div>

        <!-- Metric Gauges (3-column) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Code Health -->
            <div class="p-4 rounded-xl border flex flex-col justify-between" :class="healthColor">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider">Code Health Score</span>
                    <span class="text-lg">📈</span>
                </div>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="text-3xl font-black">{{ audit.code_health_score }}</span>
                    <span class="text-xs font-semibold opacity-75">/ 100</span>
                </div>
                <div class="text-[11px] mt-1 opacity-90">
                    {{ audit.code_health_score >= 80 ? 'Optimal codebase health' : audit.code_health_score >= 60 ? 'Moderate maintenance required' : 'High remediation priority' }}
                </div>
            </div>

            <!-- Technical Debt Index -->
            <div class="p-4 rounded-xl border flex flex-col justify-between" :class="debtColor">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider">Technical Debt Index</span>
                    <span class="text-lg">⚠️</span>
                </div>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="text-3xl font-black">{{ audit.technical_debt_score }}</span>
                    <span class="text-xs font-semibold opacity-75">/ 100</span>
                </div>
                <div class="text-[11px] mt-1 opacity-90">
                    {{ audit.technical_debt_score <= 25 ? 'Low architectural drag' : audit.technical_debt_score <= 50 ? 'Manageable legacy items' : 'Compounding velocity bottleneck' }}
                </div>
            </div>

            <!-- Security Posture -->
            <div class="p-4 rounded-xl border flex flex-col justify-between" :class="securityColor">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider">Security Posture</span>
                    <span class="text-lg">🛡️</span>
                </div>
                <div class="mt-2 flex items-baseline gap-1">
                    <span class="text-3xl font-black">{{ audit.security_score }}</span>
                    <span class="text-xs font-semibold opacity-75">/ 100</span>
                </div>
                <div class="text-[11px] mt-1 opacity-90">
                    {{ audit.security_score >= 85 ? 'Strong security posture' : 'Vulnerabilities detected' }}
                </div>
            </div>
        </div>

        <!-- Technical Debt Findings -->
        <div v-if="findings.length > 0" class="space-y-3 pt-2">
            <div class="flex items-center justify-between">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                    Diagnosed Codebase Findings & Modernization Actions ({{ findings.length }})
                </h4>
                <button
                    v-if="findings.length > 3"
                    @click="showAllFindings = !showAllFindings"
                    class="text-xs text-indigo-600 dark:text-indigo-400 font-medium hover:underline"
                >
                    {{ showAllFindings ? 'Show Less' : `View All ${findings.length}` }}
                </button>
            </div>

            <div class="space-y-2.5">
                <div
                    v-for="finding in visibleFindings"
                    :key="finding.id"
                    class="p-3.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl space-y-1.5"
                >
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span
                                class="text-[10px] font-bold px-2 py-0.5 rounded border uppercase"
                                :class="getSeverityClass(finding.severity)"
                            >
                                {{ finding.severity }}
                            </span>
                            <span class="text-sm font-bold text-slate-900 dark:text-white">
                                {{ finding.title }}
                            </span>
                        </div>
                        <span v-if="finding.file_path" class="text-[11px] font-mono text-slate-500 dark:text-slate-400 bg-white dark:bg-slate-900 px-2 py-0.5 rounded border border-slate-200 dark:border-slate-700">
                            {{ finding.file_path }}
                        </span>
                    </div>

                    <p class="text-xs text-slate-600 dark:text-slate-300">
                        {{ finding.description }}
                    </p>

                    <div class="text-xs text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 p-2 rounded-lg border border-emerald-200/60 dark:border-emerald-800/40 flex items-start gap-1.5 mt-1">
                        <span class="font-bold">Recommendation:</span>
                        <span>{{ finding.recommended_action }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

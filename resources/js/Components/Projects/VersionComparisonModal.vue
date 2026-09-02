<script setup>
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
    isOpen: {
        type: Boolean,
        default: false,
    },
})

const emit = defineEmits(['close'])

const versions = ref([])
const version1 = ref(null)
const version2 = ref(null)
const diffResult = ref(null)
const loadingVersions = ref(false)
const loadingDiff = ref(false)
const selectedDocDiff = ref(null)

const fetchVersions = async () => {
    loadingVersions.value = true
    try {
        const res = await axios.get(`/projects/${props.project.id}/versions`)
        versions.value = res.data.versions || []
        if (versions.value.length >= 2) {
            version1.value = versions.value[1].id // Older
            version2.value = versions.value[0].id // Newer
            fetchDiff()
        } else if (versions.value.length === 1) {
            version1.value = versions.value[0].id
            version2.value = versions.value[0].id
        }
    } catch (err) {
        console.error('Failed to load project versions', err)
    } finally {
        loadingVersions.value = false
    }
}

const fetchDiff = async () => {
    if (!version1.value || !version2.value) return
    loadingDiff.value = true
    selectedDocDiff.value = null
    try {
        const res = await axios.get(`/projects/${props.project.id}/versions/${version1.value}/diff/${version2.value}`)
        diffResult.value = res.data
        if (res.data.documents_diff && res.data.documents_diff.length > 0) {
            selectedDocDiff.value = res.data.documents_diff[0]
        }
    } catch (err) {
        console.error('Failed to compare versions', err)
    } finally {
        loadingDiff.value = false
    }
}

watch(() => props.isOpen, (open) => {
    if (open) {
        fetchVersions()
    }
})
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 bg-black/70 backdrop-blur-xs flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-4xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🧬</span>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">
                            Project Version Diff & Evolution Comparator
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Compare immutable snapshots, document changes, and strategic delta across stages
                        </p>
                    </div>
                </div>
                <button @click="emit('close')" class="text-slate-400 hover:text-slate-200 text-lg">✕</button>
            </div>

            <!-- Version Selector Bar -->
            <div class="px-6 py-3 bg-slate-50 dark:bg-slate-850 border-b border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-slate-500">Base Version (Old):</label>
                        <select
                            v-model="version1"
                            @change="fetchDiff"
                            class="px-3 py-1 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                        >
                            <option v-for="v in versions" :key="v.id" :value="v.id">
                                v{{ v.version }} ({{ v.created_by }}) - {{ v.note?.slice(0, 30) }}
                            </option>
                        </select>
                    </div>

                    <span class="text-xs text-slate-400">➔</span>

                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold text-slate-500">Target Version (New):</label>
                        <select
                            v-model="version2"
                            @change="fetchDiff"
                            class="px-3 py-1 text-xs rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white"
                        >
                            <option v-for="v in versions" :key="v.id" :value="v.id">
                                v{{ v.version }} ({{ v.created_by }}) - {{ v.note?.slice(0, 30) }}
                            </option>
                        </select>
                    </div>
                </div>

                <!-- Deltas Pill Badge -->
                <div v-if="diffResult?.metrics" class="flex items-center gap-2 text-xs font-mono">
                    <span class="px-2 py-0.5 rounded-md bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 font-bold">
                        Δ Opps: {{ diffResult.metrics.delta_opportunities >= 0 ? '+' : '' }}{{ diffResult.metrics.delta_opportunities }}
                    </span>
                    <span class="px-2 py-0.5 rounded-md bg-blue-100 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 font-bold">
                        Δ Evidence: {{ diffResult.metrics.delta_evidence >= 0 ? '+' : '' }}{{ diffResult.metrics.delta_evidence }}
                    </span>
                </div>
            </div>

            <!-- Content Body -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
                <div v-if="loadingDiff" class="py-16 text-center text-xs text-slate-400 font-mono">
                    ⏳ Computing semantic document diff...
                </div>

                <div v-else-if="!diffResult" class="py-16 text-center text-xs text-slate-400">
                    Select two distinct versions to inspect comparative evolution.
                </div>

                <div v-else class="space-y-6">
                    <!-- Metrics Comparative Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800">
                            <span class="text-[11px] text-slate-500 block">Opportunities</span>
                            <span class="text-base font-bold text-slate-900 dark:text-white">
                                {{ diffResult.metrics.v1_opportunities }} ➔ {{ diffResult.metrics.v2_opportunities }}
                            </span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800">
                            <span class="text-[11px] text-slate-500 block">Evidence Points</span>
                            <span class="text-base font-bold text-slate-900 dark:text-white">
                                {{ diffResult.metrics.v1_evidence }} ➔ {{ diffResult.metrics.v2_evidence }}
                            </span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800">
                            <span class="text-[11px] text-slate-500 block">Competitors Tracked</span>
                            <span class="text-base font-bold text-slate-900 dark:text-white">
                                {{ diffResult.metrics.v1_competitors }} ➔ {{ diffResult.metrics.v2_competitors }}
                            </span>
                        </div>
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800">
                            <span class="text-[11px] text-slate-500 block">Documents Tracked</span>
                            <span class="text-base font-bold text-slate-900 dark:text-white">
                                {{ diffResult.documents_diff?.length || 0 }} Spec(s)
                            </span>
                        </div>
                    </div>

                    <!-- Documents Diff Section -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span>📄</span>
                            <span>Document Specification Diffs</span>
                        </h4>

                        <!-- Document Selector Pills -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <button
                                v-for="doc in diffResult.documents_diff"
                                :key="doc.type"
                                @click="selectedDocDiff = doc"
                                class="px-3 py-1 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1.5"
                                :class="selectedDocDiff?.type === doc.type ? 'bg-indigo-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-white'"
                            >
                                <span>{{ doc.type.toUpperCase() }}</span>
                                <span class="text-[10px] px-1 py-0.2 rounded-full uppercase" :class="doc.status === 'identical' ? 'bg-slate-500/20 text-slate-400' : 'bg-emerald-500/20 text-emerald-400'">
                                    {{ doc.status }}
                                </span>
                            </button>
                        </div>

                        <!-- Diff Viewer Box -->
                        <div v-if="selectedDocDiff" class="rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                            <div class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-xs font-mono font-bold text-slate-700 dark:text-slate-300 flex items-center justify-between">
                                <span>{{ selectedDocDiff.title }} ({{ selectedDocDiff.status }})</span>
                                <span v-if="selectedDocDiff.char_delta" class="text-emerald-500">
                                    {{ selectedDocDiff.char_delta >= 0 ? '+' : '' }}{{ selectedDocDiff.char_delta }} chars
                                </span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-200 dark:divide-slate-800 text-xs font-mono max-h-72 overflow-y-auto">
                                <div class="p-4 bg-rose-50/20 dark:bg-rose-950/10">
                                    <div class="text-[10px] font-bold text-rose-500 uppercase mb-2">v{{ diffResult.version_old }} (Base)</div>
                                    <pre class="whitespace-pre-wrap text-slate-700 dark:text-slate-300 leading-relaxed">{{ selectedDocDiff.content_old || '(Document did not exist in v' + diffResult.version_old + ')' }}</pre>
                                </div>
                                <div class="p-4 bg-emerald-50/20 dark:bg-emerald-950/10">
                                    <div class="text-[10px] font-bold text-emerald-500 uppercase mb-2">v{{ diffResult.version_new }} (Target)</div>
                                    <pre class="whitespace-pre-wrap text-slate-700 dark:text-slate-300 leading-relaxed">{{ selectedDocDiff.content_new || '(Document removed in v' + diffResult.version_new + ')' }}</pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-850 flex items-center justify-end">
                <button
                    @click="emit('close')"
                    class="px-4 py-2 text-xs font-bold rounded-xl bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 hover:bg-slate-300"
                >
                    Close Comparator
                </button>
            </div>
        </div>
    </div>
</template>

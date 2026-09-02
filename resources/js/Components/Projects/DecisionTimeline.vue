<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
    project: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['stageRerun'])

const timeline = ref([])
const loading = ref(true)
const selectedStageForRerun = ref(null)
const rerunNote = ref('')
const isRerunning = ref(false)
const rerunError = ref(null)

const fetchTimeline = async () => {
    loading.value = true
    try {
        const res = await axios.get(`/projects/${props.project.id}/timeline`)
        timeline.value = res.data.timeline
    } catch (err) {
        console.error('Failed to load decision timeline', err)
    } finally {
        loading.value = false
    }
}

const openRerunModal = (stage) => {
    selectedStageForRerun.value = stage
    rerunNote.value = `Re-evaluating ${stage.stage_type} with refreshed market parameters.`
    rerunError.value = null
}

const confirmRerun = async () => {
    if (!selectedStageForRerun.value) return
    isRerunning.value = true
    rerunError.value = null

    try {
        const res = await axios.post(`/projects/${props.project.id}/stages/${selectedStageForRerun.value.id}/rerun`, {
            note: rerunNote.value,
        })
        selectedStageForRerun.value = null
        emit('stageRerun', res.data)
        fetchTimeline()
    } catch (err) {
        rerunError.value = err.response?.data?.error || err.message || 'Failed to re-run stage.'
    } finally {
        isRerunning.value = false
    }
}

const formatDate = (isoStr) => {
    if (!isoStr) return ''
    const d = new Date(isoStr)
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
    fetchTimeline()
})
</script>

<template>
    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                    <span>📜</span>
                    <span>Decision History & Strategic Timeline</span>
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Immutable log of every validated assumption, stage approval, and version snapshot.
                </p>
            </div>
            <button
                @click="fetchTimeline"
                class="px-3 py-1.5 text-xs font-medium rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
            >
                Refresh
            </button>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="py-12 text-center text-xs text-slate-400 font-mono">
            ⏳ Loading chronological history...
        </div>

        <!-- Empty State -->
        <div v-else-if="timeline.length === 0" class="py-12 text-center text-xs text-slate-400">
            No milestones or approved decisions recorded yet.
        </div>

        <!-- Vertical Timeline List -->
        <div v-else class="relative pl-6 space-y-6 before:content-[''] before:absolute before:top-2 before:bottom-2 before:left-[11px] before:w-[2px] before:bg-slate-200 dark:before:bg-slate-800">
            <div
                v-for="(item, idx) in timeline"
                :key="idx"
                class="relative flex items-start gap-4 group"
            >
                <!-- Marker Dot -->
                <div
                    class="absolute -left-6 top-1 w-6 h-6 rounded-full border-2 bg-white dark:bg-slate-900 flex items-center justify-center text-[10px] shadow-xs"
                    :class="
                        item.type === 'stage_approval'
                            ? 'border-emerald-500 text-emerald-500'
                            : item.type === 'decision'
                            ? 'border-indigo-500 text-indigo-500'
                            : 'border-blue-500 text-blue-500'
                    "
                >
                    <span v-if="item.type === 'stage_approval'">✓</span>
                    <span v-else-if="item.type === 'decision'">⚖️</span>
                    <span v-else>v</span>
                </div>

                <!-- Event Card -->
                <div class="flex-1 bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800 rounded-xl p-4 transition-all hover:border-slate-300 dark:hover:border-slate-700">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 flex-wrap">
                            <!-- Type Pill -->
                            <span
                                class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded-md"
                                :class="
                                    item.type === 'stage_approval'
                                        ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-400'
                                        : item.type === 'decision'
                                        ? 'bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400'
                                        : 'bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-400'
                                "
                            >
                                {{ item.type.replace('_', ' ') }}
                            </span>

                            <h4 class="text-xs font-bold text-slate-900 dark:text-white">
                                {{ item.type === 'version_snapshot' ? `Version Snapshot v${item.version}` : (item.stage || item.question) }}
                            </h4>
                        </div>

                        <span class="text-[10px] font-mono text-slate-400 whitespace-nowrap">
                            {{ formatDate(item.timestamp) }}
                        </span>
                    </div>

                    <!-- Details Content -->
                    <div class="mt-2 text-xs text-slate-600 dark:text-slate-300">
                        <template v-if="item.type === 'decision'">
                            <div class="p-2.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-1">
                                <div class="font-semibold text-slate-800 dark:text-slate-200">
                                    Selected: {{ item.selected_option }}
                                </div>
                                <div v-if="item.rationale" class="text-slate-500 text-[11px] italic">
                                    "{{ item.rationale }}"
                                </div>
                            </div>
                        </template>

                        <template v-else-if="item.type === 'version_snapshot'">
                            <p class="text-slate-500 dark:text-slate-400">
                                {{ item.note || 'Milestone snapshot created' }}
                            </p>
                            <span class="text-[10px] font-mono text-slate-400 mt-1 inline-block">
                                Created by: <span class="capitalize">{{ item.created_by }}</span>
                            </span>
                        </template>

                        <template v-else-if="item.type === 'stage_approval'">
                            <p v-if="item.content?.summary" class="text-slate-600 dark:text-slate-300">
                                {{ item.content.summary }}
                            </p>
                            <div class="mt-2 flex items-center gap-3 text-[11px] text-slate-400">
                                <span>Status: <span class="text-emerald-500 font-semibold uppercase">{{ item.status }}</span></span>
                                <span v-if="item.content?.evidence_count">• {{ item.content.evidence_count }} evidence pieces</span>
                                <span v-if="item.content?.competitors_mapped">• {{ item.content.competitors_mapped }} competitors</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Re-run Stage Modal -->
        <div v-if="selectedStageForRerun" class="fixed inset-0 bg-black/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 dark:border-slate-800">
                    <h4 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                        <span>🔄</span>
                        <span>Re-run Intelligence Stage</span>
                    </h4>
                    <button @click="selectedStageForRerun = null" class="text-slate-400 hover:text-white">✕</button>
                </div>

                <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 text-xs text-amber-800 dark:text-amber-300 space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <span>🛡️</span>
                        <span>Non-Destructive Evolution Guarantee</span>
                    </div>
                    <p>
                        A version snapshot of your current project state will be archived before execution. Prior decisions and documents are never silently overwritten.
                    </p>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-700 dark:text-slate-300">Regeneration Rationale / Notes</label>
                    <textarea
                        v-model="rerunNote"
                        rows="3"
                        class="w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 p-3 text-xs text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                        placeholder="Why are you re-running this stage?"
                    ></textarea>
                </div>

                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 flex items-center justify-between text-xs">
                    <span class="text-slate-500">Credit Cost:</span>
                    <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">⚡ 15 Credits</span>
                </div>

                <div v-if="rerunError" class="p-3 rounded-xl bg-rose-50 dark:bg-rose-900/30 border border-rose-200 dark:border-rose-800 text-xs text-rose-600 dark:text-rose-400">
                    {{ rerunError }}
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        @click="selectedStageForRerun = null"
                        class="px-4 py-2 text-xs font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmRerun"
                        :disabled="isRerunning"
                        class="px-4 py-2 text-xs font-bold rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white shadow-sm flex items-center gap-1.5 disabled:opacity-50"
                    >
                        <span v-if="isRerunning">⏳ Snapshotting & Re-running...</span>
                        <span v-else>Confirm & Re-run (15 Credits)</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

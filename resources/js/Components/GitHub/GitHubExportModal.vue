<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    project: {
        type: Object,
        required: true,
    },
    audit: {
        type: Object,
        required: true,
    },
})

const emit = defineEmits(['close', 'exported'])

const branchName = ref(`forge/blueprint-${new Date().toISOString().slice(0,10).replace(/-/g,'')}`)
const exporting = ref(false)
const exportResult = ref(null)
const error = ref('')

const handleExport = async () => {
    exporting.value = true
    error.value = ''
    try {
        const response = await axios.post(`/projects/${props.project.id}/github/export`, {
            repo_full_name: props.audit.repo_full_name,
            branch: branchName.value,
        })
        if (response.data.success) {
            exportResult.value = response.data
            emit('exported', response.data)
        }
    } catch (err) {
        error.value = err.response?.data?.error || 'Export failed. Please check repository write permissions.'
    } finally {
        exporting.value = false
    }
}

const reset = () => {
    exportResult.value = null
    error.value = ''
    emit('close')
}
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" @click="reset"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white dark:bg-slate-900 rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 dark:border-slate-800">
                <!-- Header -->
                <div class="px-6 pt-6 pb-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-lg shadow-md dark:bg-slate-800 dark:border dark:border-slate-700">
                            🐙
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                                Export Blueprints to GitHub
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ audit.repo_full_name }}
                            </p>
                        </div>
                    </div>
                    <button @click="reset" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        ✕
                    </button>
                </div>

                <!-- Body -->
                <div class="p-6 space-y-5">
                    <!-- State: Success -->
                    <div v-if="exportResult" class="text-center py-4 space-y-4">
                        <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-3xl">
                            ✓
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-slate-900 dark:text-white">
                                Blueprints Successfully Exported!
                            </h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                Committed {{ exportResult.files_committed?.length || 0 }} specification files to branch
                                <code class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded font-mono text-emerald-600 dark:text-emerald-400">
                                    {{ exportResult.branch }}
                                </code>
                            </p>
                        </div>

                        <!-- Open PR Button -->
                        <div class="pt-2">
                            <a
                                :href="exportResult.pull_request_url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center justify-center gap-2 w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl shadow-lg transition-all"
                            >
                                <span>🐙</span>
                                <span>Open Pull Request on GitHub &rarr;</span>
                            </a>
                        </div>
                    </div>

                    <!-- State: Form -->
                    <div v-else class="space-y-4">
                        <!-- Safety Invariant Banner -->
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl text-xs text-emerald-800 dark:text-emerald-300 space-y-1">
                            <div class="font-semibold flex items-center gap-1.5">
                                <span>🛡️</span> Safety Invariant Enforced
                            </div>
                            <p>
                                FORGE commits to an isolated blueprint branch. Your <code class="bg-emerald-100 dark:bg-emerald-900 px-1 py-0.5 rounded">main</code> branch is completely untouched.
                            </p>
                        </div>

                        <!-- Target Branch input -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">
                                Blueprint Branch Name
                            </label>
                            <input
                                v-model="branchName"
                                type="text"
                                class="w-full text-sm font-mono rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:ring-emerald-500 focus:border-emerald-500"
                                placeholder="forge/blueprint-..."
                            />
                            <p class="text-[11px] text-slate-400 mt-1">
                                Cannot be main or master. Branch will be created from default HEAD.
                            </p>
                        </div>

                        <!-- Files to be committed preview -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Specification Files to Include
                            </label>
                            <div class="space-y-1.5 max-h-40 overflow-y-auto p-3 bg-slate-50 dark:bg-slate-800/60 rounded-xl border border-slate-200 dark:border-slate-700/60 text-xs font-mono text-slate-600 dark:text-slate-300">
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-500">✓</span>
                                    <span>FORGE_BLUEPRINT.md</span>
                                    <span class="text-[10px] text-slate-400 font-sans ml-auto">(Overview & Priorities)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-500">✓</span>
                                    <span>docs/TECHNICAL_DEBT_AUDIT.md</span>
                                    <span class="text-[10px] text-slate-400 font-sans ml-auto">(Debt & Health Index)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-500">✓</span>
                                    <span>docs/REFACTOR_ROADMAP.md</span>
                                    <span class="text-[10px] text-slate-400 font-sans ml-auto">(Modernization Plan)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-500">✓</span>
                                    <span>docs/PRD.md</span>
                                    <span class="text-[10px] text-slate-400 font-sans ml-auto">(Product Requirements)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-500">✓</span>
                                    <span>docs/ARCHITECTURE.md</span>
                                    <span class="text-[10px] text-slate-400 font-sans ml-auto">(System Architecture)</span>
                                </div>
                            </div>
                        </div>

                        <div v-if="error" class="p-3 text-xs text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 rounded-xl border border-rose-200 dark:border-rose-800">
                            {{ error }}
                        </div>

                        <button
                            @click="handleExport"
                            :disabled="exporting || !branchName"
                            class="w-full py-3 px-4 bg-slate-900 hover:bg-slate-800 dark:bg-emerald-600 dark:hover:bg-emerald-500 disabled:opacity-50 text-white font-bold rounded-xl shadow-lg transition-all flex items-center justify-center gap-2"
                        >
                            <span v-if="exporting">⏳ Creating branch & committing blueprints...</span>
                            <span v-else>🚀 Push Blueprint Branch to GitHub</span>
                        </button>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button
                        @click="reset"
                        class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white"
                    >
                        {{ exportResult ? 'Done' : 'Cancel' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

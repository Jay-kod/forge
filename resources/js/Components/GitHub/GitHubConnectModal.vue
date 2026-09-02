<script setup>
import { ref, onMounted } from 'vue'
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
    githubConnection: {
        type: Object,
        default: null,
    },
})

const emit = defineEmits(['close', 'scanned'])

const loadingRepos = ref(false)
const repositories = ref([])
const selectedRepo = ref('')
const scanning = ref(false)
const error = ref('')

const fetchRepositories = async () => {
    if (!props.githubConnection) return
    loadingRepos.value = true
    error.value = ''
    try {
        const response = await axios.get('/integrations/github/repositories')
        repositories.value = response.data.repositories || []
        if (repositories.value.length > 0) {
            selectedRepo.value = repositories.value[0].full_name
        }
    } catch (err) {
        error.value = err.response?.data?.error || 'Failed to load GitHub repositories.'
    } finally {
        loadingRepos.value = false
    }
}

const handleScan = async () => {
    if (!selectedRepo.value) return
    scanning.value = true
    error.value = ''
    try {
        const response = await axios.post(`/projects/${props.project.id}/github/scan`, {
            repo_full_name: selectedRepo.value,
            branch: 'main',
        })
        if (response.data.success) {
            emit('scanned', response.data.audit)
            emit('close')
        }
    } catch (err) {
        error.value = err.response?.data?.error || 'Scan failed. Please check repository permissions.'
    } finally {
        scanning.value = false
    }
}

onMounted(() => {
    if (props.githubConnection) {
        fetchRepositories()
    }
})
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>

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
                                GitHub Repository Intelligence
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Code analysis, technical debt diagnostics & safe export
                            </p>
                        </div>
                    </div>
                    <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        ✕
                    </button>
                </div>

                <div class="p-6 space-y-6">
                    <!-- State 1: Not Connected -->
                    <div v-if="!githubConnection" class="text-center py-4 space-y-4">
                        <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-3xl">
                            🔗
                        </div>
                        <div>
                            <h4 class="text-base font-semibold text-slate-900 dark:text-white">Connect Your GitHub Account</h4>
                            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">
                                Authorize FORGE to inspect repository structures, identify runtime debt, and push modernization blueprints directly to isolated branches.
                            </p>
                        </div>

                        <!-- Safety Guarantee Pill -->
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl text-left text-xs text-emerald-800 dark:text-emerald-300 space-y-1">
                            <div class="font-semibold flex items-center gap-1.5">
                                <span>🛡️</span> Zero-Destruction Safety Invariant
                            </div>
                            <p>
                                FORGE never commits directly to <code class="bg-emerald-100 dark:bg-emerald-900 px-1 py-0.5 rounded">main</code>, never force-pushes, and never deletes repositories or remote branches.
                            </p>
                        </div>

                        <a
                            href="/integrations/github/connect"
                            class="inline-flex items-center justify-center gap-2 w-full py-3 px-4 bg-slate-900 hover:bg-slate-800 text-white font-medium rounded-xl shadow-lg transition-all"
                        >
                            <span>🐙</span>
                            <span>Connect with GitHub</span>
                        </a>
                    </div>

                    <!-- State 2: Connected -->
                    <div v-else class="space-y-4">
                        <!-- User identity card -->
                        <div class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 rounded-xl">
                            <div class="flex items-center space-x-3">
                                <img
                                    v-if="githubConnection.avatar_url"
                                    :src="githubConnection.avatar_url"
                                    alt="GitHub Avatar"
                                    class="w-9 h-9 rounded-full border border-slate-300 dark:border-slate-600"
                                />
                                <div>
                                    <div class="text-sm font-semibold text-slate-900 dark:text-white">
                                        @{{ githubConnection.github_username }}
                                    </div>
                                    <div class="text-xs text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Connected with repo permissions
                                    </div>
                                </div>
                            </div>
                            <form method="POST" action="/integrations/github/disconnect">
                                <input type="hidden" name="_token" :value="$page.props.csrf_token || ''" />
                                <button
                                    type="submit"
                                    class="text-xs text-rose-500 hover:text-rose-600 font-medium px-2 py-1 rounded hover:bg-rose-50 dark:hover:bg-rose-950/40"
                                >
                                    Disconnect
                                </button>
                            </form>
                        </div>

                        <!-- Repo selection -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                                Select Target Repository to Audit
                            </label>

                            <div v-if="loadingRepos" class="py-4 text-center text-sm text-slate-400">
                                ⏳ Fetching repositories...
                            </div>

                            <div v-else-if="repositories.length > 0" class="space-y-3">
                                <select
                                    v-model="selectedRepo"
                                    class="w-full text-sm rounded-xl border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-white focus:ring-emerald-500 focus:border-emerald-500"
                                >
                                    <option v-for="repo in repositories" :key="repo.full_name" :value="repo.full_name">
                                        {{ repo.full_name }} ({{ repo.language || 'Codebase' }})
                                    </option>
                                </select>

                                <button
                                    @click="handleScan"
                                    :disabled="scanning || !selectedRepo"
                                    class="w-full py-3 px-4 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-medium rounded-xl shadow-lg transition-all flex items-center justify-center gap-2"
                                >
                                    <span v-if="scanning">⏳ Scanning codebase structure & metrics...</span>
                                    <span v-else>⚡ Link & Scan Repository</span>
                                </button>
                            </div>

                            <div v-else class="text-sm text-slate-400 text-center py-3">
                                No repositories found. Verify permissions on GitHub.
                            </div>
                        </div>

                        <div v-if="error" class="p-3 text-xs text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 rounded-xl border border-rose-200 dark:border-rose-800">
                            {{ error }}
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/40 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                    <button
                        @click="$emit('close')"
                        class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

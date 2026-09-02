<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'

const isOpen = ref(false)
const alerts = ref([])
const unreadCount = ref(0)
const loading = ref(false)
let pollInterval = null

const fetchAlerts = async () => {
    try {
        const res = await axios.get('/alerts')
        alerts.value = res.data.alerts
        unreadCount.value = res.data.unread_count
    } catch (err) {
        // Silently catch in background poll
    }
}

const toggleDropdown = () => {
    isOpen.value = !isOpen.value
    if (isOpen.value) {
        fetchAlerts()
    }
}

const markAsRead = async (alert) => {
    if (alert.read_at) return
    try {
        await axios.post(`/alerts/${alert.id}/read`)
        alert.read_at = new Date().toISOString()
        unreadCount.value = Math.max(0, unreadCount.value - 1)
    } catch (err) {
        console.error('Failed to mark alert as read', err)
    }
}

const markAllAsRead = async () => {
    try {
        await axios.post('/alerts/read-all')
        alerts.value.forEach(a => a.read_at = new Date().toISOString())
        unreadCount.value = 0
    } catch (err) {
        console.error('Failed to mark all alerts as read', err)
    }
}

const formatTime = (isoString) => {
    if (!isoString) return ''
    const date = new Date(isoString)
    const diff = Math.floor((new Date() - date) / 1000)
    if (diff < 60) return 'Just now'
    if (diff < 3600) return `${Math.floor(diff / 60)}m ago`
    if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`
    return `${Math.floor(diff / 86400)}d ago`
}

onMounted(() => {
    fetchAlerts()
    // Poll every 30s for continuous intelligence alerts
    pollInterval = setInterval(fetchAlerts, 30000)
})

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval)
})
</script>

<template>
    <div class="relative">
        <!-- Bell Trigger Button -->
        <button
            @click="toggleDropdown"
            class="relative p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800"
            title="Continuous Intelligence Alerts"
        >
            <!-- Bell Icon -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>

            <!-- Unread Badge -->
            <span
                v-if="unreadCount > 0"
                class="absolute top-1.5 right-1.5 flex h-4 min-w-[16px] px-1 items-center justify-center text-[10px] font-bold text-white bg-rose-500 rounded-full animate-pulse shadow-xs"
            >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
            </span>
        </button>

        <!-- Dropdown Card -->
        <div
            v-if="isOpen"
            class="absolute right-0 mt-2 w-80 sm:w-96 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 z-50 overflow-hidden"
        >
            <!-- Dropdown Header -->
            <div class="px-4 py-3 bg-slate-50 dark:bg-slate-850 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-900 dark:text-white">Intelligence Alerts</span>
                    <span v-if="unreadCount > 0" class="px-1.5 py-0.5 text-[10px] font-semibold bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 rounded-full">
                        {{ unreadCount }} new
                    </span>
                </div>
                <button
                    v-if="unreadCount > 0"
                    @click="markAllAsRead"
                    class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-medium"
                >
                    Mark all read
                </button>
            </div>

            <!-- Alert List -->
            <div class="max-h-96 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800">
                <div v-if="alerts.length === 0" class="p-8 text-center text-xs text-slate-400">
                    <span>✨</span>
                    <p class="mt-1">All clear. No active drift or intelligence alerts.</p>
                </div>

                <div
                    v-for="alert in alerts"
                    :key="alert.id"
                    @click="markAsRead(alert)"
                    class="p-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors cursor-pointer"
                    :class="!alert.read_at ? 'bg-indigo-50/20 dark:bg-indigo-950/10' : ''"
                >
                    <div class="flex items-start gap-3">
                        <!-- Icon by Severity -->
                        <span class="text-base flex-shrink-0 mt-0.5">
                            {{ alert.severity === 'critical' ? '🚨' : alert.severity === 'warning' ? '⚠️' : alert.severity === 'success' ? '⚡' : 'ℹ️' }}
                        </span>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <h5 class="text-xs font-bold text-slate-900 dark:text-white truncate">
                                    {{ alert.title }}
                                </h5>
                                <span class="text-[10px] text-slate-400 whitespace-nowrap">
                                    {{ formatTime(alert.created_at) }}
                                </span>
                            </div>

                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">
                                {{ alert.message }}
                            </p>

                            <!-- Project Link Tag -->
                            <div v-if="alert.project" class="mt-2 flex items-center justify-between">
                                <Link
                                    :href="`/projects/${alert.project.id}`"
                                    class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1"
                                    @click.stop="isOpen = false"
                                >
                                    <span>📁</span>
                                    <span>{{ alert.project.title }}</span>
                                </Link>
                                <span
                                    v-if="!alert.read_at"
                                    class="w-2 h-2 rounded-full bg-indigo-600 dark:bg-indigo-400"
                                ></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

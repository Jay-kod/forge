<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps<{
    alerts: {
        data: Array<{
            id: number;
            type: string;
            severity: 'info' | 'warning' | 'critical';
            title: string;
            message: string;
            read_at: string | null;
            created_at: string;
            project?: { id: number; title: string };
        }>;
        links: any[];
    };
    unreadCount: number;
}>();

const selectedCategory = ref('all');

const categories = [
    { key: 'all', label: 'All Signals' },
    { key: 'opportunity', label: 'Opportunities' },
    { key: 'research', label: 'Research' },
    { key: 'project', label: 'Projects' },
    { key: 'github', label: 'GitHub' },
    { key: 'billing', label: 'Billing' },
    { key: 'system', label: 'System' },
];

const filteredAlerts = computed(() => {
    if (selectedCategory.value === 'all') return props.alerts.data;
    return props.alerts.data.filter(a => a.type?.toLowerCase().includes(selectedCategory.value));
});

const markAsRead = (alertId: number) => {
    router.post(route('alerts.read', alertId), {}, {
        preserveScroll: true,
    });
};

const markAllAsRead = () => {
    router.post(route('alerts.read_all'), {}, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Notifications & Signals — FORGE" />

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-primary">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-display font-extrabold tracking-tight text-text-primary">
                        Signals & Notifications
                    </h1>
                    <p class="text-xs sm:text-sm text-text-secondary mt-1">
                        Persistent database audit of opportunity updates, drift detection, and platform alerts.
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <span class="text-xs font-mono text-text-tertiary">
                        Unread: <strong class="text-indigo-400 font-bold">{{ unreadCount }}</strong>
                    </span>
                    <button
                        v-if="unreadCount > 0"
                        @click="markAllAsRead"
                        class="px-3.5 py-1.5 rounded-xl border border-primary bg-surface-secondary hover:bg-surface-tertiary text-text-secondary hover:text-text-primary text-xs font-semibold transition-colors"
                    >
                        Mark All Read
                    </button>
                </div>
            </div>

            <!-- Categories Tabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2 custom-scrollbar">
                <button
                    v-for="cat in categories"
                    :key="cat.key"
                    @click="selectedCategory = cat.key"
                    class="px-3 py-1.5 rounded-xl text-xs font-medium whitespace-nowrap transition-colors"
                    :class="selectedCategory === cat.key
                        ? 'bg-indigo-600 text-white shadow-xs font-semibold'
                        : 'bg-surface-secondary text-text-secondary hover:text-text-primary border border-primary'"
                >
                    {{ cat.label }}
                </button>
            </div>

            <!-- Notifications Feed -->
            <div v-if="filteredAlerts.length > 0" class="space-y-3">
                <div
                    v-for="alert in filteredAlerts"
                    :key="alert.id"
                    class="p-4 rounded-2xl bg-surface-secondary border transition-all flex items-start justify-between gap-4"
                    :class="!alert.read_at ? 'border-indigo-500/40 bg-surface-secondary shadow-xs' : 'border-primary opacity-80'"
                >
                    <div class="flex items-start gap-3 min-w-0">
                        <span class="text-base mt-0.5 shrink-0">
                            {{ alert.severity === 'critical' ? '🔴' : (alert.severity === 'warning' ? '🟡' : '🔵') }}
                        </span>

                        <div class="min-w-0 space-y-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-bold text-text-primary leading-snug">
                                    {{ alert.title }}
                                </span>
                                <span v-if="alert.project" class="text-[10px] font-mono px-1.5 py-0.2 rounded bg-surface-tertiary text-text-secondary border border-primary">
                                    {{ alert.project.title }}
                                </span>
                                <span v-if="!alert.read_at" class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            </div>

                            <p class="text-xs text-text-secondary leading-relaxed">
                                {{ alert.message }}
                            </p>

                            <span class="text-[10px] font-mono text-text-tertiary block">
                                {{ new Date(alert.created_at).toLocaleString() }}
                            </span>
                        </div>
                    </div>

                    <div class="shrink-0">
                        <button
                            v-if="!alert.read_at"
                            @click="markAsRead(alert.id)"
                            class="px-2.5 py-1 rounded-lg border border-primary bg-surface-primary hover:bg-surface-tertiary text-[11px] font-mono text-text-secondary hover:text-text-primary transition-colors"
                        >
                            Mark Read
                        </button>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-16 bg-surface-secondary border border-dashed border-primary rounded-2xl">
                <span class="text-3xl block mb-2">🔔</span>
                <h3 class="text-sm font-bold text-text-primary mb-1">No Notifications in this Category</h3>
                <p class="text-xs text-text-secondary">All system signals are up to date.</p>
            </div>
        </div>
    </AppLayout>
</template>

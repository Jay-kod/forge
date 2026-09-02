<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface AuditLog {
    id: number;
    action: string;
    target_type?: string;
    entity_type?: string;
    entity_id?: number;
    details?: Record<string, any>;
    ip_address?: string;
    created_at: string;
    user?: {
        id: number;
        name: string;
        email: string;
    };
}

interface Organization {
    id: number;
    name: string;
    slug: string;
}

const props = defineProps<{
    organization: Organization;
    audit_logs: AuditLog[];
    role: string;
}>();

const selectedFilter = ref('all');
const expandedLogId = ref<number | null>(null);

const filteredLogs = computed(() => {
    if (selectedFilter.value === 'all') {
        return props.audit_logs;
    }
    return props.audit_logs.filter(log => log.action === selectedFilter.value);
});

const toggleDetails = (id: number) => {
    expandedLogId.value = expandedLogId.value === id ? null : id;
};

const getActionColor = (action: string) => {
    if (action.includes('approved')) return 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30';
    if (action.includes('removed') || action.includes('deleted')) return 'bg-red-500/15 text-red-400 border-red-500/30';
    if (action.includes('invited') || action.includes('joined')) return 'bg-indigo-500/15 text-indigo-400 border-indigo-500/30';
    if (action.includes('role')) return 'bg-amber-500/15 text-amber-400 border-amber-500/30';
    return 'bg-surface-tertiary text-text-secondary border-primary';
};

const formatDate = (dateStr: string) => {
    try {
        return new Date(dateStr).toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        });
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <AppLayout>
        <Head :title="`Compliance Audit Logs — ${organization.name} — FORGE`" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
            <!-- Header Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-primary pb-6">
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <Link :href="route('organizations.index')" class="text-xs font-mono text-indigo-400 hover:underline">
                            ← Back to Organizations
                        </Link>
                    </div>
                    <h1 class="text-2xl font-display font-bold text-text-primary flex items-center gap-3">
                        📜 Compliance & Audit Trail
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-mono">
                            {{ organization.name }}
                        </span>
                    </h1>
                    <p class="text-sm text-text-secondary mt-1">
                        Tamper-evident logs of all stage approvals, blueprint exports, role updates, and member activities.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <a
                        :href="route('organizations.audit-logs.export', organization.id)"
                        class="px-4 py-2 rounded-xl text-xs font-mono font-bold border border-primary bg-surface-secondary hover:bg-surface-tertiary text-text-primary transition-colors flex items-center gap-2 shadow-xs"
                    >
                        <span>📥 Export CSV</span>
                    </a>
                </div>
            </div>

            <!-- Filter Pills -->
            <div class="flex items-center gap-2 overflow-x-auto pb-2">
                <button
                    v-for="action in ['all', 'stage.approved', 'member.invited', 'member.joined', 'role.updated', 'member.removed', 'blueprint.exported']"
                    :key="action"
                    @click="selectedFilter = action"
                    class="px-3 py-1.5 rounded-full text-xs font-mono transition-colors shrink-0"
                    :class="selectedFilter === action
                        ? 'bg-indigo-600 text-white font-bold'
                        : 'bg-surface-secondary border border-primary text-text-secondary hover:text-text-primary'"
                >
                    {{ action === 'all' ? 'All Activities' : action }}
                </button>
            </div>

            <!-- Audit Logs Table Card -->
            <div class="p-6 rounded-2xl bg-surface-secondary border border-primary space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-text-primary">Audit Log Events</h3>
                    <span class="text-xs font-mono text-text-tertiary">{{ filteredLogs.length }} recorded entries</span>
                </div>

                <div v-if="filteredLogs.length === 0" class="py-12 text-center text-xs font-mono text-text-tertiary">
                    No audit logs matching selected filter.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-primary text-text-tertiary font-mono uppercase text-[10px]">
                                <th class="pb-3">Timestamp (UTC)</th>
                                <th class="pb-3">Action</th>
                                <th class="pb-3">Actor</th>
                                <th class="pb-3">Entity Target</th>
                                <th class="pb-3">IP Address</th>
                                <th class="pb-3 text-right">Details</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary">
                            <template v-for="log in filteredLogs" :key="log.id">
                                <tr class="hover:bg-surface-tertiary/40 transition-colors">
                                    <td class="py-3 font-mono text-text-secondary whitespace-nowrap">
                                        {{ formatDate(log.created_at) }}
                                    </td>
                                    <td class="py-3">
                                        <span
                                            class="px-2 py-0.5 rounded-md font-mono text-[10px] font-bold uppercase border"
                                            :class="getActionColor(log.action)"
                                        >
                                            {{ log.action }}
                                        </span>
                                    </td>
                                    <td class="py-3">
                                        <div v-if="log.user" class="font-semibold text-text-primary">
                                            {{ log.user.name }}
                                            <div class="text-[10px] font-mono text-text-tertiary">{{ log.user.email }}</div>
                                        </div>
                                        <span v-else class="text-text-tertiary font-mono">System</span>
                                    </td>
                                    <td class="py-3 font-mono text-text-secondary">
                                        {{ log.entity_type ?? log.target_type ?? '—' }}
                                        <span v-if="log.entity_id" class="text-text-tertiary">#{{ log.entity_id }}</span>
                                    </td>
                                    <td class="py-3 font-mono text-text-tertiary">
                                        {{ log.ip_address ?? '127.0.0.1' }}
                                    </td>
                                    <td class="py-3 text-right">
                                        <button
                                            v-if="log.details && Object.keys(log.details).length > 0"
                                            @click="toggleDetails(log.id)"
                                            class="text-[11px] font-mono text-indigo-400 hover:underline"
                                        >
                                            {{ expandedLogId === log.id ? 'Hide' : 'Inspect' }}
                                        </button>
                                        <span v-else class="text-text-tertiary font-mono text-[11px]">—</span>
                                    </td>
                                </tr>

                                <!-- Expandable JSON Details Row -->
                                <tr v-if="expandedLogId === log.id">
                                    <td colspan="6" class="p-4 bg-surface-primary/70 rounded-xl">
                                        <div class="text-[10px] font-mono uppercase text-text-tertiary mb-1">Payload Details:</div>
                                        <pre class="p-3 rounded-lg bg-surface-tertiary border border-primary text-[11px] font-mono text-emerald-400 overflow-x-auto">{{ JSON.stringify(log.details, null, 2) }}</pre>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

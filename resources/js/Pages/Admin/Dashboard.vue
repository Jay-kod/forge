<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface AdminUser {
    id: number;
    name: string;
    email: string;
    role: 'user' | 'admin';
    technical_level: string | null;
    created_at: string;
    credit_account?: {
        balance: number;
        lifetime_granted: number;
        lifetime_consumed: number;
    } | null;
    subscription?: {
        status: string;
        plan?: {
            name: string;
            slug: string;
        };
    } | null;
}

interface Stats {
    total_users: number;
    total_projects: number;
    total_credits_balance: number;
    total_credits_consumed: number;
    plans: Array<{
        id: number;
        name: string;
        slug: string;
        subscriptions_count: number;
    }>;
}

const props = defineProps<{
    users: {
        data: AdminUser[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    stats: Stats;
    filters: {
        search?: string;
    };
}>();

const searchQuery = ref(props.filters.search || '');
const selectedUser = ref<AdminUser | null>(null);
const isGrantModalOpen = ref(false);

const grantForm = useForm({
    amount: 50,
    reason: 'Admin bonus grant',
});

const handleSearch = () => {
    router.get(route('admin.dashboard'), { search: searchQuery.value }, { preserveState: true, replace: true });
};

const openGrantModal = (user: AdminUser) => {
    selectedUser.value = user;
    grantForm.reset();
    isGrantModalOpen.value = true;
};

const closeGrantModal = () => {
    isGrantModalOpen.value = false;
    selectedUser.value = null;
};

const submitGrant = () => {
    if (!selectedUser.value) return;

    grantForm.post(route('admin.users.credits', selectedUser.value.id), {
        onSuccess: () => closeGrantModal(),
    });
};

const toggleRole = (user: AdminUser) => {
    const newRole = user.role === 'admin' ? 'user' : 'admin';
    if (confirm(`Change ${user.name}'s role to ${newRole.toUpperCase()}?`)) {
        router.post(route('admin.users.role', user.id), { role: newRole });
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Admin Operations Dashboard" />

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 pb-6 border-b border-primary">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="px-2.5 py-0.5 rounded text-[10px] font-mono font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30 uppercase tracking-wider">
                        Platform Administration
                    </span>
                    <span class="text-xs text-text-tertiary font-mono">Restricted Access</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-display font-extrabold text-text-primary tracking-tight">
                    Operational Control Center
                </h1>
                <p class="text-xs sm:text-sm text-text-secondary mt-1">
                    Monitor system metrics, manage subscription distribution, and regulate credit circulation.
                </p>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div class="p-5 rounded-2xl bg-surface-secondary border border-primary shadow-xs">
                <span class="text-xs font-mono uppercase tracking-wider text-text-tertiary block mb-1">Total Users</span>
                <div class="text-2xl font-bold font-mono text-text-primary">{{ stats.total_users }}</div>
                <span class="text-[11px] text-text-tertiary">Registered founders & builders</span>
            </div>

            <div class="p-5 rounded-2xl bg-surface-secondary border border-primary shadow-xs">
                <span class="text-xs font-mono uppercase tracking-wider text-text-tertiary block mb-1">Total Workspaces</span>
                <div class="text-2xl font-bold font-mono text-indigo-400">{{ stats.total_projects }}</div>
                <span class="text-[11px] text-text-tertiary">Active project workspaces</span>
            </div>

            <div class="p-5 rounded-2xl bg-surface-secondary border border-primary shadow-xs">
                <span class="text-xs font-mono uppercase tracking-wider text-text-tertiary block mb-1">Credits in Circulation</span>
                <div class="text-2xl font-bold font-mono text-emerald-400">{{ stats.total_credits_balance.toLocaleString() }}</div>
                <span class="text-[11px] text-text-tertiary">Unspent user account balances</span>
            </div>

            <div class="p-5 rounded-2xl bg-surface-secondary border border-primary shadow-xs">
                <span class="text-xs font-mono uppercase tracking-wider text-text-tertiary block mb-1">Credits Consumed</span>
                <div class="text-2xl font-bold font-mono text-text-secondary">{{ stats.total_credits_consumed.toLocaleString() }}</div>
                <span class="text-[11px] text-text-tertiary">Lifetime AI & research executions</span>
            </div>
        </div>

        <!-- Plan Subscription Breakdown -->
        <div class="mb-8 p-5 rounded-2xl bg-surface-secondary border border-primary shadow-xs">
            <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-text-tertiary mb-3">
                Subscription Tier Distribution
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div
                    v-for="plan in stats.plans"
                    :key="plan.id"
                    class="p-4 rounded-xl bg-surface-primary border border-primary flex items-center justify-between"
                >
                    <div>
                        <div class="font-bold text-sm text-text-primary">{{ plan.name }}</div>
                        <span class="text-[11px] font-mono text-text-tertiary capitalize">{{ plan.slug }} tier</span>
                    </div>
                    <span class="text-lg font-mono font-extrabold text-indigo-400">
                        {{ plan.subscriptions_count }} <span class="text-xs font-normal text-text-tertiary">subs</span>
                    </span>
                </div>
            </div>
        </div>

        <!-- User Management Table -->
        <div class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-md">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-lg font-display font-bold text-text-primary">User Accounts & Credits</h2>
                    <p class="text-xs text-text-secondary">Inspect user quotas and grant operational credits.</p>
                </div>

                <!-- Search Input -->
                <div class="flex items-center gap-2">
                    <input
                        v-model="searchQuery"
                        @keyup.enter="handleSearch"
                        type="text"
                        placeholder="Search by name or email..."
                        class="px-3.5 py-2 rounded-xl bg-surface-primary border border-primary text-xs text-text-primary placeholder:text-text-tertiary focus:outline-hidden focus:border-indigo-500 w-64"
                    />
                    <button
                        @click="handleSearch"
                        class="px-3.5 py-2 rounded-xl bg-surface-tertiary hover:bg-surface-elevated border border-primary text-xs font-mono text-text-primary transition-colors"
                    >
                        Search
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-primary text-text-tertiary font-mono uppercase text-[11px]">
                            <th class="pb-3 pl-2">User</th>
                            <th class="pb-3">Role</th>
                            <th class="pb-3">Subscription</th>
                            <th class="pb-3">Credit Balance</th>
                            <th class="pb-3">Registered</th>
                            <th class="pb-3 text-right pr-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-primary">
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="hover:bg-surface-primary/50 transition-colors"
                        >
                            <td class="py-3.5 pl-2">
                                <div class="font-bold text-text-primary">{{ user.name }}</div>
                                <div class="text-[11px] font-mono text-text-tertiary">{{ user.email }}</div>
                            </td>

                            <td class="py-3.5">
                                <span
                                    class="px-2 py-0.5 rounded text-[10px] font-mono font-bold uppercase"
                                    :class="user.role === 'admin' ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'bg-surface-tertiary text-text-secondary border border-primary'"
                                >
                                    {{ user.role }}
                                </span>
                            </td>

                            <td class="py-3.5">
                                <span class="font-mono text-xs text-indigo-400 font-semibold">
                                    {{ user.subscription?.plan?.name || 'Free Explorer' }}
                                </span>
                            </td>

                            <td class="py-3.5">
                                <span class="font-mono font-bold text-emerald-400 text-xs">
                                    ⚡ {{ user.credit_account?.balance ?? 0 }}
                                </span>
                            </td>

                            <td class="py-3.5 text-text-tertiary font-mono text-[11px]">
                                {{ new Date(user.created_at).toLocaleDateString() }}
                            </td>

                            <td class="py-3.5 text-right pr-2">
                                <div class="flex items-center justify-end gap-2">
                                    <button
                                        @click="openGrantModal(user)"
                                        class="px-2.5 py-1 rounded-lg bg-indigo-600/15 hover:bg-indigo-600/25 border border-indigo-500/30 text-indigo-400 text-xs font-mono transition-colors"
                                    >
                                        + Grant Credits
                                    </button>
                                    <button
                                        @click="toggleRole(user)"
                                        class="px-2 py-1 rounded-lg bg-surface-tertiary hover:bg-surface-elevated border border-primary text-text-secondary text-[11px] font-mono transition-colors"
                                        :title="user.role === 'admin' ? 'Demote to User' : 'Promote to Admin'"
                                    >
                                        {{ user.role === 'admin' ? 'Demote' : 'Make Admin' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Manual Credit Grant Modal -->
        <div
            v-if="isGrantModalOpen && selectedUser"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs"
        >
            <div class="w-full max-w-md bg-surface-secondary border border-primary rounded-2xl p-6 shadow-2xl">
                <div class="flex items-center justify-between mb-4 pb-3 border-b border-primary">
                    <h3 class="font-display font-bold text-lg text-text-primary">Grant Credits</h3>
                    <button @click="closeGrantModal" class="text-text-tertiary hover:text-text-primary text-lg">✕</button>
                </div>

                <p class="text-xs text-text-secondary mb-4">
                    Allocate manual credits to <strong class="text-text-primary">{{ selectedUser.name }}</strong> ({{ selectedUser.email }}).
                    Current balance: <strong class="text-emerald-400 font-mono">⚡ {{ selectedUser.credit_account?.balance ?? 0 }}</strong>.
                </p>

                <form @submit.prevent="submitGrant" class="space-y-4">
                    <div>
                        <label class="block text-xs font-mono uppercase text-text-secondary mb-1">Credit Amount</label>
                        <input
                            v-model.number="grantForm.amount"
                            type="number"
                            min="1"
                            max="50000"
                            required
                            class="w-full px-3 py-2 rounded-xl bg-surface-primary border border-primary text-xs font-mono text-text-primary focus:outline-hidden focus:border-indigo-500"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-mono uppercase text-text-secondary mb-1">Reason / Note</label>
                        <input
                            v-model="grantForm.reason"
                            type="text"
                            placeholder="e.g. VIP founder compensation, support issue"
                            class="w-full px-3 py-2 rounded-xl bg-surface-primary border border-primary text-xs text-text-primary focus:outline-hidden focus:border-indigo-500"
                        />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-primary">
                        <button
                            type="button"
                            @click="closeGrantModal"
                            class="px-4 py-2 rounded-xl bg-surface-tertiary text-text-secondary hover:text-text-primary text-xs font-mono"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="grantForm.processing"
                            class="px-5 py-2 rounded-xl brand-button text-xs font-bold shadow-md disabled:opacity-50"
                        >
                            <span v-if="grantForm.processing">Processing...</span>
                            <span v-else>Confirm Grant</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

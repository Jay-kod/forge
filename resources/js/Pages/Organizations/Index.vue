<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface Member {
    id: number;
    name: string;
    email: string;
    pivot: {
        role: string;
        created_at: string;
    };
}

interface ProjectSummary {
    id: number;
    title: string;
    status: string;
    created_at: string;
}

interface CreditAccount {
    id: number;
    balance: number;
    lifetime_granted: number;
}

interface Organization {
    id: number;
    name: string;
    slug: string;
    plan: string;
    owner: {
        id: number;
        name: string;
        email: string;
    };
    members: Member[];
    projects: ProjectSummary[];
    credit_account?: CreditAccount;
    settings?: Record<string, any>;
}

const props = defineProps<{
    organizations: Organization[];
}>();

const selectedOrgIndex = ref(0);
const currentOrg = computed(() => props.organizations[selectedOrgIndex.value] ?? null);

// Modals state
const isCreateOrgOpen = ref(false);
const isInviteOpen = ref(false);
const newOrgName = ref('');
const newOrgPlan = ref('business');
const inviteEmail = ref('');
const inviteRole = ref('member');
const isSubmitting = ref(false);
const notificationMessage = ref<string | null>(null);

const createOrganization = async () => {
    if (!newOrgName.value.trim()) return;
    isSubmitting.value = true;

    try {
        const res = await fetch('/organizations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({
                name: newOrgName.value,
                plan: newOrgPlan.value,
            }),
        });

        const data = await res.json();
        if (data.success) {
            isCreateOrgOpen.value = false;
            newOrgName.value = '';
            router.reload();
        }
    } finally {
        isSubmitting.value = false;
    }
};

const sendInvite = async () => {
    if (!inviteEmail.value.trim() || !currentOrg.value) return;
    isSubmitting.value = true;

    try {
        const res = await fetch(`/organizations/${currentOrg.value.id}/invite`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({
                email: inviteEmail.value,
                role: inviteRole.value,
            }),
        });

        const data = await res.json();
        if (data.success) {
            isInviteOpen.value = false;
            inviteEmail.value = '';
            notificationMessage.value = `Invitation sent to ${data.invitation.email}! Token: ${data.invitation.token}`;
            setTimeout(() => { notificationMessage.value = null; }, 6000);
        } else {
            alert(data.error || 'Failed to send invitation.');
        }
    } finally {
        isSubmitting.value = false;
    }
};

const updateRole = async (memberId: number, newRole: string) => {
    if (!currentOrg.value) return;
    await fetch(`/organizations/${currentOrg.value.id}/members/${memberId}/role`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
        },
        body: JSON.stringify({ role: newRole }),
    });
    router.reload();
};

const removeMember = async (memberId: number) => {
    if (!currentOrg.value) return;
    if (!confirm('Are you sure you want to remove this member from the organization?')) return;

    await fetch(`/organizations/${currentOrg.value.id}/members/${memberId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
        },
    });
    router.reload();
};
</script>

<template>
    <AppLayout>
        <Head title="Team & Organizations — FORGE" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
            <!-- Header Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-primary pb-6">
                <div>
                    <h1 class="text-2xl font-display font-bold text-text-primary flex items-center gap-3">
                        🏢 Team & Organizations
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-mono">
                            Enterprise Multi-Tenancy
                        </span>
                    </h1>
                    <p class="text-sm text-text-secondary mt-1">
                        Manage your collaborative team workspaces, shared credit pooling, member access roles, and compliance.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        @click="isCreateOrgOpen = true"
                        class="px-4 py-2 rounded-xl text-xs font-semibold brand-button shadow-xs flex items-center gap-2"
                    >
                        <span>➕ New Organization</span>
                    </button>
                </div>
            </div>

            <!-- Success Alert Banner -->
            <div v-if="notificationMessage" class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-mono flex items-center justify-between">
                <span>{{ notificationMessage }}</span>
                <button @click="notificationMessage = null" class="text-emerald-400 hover:text-emerald-300 font-bold ml-4">✕</button>
            </div>

            <!-- Main Layout: Organizations Selector & Details -->
            <div v-if="organizations.length === 0" class="p-12 text-center rounded-2xl bg-surface-secondary border border-primary">
                <div class="text-4xl mb-3">🏢</div>
                <h3 class="text-base font-bold text-text-primary">No organizations yet</h3>
                <p class="text-xs text-text-secondary mt-1 max-w-sm mx-auto">
                    Create your first organization to collaborate with colleagues, pool AI credits, and share intelligence blueprints.
                </p>
                <button
                    @click="isCreateOrgOpen = true"
                    class="mt-4 px-4 py-2 rounded-xl text-xs font-semibold brand-button shadow-xs"
                >
                    Create Organization
                </button>
            </div>

            <div v-else class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Left Sidebar: Org Switcher -->
                <div class="space-y-3">
                    <h3 class="text-xs font-mono font-bold text-text-tertiary uppercase tracking-wider px-1">
                        Workspaces ({{ organizations.length }})
                    </h3>
                    <div class="space-y-1.5">
                        <button
                            v-for="(org, idx) in organizations"
                            :key="org.id"
                            @click="selectedOrgIndex = idx"
                            class="w-full text-left p-3.5 rounded-xl border transition-all flex items-center justify-between"
                            :class="selectedOrgIndex === idx
                                ? 'bg-surface-elevated border-indigo-500/50 shadow-xs text-text-primary font-bold'
                                : 'bg-surface-secondary border-primary text-text-secondary hover:bg-surface-tertiary'"
                        >
                            <div class="truncate">
                                <div class="text-xs truncate">{{ org.name }}</div>
                                <div class="text-[10px] font-mono text-text-tertiary capitalize mt-0.5">
                                    {{ org.plan }} Plan • {{ org.members?.length || 1 }} members
                                </div>
                            </div>
                            <span class="text-xs font-mono text-indigo-400 pl-2">⚡{{ org.credit_account?.balance ?? 0 }}</span>
                        </button>
                    </div>
                </div>

                <!-- Right Area: Selected Org Details -->
                <div v-if="currentOrg" class="lg:col-span-3 space-y-6">
                    <!-- Org Summary Card -->
                    <div class="p-6 rounded-2xl bg-surface-secondary border border-primary flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-xl font-bold font-display text-text-primary">{{ currentOrg.name }}</h2>
                                <span class="px-2 py-0.5 rounded-md text-[10px] uppercase font-mono font-bold tracking-wider bg-indigo-500/15 text-indigo-400 border border-indigo-500/30">
                                    {{ currentOrg.plan }}
                                </span>
                            </div>
                            <p class="text-xs text-text-secondary mt-1">
                                Owned by <strong class="text-text-primary">{{ currentOrg.owner.name }}</strong> ({{ currentOrg.owner.email }})
                            </p>
                        </div>

                        <div class="flex items-center gap-4">
                            <!-- Shared Credit Pool Card -->
                            <div class="px-4 py-3 rounded-xl bg-surface-tertiary border border-primary flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-base">
                                    ⚡
                                </div>
                                <div>
                                    <div class="text-[10px] font-mono uppercase text-text-tertiary">Shared Credit Pool</div>
                                    <div class="text-base font-bold font-mono text-text-primary">
                                        {{ currentOrg.credit_account?.balance ?? 0 }} Credits
                                    </div>
                                </div>
                            </div>

                            <button
                                @click="isInviteOpen = true"
                                class="px-3.5 py-2 rounded-xl text-xs font-semibold brand-button shadow-xs"
                            >
                                ✉️ Invite Member
                            </button>

                            <Link
                                :href="route('organizations.audit-logs.index', currentOrg.id)"
                                class="px-3.5 py-2 rounded-xl text-xs font-mono font-medium border border-primary bg-surface-tertiary hover:bg-surface-elevated text-text-secondary hover:text-text-primary transition-colors"
                            >
                                📜 Audit Logs
                            </Link>
                        </div>
                    </div>

                    <!-- Team Members Roster -->
                    <div class="p-6 rounded-2xl bg-surface-secondary border border-primary space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-text-primary">Team Members</h3>
                                <p class="text-xs text-text-secondary">Collaborators with shared workspace and credit access.</p>
                            </div>
                            <span class="text-xs font-mono text-text-tertiary">{{ currentOrg.members?.length || 0 }} Members</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="border-b border-primary text-text-tertiary font-mono uppercase text-[10px]">
                                        <th class="pb-3">Member</th>
                                        <th class="pb-3">Email</th>
                                        <th class="pb-3">Role</th>
                                        <th class="pb-3 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-primary">
                                    <tr v-for="member in currentOrg.members" :key="member.id" class="group">
                                        <td class="py-3 font-semibold text-text-primary flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-indigo-600/30 text-indigo-300 flex items-center justify-center font-bold text-[10px]">
                                                {{ member.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <span>{{ member.name }}</span>
                                            <span v-if="member.id === currentOrg.owner.id" class="text-[9px] px-1.5 py-0.2 rounded bg-amber-500/20 text-amber-400 font-mono">Owner</span>
                                        </td>
                                        <td class="py-3 font-mono text-text-secondary">{{ member.email }}</td>
                                        <td class="py-3">
                                            <select
                                                v-if="member.id !== currentOrg.owner.id"
                                                :value="member.pivot.role"
                                                @change="updateRole(member.id, ($event.target as HTMLSelectElement).value)"
                                                class="px-2 py-1 rounded-lg bg-surface-tertiary border border-primary text-xs font-mono text-text-primary focus:outline-hidden"
                                            >
                                                <option value="admin">Admin</option>
                                                <option value="member">Member</option>
                                                <option value="viewer">Viewer</option>
                                            </select>
                                            <span v-else class="text-xs font-mono text-amber-400 capitalize">Owner</span>
                                        </td>
                                        <td class="py-3 text-right">
                                            <button
                                                v-if="member.id !== currentOrg.owner.id"
                                                @click="removeMember(member.id)"
                                                class="text-[11px] font-mono text-red-400/80 hover:text-red-400 hover:underline"
                                            >
                                                Remove
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Organization Projects -->
                    <div class="p-6 rounded-2xl bg-surface-secondary border border-primary space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-bold text-text-primary">Team Projects</h3>
                                <p class="text-xs text-text-secondary">Projects created within {{ currentOrg.name }}.</p>
                            </div>
                            <Link :href="route('projects.create')" class="text-xs font-mono text-indigo-400 hover:underline">
                                ➕ New Team Project
                            </Link>
                        </div>

                        <div v-if="!currentOrg.projects || currentOrg.projects.length === 0" class="text-xs text-text-tertiary font-mono py-4 text-center">
                            No team projects associated with this organization yet.
                        </div>

                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <Link
                                v-for="proj in currentOrg.projects"
                                :key="proj.id"
                                :href="route('projects.show', proj.id)"
                                class="p-4 rounded-xl bg-surface-tertiary border border-primary hover:border-indigo-500/50 transition-colors flex items-center justify-between"
                            >
                                <div>
                                    <div class="text-xs font-bold text-text-primary">{{ proj.title }}</div>
                                    <div class="text-[10px] font-mono text-text-tertiary mt-0.5">Status: {{ proj.status }}</div>
                                </div>
                                <span class="text-indigo-400 text-xs">→</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Organization Modal -->
        <div v-if="isCreateOrgOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-md p-6 rounded-2xl bg-surface-secondary border border-primary shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-primary pb-3">
                    <h3 class="text-sm font-bold text-text-primary">Create New Organization</h3>
                    <button @click="isCreateOrgOpen = false" class="text-text-tertiary hover:text-text-primary">✕</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-mono text-text-tertiary mb-1">Organization Name</label>
                        <input
                            v-model="newOrgName"
                            type="text"
                            placeholder="e.g. Acme Ventures"
                            class="w-full px-3 py-2 rounded-xl bg-surface-tertiary border border-primary text-text-primary focus:outline-hidden focus:border-indigo-500"
                        />
                    </div>

                    <div>
                        <label class="block font-mono text-text-tertiary mb-1">Subscription Plan</label>
                        <select
                            v-model="newOrgPlan"
                            class="w-full px-3 py-2 rounded-xl bg-surface-tertiary border border-primary text-text-primary focus:outline-hidden"
                        >
                            <option value="starter">Starter Team (5 seats)</option>
                            <option value="business">Business Collaborative (20 seats)</option>
                            <option value="enterprise">Enterprise Custom (Unlimited seats)</option>
                        </select>
                    </div>

                    <div class="p-3 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-300 text-[11px] font-mono">
                        ⚡ Every new organization receives 100 starter team credits pooled for all members.
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-primary">
                    <button
                        @click="isCreateOrgOpen = false"
                        class="px-4 py-2 rounded-xl text-xs font-mono text-text-secondary hover:bg-surface-tertiary"
                    >
                        Cancel
                    </button>
                    <button
                        @click="createOrganization"
                        :disabled="isSubmitting || !newOrgName.trim()"
                        class="px-4 py-2 rounded-xl text-xs font-semibold brand-button shadow-xs disabled:opacity-50"
                    >
                        {{ isSubmitting ? 'Creating...' : 'Create Workspace' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Invite Member Modal -->
        <div v-if="isInviteOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-md p-6 rounded-2xl bg-surface-secondary border border-primary shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-primary pb-3">
                    <h3 class="text-sm font-bold text-text-primary">Invite Teammate to {{ currentOrg?.name }}</h3>
                    <button @click="isInviteOpen = false" class="text-text-tertiary hover:text-text-primary">✕</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-mono text-text-tertiary mb-1">Email Address</label>
                        <input
                            v-model="inviteEmail"
                            type="email"
                            placeholder="colleague@company.com"
                            class="w-full px-3 py-2 rounded-xl bg-surface-tertiary border border-primary text-text-primary focus:outline-hidden focus:border-indigo-500"
                        />
                    </div>

                    <div>
                        <label class="block font-mono text-text-tertiary mb-1">Access Role</label>
                        <select
                            v-model="inviteRole"
                            class="w-full px-3 py-2 rounded-xl bg-surface-tertiary border border-primary text-text-primary focus:outline-hidden"
                        >
                            <option value="admin">Admin (Manage members, credits & projects)</option>
                            <option value="member">Member (Create projects, run intelligence pipelines)</option>
                            <option value="viewer">Viewer (Read-only access to blueprints)</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-primary">
                    <button
                        @click="isInviteOpen = false"
                        class="px-4 py-2 rounded-xl text-xs font-mono text-text-secondary hover:bg-surface-tertiary"
                    >
                        Cancel
                    </button>
                    <button
                        @click="sendInvite"
                        :disabled="isSubmitting || !inviteEmail.trim()"
                        class="px-4 py-2 rounded-xl text-xs font-semibold brand-button shadow-xs disabled:opacity-50"
                    >
                        {{ isSubmitting ? 'Sending...' : 'Send Invitation' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

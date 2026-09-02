<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface ConsentStatus {
    type: string;
    granted: boolean;
    version: string;
    updated_at?: string;
}

interface AuditRecord {
    id: number;
    consent_type: string;
    granted: boolean;
    version: string;
    ip_address?: string;
    granted_at?: string;
    revoked_at?: string;
    created_at: string;
}

const props = defineProps<{
    consents: Record<string, ConsentStatus>;
    audit_history: AuditRecord[];
}>();

const consentsState = ref<Record<string, boolean>>({
    analytics: props.consents.analytics?.granted ?? false,
    product_improvement: props.consents.product_improvement?.granted ?? false,
    ai_improvement: props.consents.ai_improvement?.granted ?? false,
    marketing: props.consents.marketing?.granted ?? false,
});

const isUpdating = ref<Record<string, boolean>>({});
const notificationMessage = ref<string | null>(null);

// Account deletion modal state
const isDeleteModalOpen = ref(false);
const deletePassword = ref('');
const deleteError = ref<string | null>(null);
const isDeleting = ref(false);

const consentDescriptions: Record<string, { title: string; icon: string; description: string }> = {
    analytics: {
        title: 'Platform Analytics',
        icon: '📊',
        description: 'Collect anonymous interaction telemetry and page flows to optimize application performance.',
    },
    product_improvement: {
        title: 'Product Improvement',
        icon: '🛠️',
        description: 'Analyze anonymized workflow abandonment points and feature engagement to build better tools.',
    },
    ai_improvement: {
        title: 'AI Model Self-Learning',
        icon: '🧠',
        description: 'Contribute anonymized recommendation ratings and thumbs up/down signals to help FORGE tune strategic weights. Never stores or shares private project text.',
    },
    marketing: {
        title: 'Founder Communications',
        icon: '📬',
        description: 'Receive monthly intelligence summaries, case studies, and notifications about major platform capabilities.',
    },
};

const toggleConsent = async (type: string) => {
    const nextState = !consentsState.value[type];
    consentsState.value[type] = nextState;
    isUpdating.value[type] = true;

    try {
        const res = await fetch('/settings/privacy/consent', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({
                consent_type: type,
                granted: nextState,
                version: '1.0',
            }),
        });

        const data = await res.json();
        if (data.success) {
            notificationMessage.value = `Privacy preference for ${consentDescriptions[type]?.title || type} updated.`;
            setTimeout(() => { notificationMessage.value = null; }, 4000);
        }
    } catch {
        consentsState.value[type] = !nextState; // Revert on failure
        alert('Failed to update consent preference.');
    } finally {
        isUpdating.value[type] = false;
    }
};

const executeAccountDeletion = async () => {
    if (!deletePassword.value.trim()) {
        deleteError.value = 'Please enter your password to confirm.';
        return;
    }

    isDeleting.value = true;
    deleteError.value = null;

    try {
        const res = await fetch('/settings/privacy/account', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({
                password: deletePassword.value,
            }),
        });

        const data = await res.json();
        if (data.success) {
            window.location.href = '/';
        } else {
            deleteError.value = data.error || 'Failed to delete account.';
        }
    } catch {
        deleteError.value = 'An unexpected error occurred during account deletion.';
    } finally {
        isDeleting.value = false;
    }
};

const formatDate = (dateStr?: string) => {
    if (!dateStr) return '—';
    try {
        return new Date(dateStr).toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Privacy, Data Governance & Portability — FORGE" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
            <!-- Header Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-primary pb-6">
                <div>
                    <h1 class="text-2xl font-display font-bold text-text-primary flex items-center gap-3">
                        🔒 Privacy, Data Governance & Portability
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono">
                            GDPR & CCPA Compliant
                        </span>
                    </h1>
                    <p class="text-sm text-text-secondary mt-1">
                        Control your data collection consents, download machine-readable archives, and manage account governance.
                    </p>
                </div>
            </div>

            <!-- Toast Alert -->
            <div v-if="notificationMessage" class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-mono flex items-center justify-between">
                <span>{{ notificationMessage }}</span>
                <button @click="notificationMessage = null" class="text-emerald-400 hover:text-emerald-300 font-bold ml-4">✕</button>
            </div>

            <!-- Section 1: Granular Consent Cards -->
            <div class="space-y-4">
                <div>
                    <h2 class="text-base font-bold text-text-primary font-display">1. Privacy & Telemetry Consents</h2>
                    <p class="text-xs text-text-secondary mt-0.5">
                        FORGE strictly obeys explicit opt-in preferences. Data is never collected for categories you leave disabled.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="(info, key) in consentDescriptions"
                        :key="key"
                        class="p-5 rounded-2xl bg-surface-secondary border border-primary flex items-start justify-between gap-4"
                    >
                        <div class="flex items-start gap-3">
                            <span class="text-2xl">{{ info.icon }}</span>
                            <div>
                                <h3 class="text-xs font-bold text-text-primary">{{ info.title }}</h3>
                                <p class="text-[11px] text-text-secondary mt-1 leading-relaxed">{{ info.description }}</p>
                            </div>
                        </div>

                        <!-- Toggle Switch -->
                        <button
                            type="button"
                            @click="toggleConsent(key as string)"
                            :disabled="isUpdating[key]"
                            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-hidden"
                            :class="consentsState[key] ? 'bg-indigo-600' : 'bg-surface-tertiary border-primary'"
                        >
                            <span
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-xs ring-0 transition duration-200 ease-in-out"
                                :class="consentsState[key] ? 'translate-x-5' : 'translate-x-0'"
                            />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Section 2: Data Portability (GDPR Article 20) -->
            <div class="p-6 rounded-2xl bg-surface-secondary border border-primary space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-bold text-text-primary font-display flex items-center gap-2">
                            <span>📦</span> 2. Data Portability & Complete Export (GDPR Article 20)
                        </h2>
                        <p class="text-xs text-text-secondary mt-1 max-w-2xl leading-relaxed">
                            Download a full, machine-readable JSON archive of all your personal projects, stage specifications, PRDs, architecture blueprints, research findings, decision timelines, and credit accounting ledger.
                        </p>
                    </div>

                    <a
                        href="/settings/privacy/export-data"
                        class="px-4 py-2.5 rounded-xl text-xs font-semibold brand-button shadow-xs flex items-center gap-2 shrink-0 self-start sm:self-auto"
                    >
                        <span>📥 Download My Data Archive</span>
                    </a>
                </div>
            </div>

            <!-- Section 3: Consent Audit Trail -->
            <div class="p-6 rounded-2xl bg-surface-secondary border border-primary space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-text-primary font-display">3. Consent Audit History</h2>
                        <p class="text-xs text-text-secondary">Immutable legal record of privacy preferences granted or revoked.</p>
                    </div>
                    <span class="text-xs font-mono text-text-tertiary">{{ audit_history.length }} Events</span>
                </div>

                <div v-if="audit_history.length === 0" class="py-8 text-center text-xs font-mono text-text-tertiary">
                    No consent changes recorded yet.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-primary text-text-tertiary font-mono uppercase text-[10px]">
                                <th class="pb-3">Timestamp</th>
                                <th class="pb-3">Category</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3">Policy Version</th>
                                <th class="pb-3">IP Address</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary">
                            <tr v-for="record in audit_history" :key="record.id" class="hover:bg-surface-tertiary/40 transition-colors">
                                <td class="py-3 font-mono text-text-secondary whitespace-nowrap">{{ formatDate(record.created_at) }}</td>
                                <td class="py-3 font-semibold text-text-primary capitalize">{{ record.consent_type.replace('_', ' ') }}</td>
                                <td class="py-3">
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase"
                                        :class="record.granted ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-red-500/15 text-red-400 border border-red-500/30'"
                                    >
                                        {{ record.granted ? 'Granted ✓' : 'Revoked ✕' }}
                                    </span>
                                </td>
                                <td class="py-3 font-mono text-text-tertiary">v{{ record.version }}</td>
                                <td class="py-3 font-mono text-text-tertiary">{{ record.ip_address || '127.0.0.1' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Section 4: Danger Zone - Right to Be Forgotten -->
            <div class="p-6 rounded-2xl bg-surface-secondary border border-red-500/30 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-bold text-red-400 font-display flex items-center gap-2">
                            <span>⚠️</span> 4. Danger Zone: Right to Be Forgotten (GDPR Article 17)
                        </h2>
                        <p class="text-xs text-text-secondary mt-1 max-w-2xl leading-relaxed">
                            Permanently delete your account and all associated assets. All projects, contexts, generated blueprints, research results, OAuth credentials, and BYOK keys will be permanently purged.
                        </p>
                    </div>

                    <button
                        @click="isDeleteModalOpen = true"
                        class="px-4 py-2.5 rounded-xl text-xs font-semibold bg-red-600 hover:bg-red-500 text-white shadow-xs shrink-0 self-start sm:self-auto transition-colors"
                    >
                        Delete My Account
                    </button>
                </div>
            </div>
        </div>

        <!-- Account Deletion Confirmation Modal -->
        <div v-if="isDeleteModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-md p-6 rounded-2xl bg-surface-secondary border border-red-500/40 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-primary pb-3">
                    <h3 class="text-sm font-bold text-red-400 flex items-center gap-2">
                        <span>⚠️</span> Permanent Account Purge
                    </h3>
                    <button @click="isDeleteModalOpen = false" class="text-text-tertiary hover:text-text-primary">✕</button>
                </div>

                <div class="p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-300 text-xs font-mono">
                    This action cannot be undone. All your workspaces, projects, research, and keys will be permanently destroyed.
                </div>

                <div v-if="deleteError" class="p-3 rounded-xl bg-red-500/20 border border-red-500/40 text-red-300 text-xs font-mono">
                    {{ deleteError }}
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-mono text-text-tertiary mb-1">Confirm with your password</label>
                        <input
                            v-model="deletePassword"
                            type="password"
                            placeholder="Enter current account password..."
                            class="w-full px-3 py-2 rounded-xl bg-surface-tertiary border border-primary text-text-primary font-mono focus:outline-hidden focus:border-red-500"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-primary">
                    <button
                        @click="isDeleteModalOpen = false"
                        class="px-4 py-2 rounded-xl text-xs font-mono text-text-secondary hover:bg-surface-tertiary"
                    >
                        Cancel
                    </button>
                    <button
                        @click="executeAccountDeletion"
                        :disabled="isDeleting || !deletePassword.trim()"
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-red-600 hover:bg-red-500 text-white shadow-xs disabled:opacity-50 transition-colors"
                    >
                        {{ isDeleting ? 'Purging Account...' : 'Permanently Delete' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

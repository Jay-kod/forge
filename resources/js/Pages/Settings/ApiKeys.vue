<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface ApiKey {
    id: number;
    organization_id?: number;
    name: string;
    prefix: string;
    abilities?: string[];
    last_used_at?: string;
    expires_at?: string;
    created_at: string;
    organization?: {
        id: number;
        name: string;
    };
}

interface OrganizationOption {
    id: number;
    name: string;
}

const props = defineProps<{
    api_keys: ApiKey[];
    organizations: OrganizationOption[];
}>();

// Modals
const isCreateOpen = ref(false);
const newKeyName = ref('');
const selectedOrgId = ref<number | null>(null);
const expiresInDays = ref<number | null>(90);
const isSubmitting = ref(false);

// Generated token display
const generatedSecret = ref<string | null>(null);
const copySuccess = ref(false);

const createKey = async () => {
    if (!newKeyName.value.trim()) return;
    isSubmitting.value = true;

    try {
        const res = await fetch('/settings/api-keys', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({
                name: newKeyName.value,
                organization_id: selectedOrgId.value,
                expires_in_days: expiresInDays.value,
            }),
        });

        const data = await res.json();
        if (data.success) {
            generatedSecret.value = data.api_key.plain_token;
            newKeyName.value = '';
        }
    } finally {
        isSubmitting.value = false;
    }
};

const copyToken = async () => {
    if (!generatedSecret.value) return;
    await navigator.clipboard.writeText(generatedSecret.value);
    copySuccess.value = true;
    setTimeout(() => { copySuccess.value = false; }, 3000);
};

const closeTokenModal = () => {
    generatedSecret.value = null;
    isCreateOpen.value = false;
    router.reload();
};

const revokeKey = async (keyId: number) => {
    if (!confirm('Are you sure you want to revoke this API key? Any automated scripts using it will stop working immediately.')) {
        return;
    }

    await fetch(`/settings/api-keys/${keyId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
        },
    });
    router.reload();
};

const formatDate = (dateStr?: string) => {
    if (!dateStr) return 'Never';
    try {
        return new Date(dateStr).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
    } catch {
        return dateStr;
    }
};
</script>

<template>
    <AppLayout>
        <Head title="API Keys & Access Tokens — FORGE" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
            <!-- Header Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-primary pb-6">
                <div>
                    <h1 class="text-2xl font-display font-bold text-text-primary flex items-center gap-3">
                        🔑 API Keys & Developer Tokens
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-mono">
                            REST API v1
                        </span>
                    </h1>
                    <p class="text-sm text-text-secondary mt-1">
                        Generate and manage hashed API keys for programmatic access, CI/CD automated blueprints, and API integrations.
                    </p>
                </div>

                <div class="flex items-center gap-3">
                    <button
                        @click="isCreateOpen = true"
                        class="px-4 py-2 rounded-xl text-xs font-semibold brand-button shadow-xs flex items-center gap-2"
                    >
                        <span>➕ Create New Key</span>
                    </button>
                </div>
            </div>

            <!-- API Keys Table Card -->
            <div class="p-6 rounded-2xl bg-surface-secondary border border-primary space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-text-primary">Active API Keys</h3>
                        <p class="text-xs text-text-secondary">Keys are cryptographically hashed using SHA-256 for optimal security.</p>
                    </div>
                    <span class="text-xs font-mono text-text-tertiary">{{ api_keys.length }} Keys</span>
                </div>

                <div v-if="api_keys.length === 0" class="py-12 text-center text-xs font-mono text-text-tertiary">
                    No active API keys found. Click "Create New Key" above to generate a programmatic access token.
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-primary text-text-tertiary font-mono uppercase text-[10px]">
                                <th class="pb-3">Key Name</th>
                                <th class="pb-3">Token Prefix</th>
                                <th class="pb-3">Workspace</th>
                                <th class="pb-3">Last Used</th>
                                <th class="pb-3">Expires</th>
                                <th class="pb-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary">
                            <tr v-for="key in api_keys" :key="key.id" class="hover:bg-surface-tertiary/40 transition-colors">
                                <td class="py-3 font-semibold text-text-primary">
                                    {{ key.name }}
                                </td>
                                <td class="py-3 font-mono text-indigo-400">
                                    {{ key.prefix }}••••••••
                                </td>
                                <td class="py-3 font-mono text-text-secondary">
                                    <span v-if="key.organization" class="px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-300 text-[10px]">
                                        🏢 {{ key.organization.name }}
                                    </span>
                                    <span v-else class="text-text-tertiary text-[10px]">
                                        👤 Personal
                                    </span>
                                </td>
                                <td class="py-3 font-mono text-text-tertiary">
                                    {{ formatDate(key.last_used_at) }}
                                </td>
                                <td class="py-3 font-mono text-text-secondary">
                                    {{ formatDate(key.expires_at) }}
                                </td>
                                <td class="py-3 text-right">
                                    <button
                                        @click="revokeKey(key.id)"
                                        class="text-[11px] font-mono text-red-400/80 hover:text-red-400 hover:underline"
                                    >
                                        Revoke
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Developer Integration Example Card -->
            <div class="p-6 rounded-2xl bg-surface-secondary border border-primary space-y-4">
                <h3 class="text-sm font-bold text-text-primary flex items-center gap-2">
                    <span>💻</span> Quickstart Integration Example
                </h3>
                <p class="text-xs text-text-secondary">
                    Pass your API token as an HTTP Bearer token in the <code class="text-indigo-400 font-mono">Authorization</code> header:
                </p>

                <pre class="p-4 rounded-xl bg-surface-primary border border-primary font-mono text-xs text-emerald-400 overflow-x-auto">curl -X GET "https://forge.app/api/v1/projects" \
  -H "Authorization: Bearer forge_live_YOUR_SECRET_TOKEN" \
  -H "Accept: application/json"</pre>
            </div>
        </div>

        <!-- Create Key Modal -->
        <div v-if="isCreateOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-md p-6 rounded-2xl bg-surface-secondary border border-primary shadow-2xl space-y-4">
                <!-- Secret Display Step -->
                <div v-if="generatedSecret" class="space-y-4">
                    <div class="flex items-center gap-2 text-emerald-400">
                        <span class="text-xl">✅</span>
                        <h3 class="text-sm font-bold text-text-primary">API Key Generated!</h3>
                    </div>

                    <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-mono">
                        ⚠️ <strong>Copy this secret now!</strong> For security reasons, it is stored as a SHA-256 hash and will never be displayed again.
                    </div>

                    <div class="p-3 rounded-xl bg-surface-primary border border-primary font-mono text-xs text-emerald-400 break-all select-all flex items-center justify-between gap-2">
                        <span>{{ generatedSecret }}</span>
                        <button
                            @click="copyToken"
                            class="px-2.5 py-1 rounded-lg bg-surface-tertiary hover:bg-surface-secondary border border-primary text-text-primary text-[10px] font-mono shrink-0"
                        >
                            {{ copySuccess ? 'Copied! ✓' : 'Copy' }}
                        </button>
                    </div>

                    <div class="flex justify-end pt-3 border-t border-primary">
                        <button
                            @click="closeTokenModal"
                            class="px-4 py-2 rounded-xl text-xs font-semibold brand-button shadow-xs"
                        >
                            Done & Close
                        </button>
                    </div>
                </div>

                <!-- Input Step -->
                <div v-else class="space-y-4">
                    <div class="flex items-center justify-between border-b border-primary pb-3">
                        <h3 class="text-sm font-bold text-text-primary">Create API Access Token</h3>
                        <button @click="isCreateOpen = false" class="text-text-tertiary hover:text-text-primary">✕</button>
                    </div>

                    <div class="space-y-3 text-xs">
                        <div>
                            <label class="block font-mono text-text-tertiary mb-1">Key Name / Description</label>
                            <input
                                v-model="newKeyName"
                                type="text"
                                placeholder="e.g. GitHub Action Bot"
                                class="w-full px-3 py-2 rounded-xl bg-surface-tertiary border border-primary text-text-primary focus:outline-hidden focus:border-indigo-500"
                            />
                        </div>

                        <div>
                            <label class="block font-mono text-text-tertiary mb-1">Workspace Scope</label>
                            <select
                                v-model="selectedOrgId"
                                class="w-full px-3 py-2 rounded-xl bg-surface-tertiary border border-primary text-text-primary focus:outline-hidden"
                            >
                                <option :value="null">👤 Personal Workspace</option>
                                <option v-for="org in organizations" :key="org.id" :value="org.id">
                                    🏢 {{ org.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-mono text-text-tertiary mb-1">Expiration Period</label>
                            <select
                                v-model="expiresInDays"
                                class="w-full px-3 py-2 rounded-xl bg-surface-tertiary border border-primary text-text-primary focus:outline-hidden"
                            >
                                <option :value="30">30 Days</option>
                                <option :value="90">90 Days (Recommended)</option>
                                <option :value="365">1 Year</option>
                                <option :value="null">No Expiry (Permanent)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-primary">
                        <button
                            @click="isCreateOpen = false"
                            class="px-4 py-2 rounded-xl text-xs font-mono text-text-secondary hover:bg-surface-tertiary"
                        >
                            Cancel
                        </button>
                        <button
                            @click="createKey"
                            :disabled="isSubmitting || !newKeyName.trim()"
                            class="px-4 py-2 rounded-xl text-xs font-semibold brand-button shadow-xs disabled:opacity-50"
                        >
                            {{ isSubmitting ? 'Generating...' : 'Generate Secret Token' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

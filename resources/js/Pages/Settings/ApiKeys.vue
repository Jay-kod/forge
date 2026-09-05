<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
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

interface ByokCredential {
    id: number;
    organization_id?: number;
    provider: string;
    label?: string;
    masked_key: string;
    is_active: boolean;
    last_validated_at?: string;
    created_at: string;
}

interface OrganizationOption {
    id: number;
    name: string;
}

const props = withDefaults(defineProps<{
    api_keys: ApiKey[];
    byok_credentials?: ByokCredential[];
    supported_providers?: string[];
    organizations: OrganizationOption[];
    initial_tab?: string;
}>(), {
    byok_credentials: () => [],
    supported_providers: () => ['openai', 'anthropic', 'gemini'],
    initial_tab: 'platform',
});

// Active Tab State ('platform' | 'byok')
const activeTab = ref(props.initial_tab === 'byok' ? 'byok' : 'platform');

// Check URL query on mount for tab param
onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const tabParam = params.get('tab');
    if (tabParam === 'byok' || tabParam === 'platform') {
        activeTab.value = tabParam;
    }
});

// =========================================================================
// TAB 1: Platform API Keys State & Actions
// =========================================================================
const isCreateKeyOpen = ref(false);
const newKeyName = ref('');
const selectedOrgId = ref<number | null>(null);
const expiresInDays = ref<number | null>(90);
const isSubmittingKey = ref(false);
const generatedSecret = ref<string | null>(null);
const copyKeySuccess = ref(false);

const createKey = async () => {
    if (!newKeyName.value.trim()) return;
    isSubmittingKey.value = true;

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
            router.reload({ only: ['api_keys'] });
        }
    } finally {
        isSubmittingKey.value = false;
    }
};

const copyToken = async () => {
    if (!generatedSecret.value) return;
    await navigator.clipboard.writeText(generatedSecret.value);
    copyKeySuccess.value = true;
    setTimeout(() => {
        copyKeySuccess.value = false;
    }, 2500);
};

const revokeKey = async (id: number) => {
    if (!confirm('Are you sure you want to revoke this API key? Applications using it will immediately be rejected.')) return;

    await fetch(`/settings/api-keys/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
        },
    });

    router.reload({ only: ['api_keys'] });
};

// =========================================================================
// TAB 2: AI Provider BYOK State & Actions
// =========================================================================
const isByokModalOpen = ref(false);
const activeProvider = ref('openai');
const apiKeyInput = ref('');
const byokLabel = ref('');
const selectedByokOrgId = ref<number | null>(null);
const isSubmittingByok = ref(false);
const byokSuccessMessage = ref<string | null>(null);

const providerConfig: Record<string, { name: string; icon: string; docsUrl: string; placeholder: string; modelBadge: string }> = {
    anthropic: {
        name: 'Anthropic Claude',
        icon: '⚡',
        docsUrl: 'https://console.anthropic.com/settings/keys',
        placeholder: 'sk-ant-api03-...',
        modelBadge: 'Claude 3.7 / 3.5 Sonnet',
    },
    openai: {
        name: 'OpenAI',
        icon: '🧠',
        docsUrl: 'https://platform.openai.com/api-keys',
        placeholder: 'sk-proj-...',
        modelBadge: 'GPT-4o / o3-mini',
    },
    gemini: {
        name: 'Google Gemini',
        icon: '✨',
        docsUrl: 'https://aistudio.google.com/app/apikey',
        placeholder: 'AIzaSy...',
        modelBadge: 'Gemini 2.5 Flash / Pro',
    },
};

const credentialMap = computed(() => {
    const map = new Map<string, ByokCredential>();
    for (const cred of props.byok_credentials) {
        map.set(cred.provider, cred);
    }
    return map;
});

const openConfigureByok = (provider: string) => {
    activeProvider.value = provider;
    apiKeyInput.value = '';
    byokLabel.value = '';
    isByokModalOpen.value = true;
};

const saveByokCredential = async () => {
    if (!apiKeyInput.value.trim()) return;
    isSubmittingByok.value = true;

    try {
        const res = await fetch('/settings/byok', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({
                provider: activeProvider.value,
                api_key: apiKeyInput.value,
                label: byokLabel.value || null,
                organization_id: selectedByokOrgId.value,
            }),
        });

        const data = await res.json();
        if (data.success) {
            isByokModalOpen.value = false;
            byokSuccessMessage.value = `Successfully configured ${providerConfig[activeProvider.value]?.name || activeProvider.value} API key.`;
            setTimeout(() => {
                byokSuccessMessage.value = null;
            }, 4000);
            router.reload({ only: ['byok_credentials'] });
        }
    } finally {
        isSubmittingByok.value = false;
    }
};

const revokeByokCredential = async (provider: string) => {
    if (!confirm(`Are you sure you want to remove your ${providerConfig[provider]?.name || provider} API key? Operations will revert to standard credit balance.`)) return;

    await fetch(`/settings/byok/${provider}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
        },
    });

    router.reload({ only: ['byok_credentials'] });
};
</script>

<template>
    <AppLayout>
        <Head title="API Keys & Integrations" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-primary">
                <div>
                    <h1 class="text-2xl font-bold font-display tracking-tight text-text-primary">
                        API Keys & Integrations
                    </h1>
                    <p class="text-xs text-text-tertiary mt-1">
                        Manage programmatic REST API credentials and connect your own AI provider keys with zero platform markup.
                    </p>
                </div>

                <!-- Tab Navigation Switcher -->
                <div class="flex items-center gap-1 p-1 rounded-xl bg-surface-secondary border border-primary">
                    <button
                        type="button"
                        @click="activeTab = 'platform'"
                        class="px-4 py-2 rounded-lg text-xs font-medium transition-all flex items-center gap-2"
                        :class="activeTab === 'platform' ? 'bg-surface-elevated text-indigo-400 font-semibold shadow-xs border border-primary' : 'text-text-secondary hover:text-text-primary'"
                    >
                        <span>⚡ Platform API Keys</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono bg-indigo-500/20 text-indigo-300">
                            {{ api_keys.length }}
                        </span>
                    </button>
                    <button
                        type="button"
                        @click="activeTab = 'byok'"
                        class="px-4 py-2 rounded-lg text-xs font-medium transition-all flex items-center gap-2"
                        :class="activeTab === 'byok' ? 'bg-surface-elevated text-indigo-400 font-semibold shadow-xs border border-primary' : 'text-text-secondary hover:text-text-primary'"
                    >
                        <span>🤖 AI Provider Keys (BYOK)</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] font-mono bg-emerald-500/20 text-emerald-300">
                            {{ byok_credentials.length }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Success Alert Banner -->
            <div v-if="byokSuccessMessage" class="mt-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-xs font-mono flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span>✓</span>
                    <span>{{ byokSuccessMessage }}</span>
                </div>
                <button type="button" @click="byokSuccessMessage = null" class="text-emerald-400 hover:text-emerald-200">✕</button>
            </div>

            <!-- ============================================================= -->
            <!-- TAB 1: Platform API Keys Content                              -->
            <!-- ============================================================= -->
            <div v-if="activeTab === 'platform'" class="mt-8 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-text-primary">Personal & Organization Access Tokens</h2>
                        <p class="text-xs text-text-tertiary mt-0.5">
                            Authenticate automated CI/CD pipelines, terminal CLI scripts, or custom microservices with the FORGE API.
                        </p>
                    </div>
                    <button
                        type="button"
                        @click="isCreateKeyOpen = true; generatedSecret = null;"
                        class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-xs shadow-xs transition-colors flex items-center gap-2"
                    >
                        <span>+ Generate New API Key</span>
                    </button>
                </div>

                <!-- API Keys List -->
                <div v-if="api_keys.length > 0" class="rounded-2xl border border-primary bg-surface-secondary overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-surface-tertiary/50 border-b border-primary text-text-tertiary uppercase font-mono tracking-wider">
                            <tr>
                                <th class="px-6 py-3.5">Name & Scope</th>
                                <th class="px-6 py-3.5">Key Prefix</th>
                                <th class="px-6 py-3.5">Created</th>
                                <th class="px-6 py-3.5">Expires</th>
                                <th class="px-6 py-3.5">Last Used</th>
                                <th class="px-6 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary">
                            <tr v-for="key in api_keys" :key="key.id" class="hover:bg-surface-tertiary/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-text-primary">{{ key.name }}</div>
                                    <div v-if="key.organization" class="text-[10px] font-mono text-indigo-400 mt-0.5">
                                        🏢 {{ key.organization.name }}
                                    </div>
                                    <div v-else class="text-[10px] font-mono text-text-tertiary mt-0.5">
                                        Personal User Key
                                    </div>
                                </td>
                                <td class="px-6 py-4 font-mono text-text-secondary">
                                    <span class="px-2 py-1 rounded-md bg-surface-tertiary border border-primary">
                                        {{ key.prefix }}••••••••
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-text-tertiary font-mono">
                                    {{ new Date(key.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-6 py-4 font-mono">
                                    <span v-if="key.expires_at" class="text-text-tertiary">
                                        {{ new Date(key.expires_at).toLocaleDateString() }}
                                    </span>
                                    <span v-else class="text-emerald-400">Never</span>
                                </td>
                                <td class="px-6 py-4 text-text-tertiary font-mono">
                                    {{ key.last_used_at ? new Date(key.last_used_at).toLocaleDateString() : 'Never' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button
                                        type="button"
                                        @click="revokeKey(key.id)"
                                        class="px-2.5 py-1 rounded-lg border border-red-500/30 text-red-400 hover:bg-red-500/10 font-mono transition-colors"
                                    >
                                        Revoke
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12 px-4 rounded-2xl border border-dashed border-primary bg-surface-secondary/50">
                    <div class="text-3xl mb-2">🔑</div>
                    <h3 class="text-sm font-semibold text-text-primary">No API Keys Generated</h3>
                    <p class="text-xs text-text-tertiary max-w-sm mx-auto mt-1 mb-4">
                        Generate a secure token to interact with the FORGE intelligence API from your CLI, CI/CD pipelines, or microservices.
                    </p>
                    <button
                        type="button"
                        @click="isCreateKeyOpen = true; generatedSecret = null;"
                        class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-xs transition-colors"
                    >
                        Generate API Key
                    </button>
                </div>

                <!-- API Quick Start Documentation Card -->
                <div class="p-6 rounded-2xl border border-primary bg-surface-secondary">
                    <div class="flex items-center gap-2 text-xs font-semibold text-text-primary mb-2">
                        <span>📖</span>
                        <span>Authentication Quick Start</span>
                    </div>
                    <p class="text-xs text-text-tertiary mb-3">
                        Include your generated secret token in the <code class="font-mono text-indigo-400">Authorization: Bearer &lt;TOKEN&gt;</code> header on all REST API requests.
                    </p>
                    <pre class="p-4 rounded-xl bg-surface-tertiary border border-primary font-mono text-[11px] text-text-secondary overflow-x-auto leading-relaxed">
curl -X POST https://forge.ai/api/v1/projects \
  -H "Authorization: Bearer frg_live_948f29..." \
  -H "Content-Type: application/json" \
  -d '{"title": "Automated Logistics", "goal": "Cost optimization for freight logistics"}'</pre>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- TAB 2: AI Provider Keys (BYOK) Content                        -->
            <!-- ============================================================= -->
            <div v-if="activeTab === 'byok'" class="mt-8 space-y-6">
                <!-- Info Banner -->
                <div class="p-5 rounded-2xl border border-indigo-500/30 bg-indigo-500/10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2 text-xs font-bold text-indigo-300">
                            <span>🛡️</span>
                            <span>Direct Zero-Markup Inference (BYOK)</span>
                        </div>
                        <p class="text-xs text-indigo-200/80 mt-1 max-w-2xl">
                            When you register your own provider credentials, all AI reasoning operations bypass platform token deductions. Keys are encrypted at rest with hardware-backed AES-256-GCM.
                        </p>
                    </div>
                    <div class="px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-[11px] font-mono whitespace-nowrap border border-indigo-500/30">
                        ⚡ 0% Credit Markup
                    </div>
                </div>

                <!-- Provider Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="provider in supported_providers"
                        :key="provider"
                        class="p-6 rounded-2xl border bg-surface-secondary flex flex-col justify-between transition-all"
                        :class="credentialMap.has(provider) ? 'border-emerald-500/40 shadow-xs' : 'border-primary'"
                    >
                        <div>
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-2.5">
                                    <span class="text-2xl">{{ providerConfig[provider]?.icon || '🤖' }}</span>
                                    <div>
                                        <h3 class="text-sm font-bold text-text-primary">
                                            {{ providerConfig[provider]?.name || provider }}
                                        </h3>
                                        <span class="text-[10px] font-mono text-text-tertiary">
                                            {{ providerConfig[provider]?.modelBadge }}
                                        </span>
                                    </div>
                                </div>
                                <span
                                    v-if="credentialMap.has(provider)"
                                    class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-emerald-500/20 text-emerald-300 border border-emerald-500/30"
                                >
                                    ✓ Active
                                </span>
                                <span
                                    v-else
                                    class="px-2 py-0.5 rounded-full text-[10px] font-mono bg-surface-tertiary text-text-tertiary border border-primary"
                                >
                                    Not Configured
                                </span>
                            </div>

                            <!-- Masked key or documentation link -->
                            <div class="mt-4 pt-4 border-t border-primary">
                                <div v-if="credentialMap.has(provider)">
                                    <div class="text-[11px] text-text-tertiary mb-1">Encrypted Key:</div>
                                    <div class="px-3 py-1.5 rounded-lg bg-surface-tertiary font-mono text-xs text-text-primary border border-primary">
                                        {{ credentialMap.get(provider)?.masked_key }}
                                    </div>
                                </div>
                                <div v-else>
                                    <p class="text-xs text-text-tertiary">
                                        Get your API key directly from the official provider dashboard:
                                    </p>
                                    <a
                                        :href="providerConfig[provider]?.docsUrl"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 text-xs text-indigo-400 hover:text-indigo-300 font-mono mt-2"
                                    >
                                        <span>Open {{ providerConfig[provider]?.name }} Console</span>
                                        <span>↗</span>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Card Action Buttons -->
                        <div class="mt-6 pt-4 border-t border-primary flex items-center justify-between gap-2">
                            <button
                                v-if="credentialMap.has(provider)"
                                type="button"
                                @click="revokeByokCredential(provider)"
                                class="text-xs font-mono text-red-400 hover:text-red-300 transition-colors"
                            >
                                Disconnect
                            </button>
                            <span v-else></span>

                            <button
                                type="button"
                                @click="openConfigureByok(provider)"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-medium transition-colors"
                                :class="credentialMap.has(provider) ? 'border border-primary bg-surface-tertiary text-text-secondary hover:text-text-primary' : 'bg-indigo-600 hover:bg-indigo-500 text-white'"
                            >
                                {{ credentialMap.has(provider) ? 'Update Key' : 'Configure Key' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- MODAL: Generate Platform API Key                                  -->
        <!-- ================================================================= -->
        <div v-if="isCreateKeyOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs">
            <div class="w-full max-w-md p-6 rounded-2xl bg-surface-elevated border border-primary shadow-xl">
                <!-- If token was generated, show secret view -->
                <div v-if="generatedSecret" class="space-y-4">
                    <div class="w-10 h-10 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-lg mx-auto">
                        ✓
                    </div>
                    <h3 class="text-base font-bold text-center text-text-primary">Key Generated Successfully!</h3>
                    <p class="text-xs text-amber-400/90 text-center">
                        ⚠️ Please copy this key now. For security purposes, you will not be able to view it again.
                    </p>

                    <div class="p-3 rounded-xl bg-surface-secondary border border-primary font-mono text-xs text-text-primary break-all">
                        {{ generatedSecret }}
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button
                            type="button"
                            @click="copyToken"
                            class="flex-1 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-xs transition-colors flex items-center justify-center gap-1.5"
                        >
                            <span>{{ copyKeySuccess ? 'Copied!' : 'Copy to Clipboard' }}</span>
                        </button>
                        <button
                            type="button"
                            @click="isCreateKeyOpen = false; generatedSecret = null;"
                            class="px-4 py-2.5 rounded-xl border border-primary text-text-secondary hover:text-text-primary text-xs font-mono"
                        >
                            Done
                        </button>
                    </div>
                </div>

                <!-- Input Form -->
                <form v-else @submit.prevent="createKey" class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-primary">
                        <h3 class="text-sm font-bold text-text-primary">Generate API Key</h3>
                        <button type="button" @click="isCreateKeyOpen = false" class="text-text-tertiary hover:text-text-primary">✕</button>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-text-secondary mb-1">Key Name</label>
                        <input
                            v-model="newKeyName"
                            type="text"
                            required
                            placeholder="e.g. GitHub Actions CI, Terminal Tool"
                            class="w-full px-3.5 py-2 rounded-xl bg-surface-secondary border border-primary text-xs text-text-primary placeholder-text-tertiary focus:border-indigo-500 focus:outline-hidden"
                        />
                    </div>

                    <div v-if="organizations.length > 0">
                        <label class="block text-xs font-medium text-text-secondary mb-1">Organization (Optional)</label>
                        <select
                            v-model="selectedOrgId"
                            class="w-full px-3.5 py-2 rounded-xl bg-surface-secondary border border-primary text-xs text-text-primary focus:border-indigo-500 focus:outline-hidden"
                        >
                            <option :value="null">Personal Scope (No Organization)</option>
                            <option v-for="org in organizations" :key="org.id" :value="org.id">
                                {{ org.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-text-secondary mb-1">Expiration Period</label>
                        <select
                            v-model="expiresInDays"
                            class="w-full px-3.5 py-2 rounded-xl bg-surface-secondary border border-primary text-xs text-text-primary focus:border-indigo-500 focus:outline-hidden"
                        >
                            <option :value="30">30 Days</option>
                            <option :value="90">90 Days (Recommended)</option>
                            <option :value="365">1 Year</option>
                            <option :value="null">Never Expires</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-primary">
                        <button
                            type="button"
                            @click="isCreateKeyOpen = false"
                            class="px-4 py-2 rounded-xl border border-primary text-text-secondary hover:text-text-primary text-xs"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmittingKey || !newKeyName.trim()"
                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white font-medium text-xs transition-colors"
                        >
                            {{ isSubmittingKey ? 'Creating...' : 'Create Key' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- MODAL: Configure AI Provider Key (BYOK)                            -->
        <!-- ================================================================= -->
        <div v-if="isByokModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-xs">
            <div class="w-full max-w-md p-6 rounded-2xl bg-surface-elevated border border-primary shadow-xl">
                <form @submit.prevent="saveByokCredential" class="space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-primary">
                        <div class="flex items-center gap-2">
                            <span>{{ providerConfig[activeProvider]?.icon }}</span>
                            <h3 class="text-sm font-bold text-text-primary">
                                Configure {{ providerConfig[activeProvider]?.name }}
                            </h3>
                        </div>
                        <button type="button" @click="isByokModalOpen = false" class="text-text-tertiary hover:text-text-primary">✕</button>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-text-secondary mb-1">API Key</label>
                        <input
                            v-model="apiKeyInput"
                            type="password"
                            required
                            :placeholder="providerConfig[activeProvider]?.placeholder"
                            class="w-full px-3.5 py-2 rounded-xl bg-surface-secondary border border-primary text-xs font-mono text-text-primary placeholder-text-tertiary focus:border-indigo-500 focus:outline-hidden"
                        />
                        <p class="text-[11px] text-text-tertiary mt-1">
                            Stored in MySQL with AES-256-GCM encryption. Never logged in plaintext.
                        </p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-text-secondary mb-1">Key Label (Optional)</label>
                        <input
                            v-model="byokLabel"
                            type="text"
                            placeholder="e.g. Production Anthropic Key"
                            class="w-full px-3.5 py-2 rounded-xl bg-surface-secondary border border-primary text-xs text-text-primary placeholder-text-tertiary focus:border-indigo-500 focus:outline-hidden"
                        />
                    </div>

                    <div v-if="organizations.length > 0">
                        <label class="block text-xs font-medium text-text-secondary mb-1">Organization Scope</label>
                        <select
                            v-model="selectedByokOrgId"
                            class="w-full px-3.5 py-2 rounded-xl bg-surface-secondary border border-primary text-xs text-text-primary focus:border-indigo-500 focus:outline-hidden"
                        >
                            <option :value="null">Personal (My Account Only)</option>
                            <option v-for="org in organizations" :key="org.id" :value="org.id">
                                Share with {{ org.name }}
                            </option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-primary">
                        <button
                            type="button"
                            @click="isByokModalOpen = false"
                            class="px-4 py-2 rounded-xl border border-primary text-text-secondary hover:text-text-primary text-xs"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="isSubmittingByok || !apiKeyInput.trim()"
                            class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 text-white font-medium text-xs transition-colors"
                        >
                            {{ isSubmittingByok ? 'Saving...' : 'Save & Encrypt' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

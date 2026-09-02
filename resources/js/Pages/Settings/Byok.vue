<script setup lang="ts">
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

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

const props = defineProps<{
    supported_providers: string[];
    credentials: ByokCredential[];
    organizations: OrganizationOption[];
}>();

// Modal state
const isModalOpen = ref(false);
const activeProvider = ref('openai');
const apiKeyInput = ref('');
const keyLabel = ref('');
const selectedOrgId = ref<number | null>(null);
const isSubmitting = ref(false);
const successMessage = ref<string | null>(null);

const providerConfig: Record<string, { name: string; icon: string; docsUrl: string; placeholder: string }> = {
    openai: {
        name: 'OpenAI',
        icon: '🧠',
        docsUrl: 'https://platform.openai.com/api-keys',
        placeholder: 'sk-proj-...',
    },
    anthropic: {
        name: 'Anthropic Claude',
        icon: '⚡',
        docsUrl: 'https://console.anthropic.com/settings/keys',
        placeholder: 'sk-ant-api03-...',
    },
    gemini: {
        name: 'Google Gemini',
        icon: '✨',
        docsUrl: 'https://aistudio.google.com/app/apikey',
        placeholder: 'AIzaSy...',
    },
};

const openConfigureModal = (provider: string) => {
    activeProvider.value = provider;
    apiKeyInput.value = '';
    keyLabel.value = '';
    isModalOpen.value = true;
};

const saveCredential = async () => {
    if (!apiKeyInput.value.trim()) return;
    isSubmitting.value = true;

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
                label: keyLabel.value || null,
                organization_id: selectedOrgId.value,
            }),
        });

        const data = await res.json();
        if (data.success) {
            isModalOpen.value = false;
            successMessage.value = `${providerConfig[activeProvider.value]?.name || activeProvider.value} credential encrypted and saved!`;
            setTimeout(() => { successMessage.value = null; }, 5000);
            router.reload();
        } else {
            alert(data.message || 'Failed to save credential.');
        }
    } finally {
        isSubmitting.value = false;
    }
};

const deleteCredential = async (provider: string) => {
    if (!confirm(`Are you sure you want to remove your ${providerConfig[provider]?.name || provider} BYOK credential?`)) {
        return;
    }

    await fetch(`/settings/byok/${provider}`, {
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
        <Head title="Bring Your Own Key (BYOK) — FORGE" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
            <!-- Header Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-primary pb-6">
                <div>
                    <h1 class="text-2xl font-display font-bold text-text-primary flex items-center gap-3">
                        🔐 Bring Your Own Key (BYOK)
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-mono">
                            Enterprise Feature
                        </span>
                    </h1>
                    <p class="text-sm text-text-secondary mt-1">
                        Connect your own direct LLM provider credentials. Keys are encrypted with AES-256 and operations enjoy slashed platform credit costs (1 credit/run).
                    </p>
                </div>
            </div>

            <!-- Success Alert Banner -->
            <div v-if="successMessage" class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-mono flex items-center justify-between">
                <span>{{ successMessage }}</span>
                <button @click="successMessage = null" class="text-emerald-400 hover:text-emerald-300 font-bold ml-4">✕</button>
            </div>

            <!-- Security Assurance Callout -->
            <div class="p-4 rounded-2xl bg-surface-secondary border border-primary flex items-start gap-4">
                <div class="w-8 h-8 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-base shrink-0">
                    🛡️
                </div>
                <div class="text-xs space-y-1">
                    <div class="font-bold text-text-primary">Military-Grade AES-256 Cipher Storage</div>
                    <div class="text-text-secondary">
                        Your secret keys are encrypted before storage and only decrypted in-memory during operation dispatch. FORGE never logs keys, never displays raw secrets in the browser, and never transfers keys to unauthorized third parties.
                    </div>
                </div>
            </div>

            <!-- Provider Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    v-for="provider in supported_providers"
                    :key="provider"
                    class="p-6 rounded-2xl bg-surface-secondary border border-primary flex flex-col justify-between space-y-6"
                >
                    <div class="space-y-4">
                        <!-- Provider Title & Icon -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="text-2xl">{{ providerConfig[provider]?.icon || '🤖' }}</span>
                                <div>
                                    <h3 class="font-bold text-text-primary text-sm">{{ providerConfig[provider]?.name || provider }}</h3>
                                    <a
                                        :href="providerConfig[provider]?.docsUrl"
                                        target="_blank"
                                        class="text-[10px] font-mono text-indigo-400 hover:underline inline-flex items-center gap-1"
                                    >
                                        Get API Key ↗
                                    </a>
                                </div>
                            </div>

                            <!-- Connection Status Badge -->
                            <span
                                v-if="credentials.find(c => c.provider === provider)"
                                class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-emerald-500/15 text-emerald-400 border border-emerald-500/30"
                            >
                                Active ✓
                            </span>
                            <span
                                v-else
                                class="px-2 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-surface-tertiary text-text-tertiary border border-primary"
                            >
                                Not Set
                            </span>
                        </div>

                        <!-- Card Body / Key Details -->
                        <div v-if="credentials.find(c => c.provider === provider)" class="p-3 rounded-xl bg-surface-tertiary border border-primary space-y-2">
                            <div class="flex items-center justify-between text-[11px] font-mono">
                                <span class="text-text-tertiary">Masked Key:</span>
                                <span class="text-text-primary font-bold">{{ credentials.find(c => c.provider === provider)?.masked_key }}</span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] font-mono">
                                <span class="text-text-tertiary">Rate:</span>
                                <span class="text-emerald-400 font-bold">1 Credit / Run (Discounted)</span>
                            </div>
                        </div>

                        <div v-else class="p-3 rounded-xl bg-surface-tertiary border border-primary text-text-tertiary text-xs font-mono">
                            Using standard platform key. Standard credit consumption applies.
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 pt-2">
                        <button
                            @click="openConfigureModal(provider)"
                            class="flex-1 px-3.5 py-2 rounded-xl text-xs font-semibold brand-button shadow-xs text-center"
                        >
                            {{ credentials.find(c => c.provider === provider) ? 'Update Key' : 'Configure Key' }}
                        </button>

                        <button
                            v-if="credentials.find(c => c.provider === provider)"
                            @click="deleteCredential(provider)"
                            class="px-3 py-2 rounded-xl text-xs font-mono text-red-400/80 hover:text-red-400 hover:bg-red-500/10 transition-colors"
                            title="Remove Key"
                        >
                            ✕
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Configure Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
            <div class="w-full max-w-md p-6 rounded-2xl bg-surface-secondary border border-primary shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-primary pb-3">
                    <h3 class="text-sm font-bold text-text-primary">
                        Configure {{ providerConfig[activeProvider]?.name }} Key
                    </h3>
                    <button @click="isModalOpen = false" class="text-text-tertiary hover:text-text-primary">✕</button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="block font-mono text-text-tertiary mb-1">API Secret Key</label>
                        <input
                            v-model="apiKeyInput"
                            type="password"
                            :placeholder="providerConfig[activeProvider]?.placeholder || 'Paste secret key...'"
                            class="w-full px-3 py-2 rounded-xl bg-surface-tertiary border border-primary text-text-primary font-mono focus:outline-hidden focus:border-indigo-500"
                        />
                    </div>

                    <div>
                        <label class="block font-mono text-text-tertiary mb-1">Scope</label>
                        <select
                            v-model="selectedOrgId"
                            class="w-full px-3 py-2 rounded-xl bg-surface-tertiary border border-primary text-text-primary focus:outline-hidden"
                        >
                            <option :value="null">👤 Personal Scope</option>
                            <option v-for="org in organizations" :key="org.id" :value="org.id">
                                🏢 {{ org.name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-mono text-text-tertiary mb-1">Key Label (Optional)</label>
                        <input
                            v-model="keyLabel"
                            type="text"
                            placeholder="e.g. Production GPT-4o Key"
                            class="w-full px-3 py-2 rounded-xl bg-surface-tertiary border border-primary text-text-primary focus:outline-hidden"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-primary">
                    <button
                        @click="isModalOpen = false"
                        class="px-4 py-2 rounded-xl text-xs font-mono text-text-secondary hover:bg-surface-tertiary"
                    >
                        Cancel
                    </button>
                    <button
                        @click="saveCredential"
                        :disabled="isSubmitting || !apiKeyInput.trim()"
                        class="px-4 py-2 rounded-xl text-xs font-semibold brand-button shadow-xs disabled:opacity-50"
                    >
                        {{ isSubmitting ? 'Encrypting...' : 'Save & Encrypt' }}
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

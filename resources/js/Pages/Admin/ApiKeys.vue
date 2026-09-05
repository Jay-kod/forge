<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

interface ProviderInfo {
    name: string;
    category: 'ai' | 'billing' | 'integration' | 'research';
    icon: string;
    env_var: string;
    is_configured: boolean;
    masked_key: string;
    default_model?: string;
    mode?: string;
    webhook_configured?: boolean;
    callback_url?: string;
    docs_url: string;
}

const props = defineProps<{
    providers: Record<string, ProviderInfo>;
}>();

// Live test results state
const testingProvider = ref<string | null>(null);
const testResults = ref<Record<string, { success: boolean; latency_ms: number; message: string }>>({});

const runConnectionTest = async (providerKey: string) => {
    testingProvider.value = providerKey;

    try {
        const res = await fetch('/admin/api-keys/test', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({ provider: providerKey }),
        });

        const data = await res.json();
        testResults.value[providerKey] = data;
    } catch (err: any) {
        testResults.value[providerKey] = {
            success: false,
            latency_ms: 0,
            message: err.message || 'Network request failed',
        };
    } finally {
        testingProvider.value = null;
    }
};
</script>

<template>
    <AppLayout>
        <Head title="System API Keys — Admin Operations" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
            <!-- Header & Admin Sub-nav -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-primary">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase tracking-wider bg-amber-500/20 text-amber-400 border border-amber-500/30">
                            Superadmin Console
                        </span>
                    </div>
                    <h1 class="text-2xl font-bold font-display tracking-tight text-text-primary mt-1">
                        System API Keys & Integrations
                    </h1>
                    <p class="text-xs text-text-tertiary mt-1">
                        Inspect active system credentials, test real-time provider latency, and monitor external platform connectivity.
                    </p>
                </div>

                <!-- Admin Navigation Switcher -->
                <div class="flex items-center gap-2">
                    <Link
                        :href="route('admin.dashboard')"
                        class="px-3.5 py-2 rounded-xl border border-primary bg-surface-secondary text-text-secondary hover:text-text-primary text-xs font-medium transition-colors"
                    >
                        📊 Metrics & Users
                    </Link>
                    <Link
                        :href="route('admin.api-keys.index')"
                        class="px-3.5 py-2 rounded-xl bg-amber-500/15 text-amber-400 font-semibold border border-amber-500/30 text-xs shadow-xs"
                    >
                        🔑 System API Keys
                    </Link>
                </div>
            </div>

            <!-- Provider Cards Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div
                    v-for="(info, key) in providers"
                    :key="key"
                    class="p-6 rounded-2xl border bg-surface-secondary flex flex-col justify-between transition-all"
                    :class="info.is_configured ? 'border-primary' : 'border-amber-500/30 bg-amber-500/5'"
                >
                    <div>
                        <!-- Card Top Bar -->
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="text-3xl p-2 rounded-xl bg-surface-tertiary border border-primary">
                                    {{ info.icon }}
                                </span>
                                <div>
                                    <h3 class="text-base font-bold text-text-primary flex items-center gap-2">
                                        <span>{{ info.name }}</span>
                                        <span class="text-[10px] font-mono px-2 py-0.5 rounded-md bg-surface-tertiary text-text-tertiary uppercase">
                                            {{ info.category }}
                                        </span>
                                    </h3>
                                    <div class="text-[11px] font-mono text-text-tertiary mt-0.5">
                                        ENV: <code class="text-indigo-400">{{ info.env_var }}</code>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <span
                                v-if="info.is_configured"
                                class="px-2.5 py-1 rounded-full text-[10px] font-mono font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 flex items-center gap-1.5"
                            >
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                Configured
                            </span>
                            <span
                                v-else
                                class="px-2.5 py-1 rounded-full text-[10px] font-mono font-semibold bg-amber-500/15 text-amber-300 border border-amber-500/30"
                            >
                                Missing in .env
                            </span>
                        </div>

                        <!-- Details Section -->
                        <div class="mt-5 pt-4 border-t border-primary space-y-2.5 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-text-tertiary">Masked Credential:</span>
                                <span class="font-mono px-2 py-1 rounded-md bg-surface-tertiary border border-primary text-text-secondary text-[11px]">
                                    {{ info.masked_key }}
                                </span>
                            </div>

                            <div v-if="info.default_model" class="flex items-center justify-between">
                                <span class="text-text-tertiary">Active Default Model:</span>
                                <span class="font-mono text-text-primary text-[11px]">
                                    {{ info.default_model }}
                                </span>
                            </div>

                            <div v-if="info.mode" class="flex items-center justify-between">
                                <span class="text-text-tertiary">Stripe Gateway Mode:</span>
                                <span
                                    class="font-mono text-[11px] uppercase font-bold"
                                    :class="info.mode === 'live' ? 'text-emerald-400' : 'text-amber-400'"
                                >
                                    {{ info.mode }}
                                </span>
                            </div>

                            <div v-if="info.callback_url" class="flex items-center justify-between">
                                <span class="text-text-tertiary">Redirect URL:</span>
                                <span class="font-mono text-[11px] text-text-tertiary truncate max-w-xs">
                                    {{ info.callback_url }}
                                </span>
                            </div>
                        </div>

                        <!-- Live Test Result Banner -->
                        <div
                            v-if="testResults[key]"
                            class="mt-4 p-3 rounded-xl border text-xs font-mono transition-all"
                            :class="testResults[key].success ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-300' : 'bg-red-500/10 border-red-500/30 text-red-300'"
                        >
                            <div class="flex items-center justify-between font-bold">
                                <span>{{ testResults[key].success ? '✓ Connectivity Passed' : '✗ Connectivity Failed' }}</span>
                                <span v-if="testResults[key].latency_ms > 0">{{ testResults[key].latency_ms }} ms</span>
                            </div>
                            <p class="text-[11px] mt-1 text-text-secondary leading-snug">
                                {{ testResults[key].message }}
                            </p>
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="mt-6 pt-4 border-t border-primary flex items-center justify-between gap-3">
                        <a
                            :href="info.docs_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-xs font-mono text-indigo-400 hover:text-indigo-300 flex items-center gap-1"
                        >
                            <span>Provider Console</span>
                            <span>↗</span>
                        </a>

                        <button
                            type="button"
                            @click="runConnectionTest(key)"
                            :disabled="testingProvider === key"
                            class="px-4 py-2 rounded-xl text-xs font-medium transition-all flex items-center gap-2"
                            :class="info.is_configured ? 'bg-indigo-600 hover:bg-indigo-500 text-white shadow-xs' : 'bg-surface-tertiary text-text-tertiary border border-primary hover:text-text-primary'"
                        >
                            <span v-if="testingProvider === key" class="inline-block animate-spin">⟳</span>
                            <span>{{ testingProvider === key ? 'Probing...' : 'Test Connection' }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Operations & Environment Deployment Note -->
            <div class="p-6 rounded-2xl border border-primary bg-surface-secondary/70">
                <div class="flex items-center gap-2 text-sm font-bold text-text-primary mb-1">
                    <span>🛡️</span>
                    <span>Production Secrets Protocol</span>
                </div>
                <p class="text-xs text-text-tertiary leading-relaxed">
                    System API keys are loaded directly from encrypted server environment variables. To update or rotate provider keys in staging or production, edit your server's <code class="font-mono text-indigo-400">.env</code> file (or secrets manager), and run <code class="font-mono text-indigo-400">php artisan config:cache</code> followed by <code class="font-mono text-indigo-400">php artisan queue:restart</code>.
                </p>
            </div>
        </div>
    </AppLayout>
</template>

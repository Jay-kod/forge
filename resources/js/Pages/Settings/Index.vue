<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const props = defineProps<{
    profile: {
        name: string;
        email: string;
        role: string;
        technical_level: string;
        referral_code: string | null;
        created_at?: string;
    };
    github: {
        username: string;
        avatar_url: string | null;
        scope: string | null;
    } | null;
    apiKeys: any[];
    byok: any[];
    consent: {
        telemetry_enabled: boolean;
        allow_model_training: boolean;
        data_retention_days: number;
    };
}>();

const activeTab = ref<'profile' | 'accounts' | 'developer' | 'privacy'>('profile');

const profileForm = useForm({
    name: props.profile.name,
    technical_level: props.profile.technical_level || 'non_developer',
});

const saveProfile = () => {
    profileForm.post(route('settings.profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout>
        <Head title="Account & Platform Settings — FORGE" />

        <div class="max-w-5xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-primary">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-display font-extrabold tracking-tight text-text-primary">
                        Settings & Governance
                    </h1>
                    <p class="text-xs sm:text-sm text-text-secondary mt-1">
                        Manage your profile persona, connected accounts, developer API keys, and privacy controls.
                    </p>
                </div>
            </div>

            <!-- Settings Layout: Vertical Tabs on left, Content on right -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Tabs Menu -->
                <div class="space-y-1">
                    <button
                        @click="activeTab = 'profile'"
                        class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-colors flex items-center gap-2.5"
                        :class="activeTab === 'profile' ? 'bg-indigo-600 text-white shadow-xs' : 'text-text-secondary hover:text-text-primary hover:bg-surface-secondary'"
                    >
                        <span>👤</span>
                        <span>Profile & Persona</span>
                    </button>

                    <button
                        @click="activeTab = 'accounts'"
                        class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-colors flex items-center gap-2.5"
                        :class="activeTab === 'accounts' ? 'bg-indigo-600 text-white shadow-xs' : 'text-text-secondary hover:text-text-primary hover:bg-surface-secondary'"
                    >
                        <span>🐙</span>
                        <span>Connected Accounts</span>
                    </button>

                    <button
                        @click="activeTab = 'developer'"
                        class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-colors flex items-center gap-2.5"
                        :class="activeTab === 'developer' ? 'bg-indigo-600 text-white shadow-xs' : 'text-text-secondary hover:text-text-primary hover:bg-surface-secondary'"
                    >
                        <span>🔑</span>
                        <span>API Keys & BYOK</span>
                    </button>

                    <button
                        @click="activeTab = 'privacy'"
                        class="w-full text-left px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-colors flex items-center gap-2.5"
                        :class="activeTab === 'privacy' ? 'bg-indigo-600 text-white shadow-xs' : 'text-text-secondary hover:text-text-primary hover:bg-surface-secondary'"
                    >
                        <span>🔒</span>
                        <span>Privacy & Data</span>
                    </button>
                </div>

                <!-- Active Tab Content Area -->
                <div class="md:col-span-3 space-y-6">
                    <!-- Tab 1: Profile & Persona -->
                    <div v-if="activeTab === 'profile'" class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-xs space-y-6">
                        <h2 class="text-base font-bold text-text-primary pb-3 border-b border-primary">
                            Profile & Experience Personalization
                        </h2>

                        <form @submit.prevent="saveProfile" class="space-y-4 max-w-md">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-mono uppercase text-text-tertiary font-bold">
                                    Display Name
                                </label>
                                <input
                                    v-model="profileForm.name"
                                    type="text"
                                    class="w-full rounded-xl bg-surface-primary border border-primary px-3.5 py-2 text-xs text-text-primary focus:outline-hidden focus:border-indigo-500"
                                    required
                                />
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-mono uppercase text-text-tertiary font-bold">
                                    Email Address (Identity)
                                </label>
                                <input
                                    :value="profile.email"
                                    type="email"
                                    disabled
                                    class="w-full rounded-xl bg-surface-primary/50 border border-primary px-3.5 py-2 text-xs text-text-tertiary cursor-not-allowed font-mono"
                                />
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-mono uppercase text-text-tertiary font-bold">
                                    Technical Profile / Lens
                                </label>
                                <select
                                    v-model="profileForm.technical_level"
                                    class="w-full rounded-xl bg-surface-primary border border-primary px-3.5 py-2 text-xs text-text-primary focus:outline-hidden focus:border-indigo-500"
                                >
                                    <option value="non_developer">Business Founder / Non-Technical</option>
                                    <option value="vibe_coder">Vibe Coder / AI Explorer</option>
                                    <option value="developer">Full-Stack Developer</option>
                                    <option value="senior_developer">Senior Architect / Principal</option>
                                </select>
                                <span class="text-[10px] text-text-tertiary block font-mono">
                                    Adapts the Growth Center, Recommendations, and Architecture jargon.
                                </span>
                            </div>

                            <div class="pt-2">
                                <button
                                    type="submit"
                                    :disabled="profileForm.processing"
                                    class="px-5 py-2 rounded-xl brand-button text-xs font-bold shadow-xs disabled:opacity-50"
                                >
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Tab 2: Connected Accounts -->
                    <div v-if="activeTab === 'accounts'" class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-xs space-y-6">
                        <h2 class="text-base font-bold text-text-primary pb-3 border-b border-primary">
                            Connected Integrations & OAuth
                        </h2>

                        <div class="p-4 rounded-xl bg-surface-primary border border-primary flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-surface-tertiary border border-primary flex items-center justify-center text-lg">
                                    🐙
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-text-primary">GitHub</div>
                                    <div class="text-[11px] text-text-secondary font-mono">
                                        {{ github ? `@${github.username} connected` : 'Not connected' }}
                                    </div>
                                </div>
                            </div>

                            <div>
                                <a
                                    v-if="!github"
                                    :href="route('github.connect')"
                                    class="px-3.5 py-1.5 rounded-xl brand-button text-xs font-bold"
                                >
                                    Connect GitHub
                                </a>
                                <Link
                                    v-else
                                    :href="route('github.index')"
                                    class="px-3.5 py-1.5 rounded-xl border border-primary bg-surface-secondary hover:bg-surface-tertiary text-xs font-semibold text-text-secondary hover:text-text-primary transition-colors"
                                >
                                    Manage Permissions &rarr;
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Developer & API Keys -->
                    <div v-if="activeTab === 'developer'" class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-primary">
                            <div>
                                <h2 class="text-base font-bold text-text-primary">API Credentials & Bring Your Own Key</h2>
                                <p class="text-xs text-text-secondary mt-0.5">Manage programmatic tokens and custom AI provider endpoints.</p>
                            </div>
                            <Link :href="route('api-keys.index')" class="text-xs font-mono text-indigo-400 hover:underline">
                                Full Key Manager &rarr;
                            </Link>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl bg-surface-primary border border-primary space-y-1">
                                <span class="text-[10px] font-mono uppercase text-text-tertiary block">Active API Keys</span>
                                <div class="text-xl font-bold text-text-primary">{{ apiKeys.length }}</div>
                                <Link :href="route('api-keys.index')" class="text-[11px] font-mono text-indigo-400 block hover:underline">
                                    + Generate New Key
                                </Link>
                            </div>

                            <div class="p-4 rounded-xl bg-surface-primary border border-primary space-y-1">
                                <span class="text-[10px] font-mono uppercase text-text-tertiary block">BYOK Providers</span>
                                <div class="text-xl font-bold text-indigo-400">{{ byok.length }}</div>
                                <Link :href="route('byok.index')" class="text-[11px] font-mono text-indigo-400 block hover:underline">
                                    Configure Provider Keys &rarr;
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 4: Privacy & Data Controls -->
                    <div v-if="activeTab === 'privacy'" class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-xs space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-primary">
                            <div>
                                <h2 class="text-base font-bold text-text-primary">Privacy & Data Portability</h2>
                                <p class="text-xs text-text-secondary mt-0.5">Export your workspace data or manage telemetry preferences.</p>
                            </div>
                            <Link :href="route('privacy.index')" class="text-xs font-mono text-indigo-400 hover:underline">
                                Full Privacy Center &rarr;
                            </Link>
                        </div>

                        <div class="space-y-3">
                            <div class="p-4 rounded-xl bg-surface-primary border border-primary flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-bold text-text-primary block">GDPR & Export Archives</span>
                                    <span class="text-[11px] text-text-secondary">Download complete machine-readable copy of your data</span>
                                </div>
                                <a :href="route('privacy.export-data')" class="px-3.5 py-1.5 rounded-xl border border-primary bg-surface-secondary text-xs font-mono text-text-secondary hover:text-text-primary">
                                    Export JSON
                                </a>
                            </div>

                            <div class="p-4 rounded-xl bg-surface-primary border border-primary flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-bold text-text-primary block">Anonymized Learning Telemetry</span>
                                    <span class="text-[11px] text-text-secondary">Contribute signals without sharing private prompt data</span>
                                </div>
                                <span class="text-xs font-mono text-emerald-400">Opted In</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

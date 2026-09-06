<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps<{
    connection: {
        github_username: string;
        avatar_url: string | null;
        scope: string | null;
        has_repo_access: boolean;
        created_at: string;
    } | null;
    repositories: any[];
    audits: any[];
    projects: any[];
}>();
</script>

<template>
    <AppLayout>
        <Head title="GitHub Intelligence & Repositories — FORGE" />

        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Strategic Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-primary">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-display font-extrabold tracking-tight text-text-primary">
                        GitHub Integration
                    </h1>
                    <p class="text-xs sm:text-sm text-text-secondary mt-1">
                        Connect repositories, audit code architecture and technical debt, and export production blueprints.
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <a
                        v-if="!connection"
                        :href="route('github.connect')"
                        class="px-4 py-2.5 rounded-xl brand-button text-xs font-bold shadow-xs inline-flex items-center gap-2 transition-all hover:scale-[1.02]"
                    >
                        <span>🐙</span>
                        <span>Connect GitHub Account</span>
                    </a>
                    <Link
                        v-else
                        :href="route('github.disconnect')"
                        method="post"
                        as="button"
                        class="px-4 py-2 rounded-xl border border-red-500/30 bg-red-500/10 hover:bg-red-500/20 text-red-400 text-xs font-semibold transition-colors"
                    >
                        Disconnect Account
                    </Link>
                </div>
            </div>

            <!-- Permission Tiers Matrix: Separate Login vs Repo Access vs Write Actions -->
            <div class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-xs space-y-4">
                <h2 class="text-xs font-mono uppercase tracking-wider text-text-tertiary font-bold">
                    Permission & Governance Matrix
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Tier 1: Identity / Login -->
                    <div class="p-4 rounded-xl bg-surface-primary border border-primary space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-text-primary">1. GitHub Identity</span>
                            <span
                                class="px-2 py-0.5 rounded text-[10px] font-mono font-bold"
                                :class="connection ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-surface-tertiary text-text-tertiary border border-primary'"
                            >
                                {{ connection ? 'Active' : 'Not Connected' }}
                            </span>
                        </div>
                        <p class="text-xs text-text-secondary leading-relaxed">
                            Allows signing in with GitHub. Grants read-only access to public profile data.
                        </p>
                    </div>

                    <!-- Tier 2: Repository Read Access -->
                    <div class="p-4 rounded-xl bg-surface-primary border border-primary space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-text-primary">2. Repository Access</span>
                            <span
                                class="px-2 py-0.5 rounded text-[10px] font-mono font-bold"
                                :class="connection?.has_repo_access ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-400 border border-amber-500/30'"
                            >
                                {{ connection?.has_repo_access ? 'Authorized' : 'Requires Scope' }}
                            </span>
                        </div>
                        <p class="text-xs text-text-secondary leading-relaxed">
                            Allows FORGE to inspect directory trees, dependency manifests, and architecture structure.
                        </p>
                    </div>

                    <!-- Tier 3: Isolated Write Actions -->
                    <div class="p-4 rounded-xl bg-surface-primary border border-primary space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-text-primary">3. Isolated Write Actions</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-indigo-500/15 text-indigo-400 border border-indigo-500/30">
                                Isolated Branch Only
                            </span>
                        </div>
                        <p class="text-xs text-text-secondary leading-relaxed">
                            Never pushes directly to default branch. Always creates isolated blueprint branches or PRs.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Audited Repositories & Code Health -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-text-primary">
                        Repository Audits & Codebase Health
                    </h2>
                    <span class="text-xs font-mono text-text-tertiary">{{ audits.length }} Audited Repositories</span>
                </div>

                <div v-if="audits.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="audit in audits"
                        :key="audit.id"
                        class="p-5 rounded-2xl bg-surface-secondary border border-primary space-y-4"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-mono uppercase text-indigo-400 block mb-0.5 font-bold">
                                    Project: {{ audit.project?.title || 'Standalone' }}
                                </span>
                                <h3 class="text-sm font-bold text-text-primary">{{ audit.repo_full_name }}</h3>
                            </div>
                            <span class="text-xs font-mono px-2 py-0.5 rounded bg-surface-primary text-text-secondary border border-primary">
                                Branch: {{ audit.default_branch || 'main' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="p-2.5 rounded-xl bg-surface-primary border border-primary">
                                <span class="text-[10px] font-mono text-text-tertiary block">Code Health</span>
                                <span class="text-sm font-bold text-emerald-400 font-mono">{{ audit.code_health_score || 85 }}/100</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-surface-primary border border-primary">
                                <span class="text-[10px] font-mono text-text-tertiary block">Tech Debt</span>
                                <span class="text-sm font-bold text-amber-400 font-mono">{{ audit.technical_debt_score || 20 }}/100</span>
                            </div>
                            <div class="p-2.5 rounded-xl bg-surface-primary border border-primary">
                                <span class="text-[10px] font-mono text-text-tertiary block">Language</span>
                                <span class="text-xs font-bold text-text-primary truncate block mt-0.5">{{ audit.primary_language || 'PHP/JS' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2 border-t border-primary text-xs">
                            <span class="text-text-tertiary font-mono">
                                Framework: <strong class="text-text-primary">{{ audit.detected_framework || 'Laravel/Vue' }}</strong>
                            </span>
                            <Link
                                v-if="audit.project_id"
                                :href="route('projects.show', audit.project_id)"
                                class="text-indigo-400 font-mono hover:underline"
                            >
                                Open Workspace &rarr;
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12 bg-surface-secondary border border-dashed border-primary rounded-2xl">
                    <span class="text-3xl block mb-2">🐙</span>
                    <h3 class="text-sm font-bold text-text-primary mb-1">No Repositories Audited Yet</h3>
                    <p class="text-xs text-text-secondary max-w-sm mx-auto mb-4">
                        Connect a GitHub repository inside any project workspace to run automated architectural audits.
                    </p>
                </div>
            </div>

            <!-- Authorized Repositories List (if connected) -->
            <div v-if="connection && repositories.length > 0" class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-xs space-y-4">
                <h2 class="text-base font-bold text-text-primary">Authorized Repositories ({{ repositories.length }})</h2>
                <div class="divide-y divide-primary/50 max-h-96 overflow-y-auto custom-scrollbar">
                    <div
                        v-for="repo in repositories"
                        :key="repo.id"
                        class="py-3 first:pt-0 last:pb-0 flex items-center justify-between gap-4"
                    >
                        <div class="min-w-0">
                            <span class="text-xs font-bold text-text-primary block truncate">{{ repo.full_name }}</span>
                            <span class="text-[11px] text-text-tertiary font-mono">
                                {{ repo.private ? '🔒 Private' : '🌐 Public' }} &bull; {{ repo.language || 'Codebase' }}
                            </span>
                        </div>
                        <a
                            :href="repo.html_url"
                            target="_blank"
                            class="text-xs font-mono text-indigo-400 hover:underline shrink-0"
                        >
                            View on GitHub &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

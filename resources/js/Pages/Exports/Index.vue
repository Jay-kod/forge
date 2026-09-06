<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps<{
    projects: Array<{
        id: number;
        title: string;
        classification: string;
        status: string;
        versions?: any[];
        repository_audit?: any;
    }>;
}>();

const copiedId = ref<number | null>(null);

const copyMasterPrompt = (project: any) => {
    const prompt = `# FORGE MASTER PROMPT: ${project.title}\n\nProject ID: ${project.id}\nClassification: ${project.classification}\n\nInspect generated PRD, Architecture, and AGENTS.md before writing code. Enforce module-based architecture and test all failure cases.`;
    navigator.clipboard.writeText(prompt);
    copiedId.value = project.id;
    setTimeout(() => {
        copiedId.value = null;
    }, 2000);
};
</script>

<template>
    <AppLayout>
        <Head title="Generated Artifacts & Exports — FORGE" />

        <div class="max-w-7xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-primary">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-display font-extrabold tracking-tight text-text-primary">
                        Generated Artifacts & Exports
                    </h1>
                    <p class="text-xs sm:text-sm text-text-secondary mt-1">
                        Revisit and download specifications, AI project packages, printable blueprints, and GitHub export branches.
                    </p>
                </div>

                <span class="text-xs font-mono text-text-tertiary">
                    Workspaces with Artifacts: <strong class="text-text-primary">{{ projects.length }}</strong>
                </span>
            </div>

            <!-- Artifacts Grid -->
            <div v-if="projects.length > 0" class="space-y-4">
                <div
                    v-for="proj in projects"
                    :key="proj.id"
                    class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-6"
                >
                    <!-- Project Summary -->
                    <div class="space-y-2 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono uppercase font-bold bg-surface-tertiary text-indigo-400 border border-primary">
                                {{ proj.classification }}
                            </span>
                            <span class="text-xs font-mono text-text-tertiary">
                                Workspace #{{ proj.id }} &bull; {{ proj.versions?.length || 1 }} versions recorded
                            </span>
                        </div>

                        <h2 class="text-base font-bold text-text-primary">
                            {{ proj.title }}
                        </h2>

                        <div class="flex flex-wrap items-center gap-3 text-xs text-text-secondary font-mono">
                            <span>📄 PRD.md</span>
                            <span>&bull;</span>
                            <span>🏗️ Architecture.md</span>
                            <span>&bull;</span>
                            <span>🤖 AGENTS.md</span>
                            <span>&bull;</span>
                            <span>📋 CLAUDE.md</span>
                        </div>
                    </div>

                    <!-- Export Actions Strip -->
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <button
                            @click="copyMasterPrompt(proj)"
                            class="px-3 py-2 rounded-xl border border-primary bg-surface-primary hover:bg-surface-tertiary text-xs font-mono text-text-secondary hover:text-text-primary transition-colors"
                        >
                            <span v-if="copiedId === proj.id">✓ Copied!</span>
                            <span v-else>📋 Copy Master Prompt</span>
                        </button>

                        <a
                            :href="route('export.pdf', proj.id)"
                            class="px-3 py-2 rounded-xl border border-primary bg-surface-primary hover:bg-surface-tertiary text-xs font-mono text-text-secondary hover:text-text-primary transition-colors"
                        >
                            📄 PDF Blueprint
                        </a>

                        <a
                            v-if="['BUSINESS_GROWTH', 'WEBSITE_IMPROVEMENT', 'MARKET_EXPANSION', 'STRATEGIC_PLANNING'].includes(proj.classification)"
                            :href="route('export.growth-plan', proj.id)"
                            class="px-3 py-2 rounded-xl border border-primary bg-surface-primary hover:bg-surface-tertiary text-xs font-mono text-text-secondary hover:text-text-primary transition-colors"
                        >
                            🚀 Growth Plan PDF
                        </a>

                        <a
                            :href="route('export.package', proj.id)"
                            class="px-3.5 py-2 rounded-xl brand-button text-xs font-bold shadow-xs inline-flex items-center gap-1.5"
                        >
                            <span>📦 AI Package.zip</span>
                        </a>

                        <Link
                            :href="route('projects.show', proj.id)"
                            class="p-2 rounded-xl border border-primary hover:bg-surface-tertiary text-text-secondary hover:text-text-primary transition-colors"
                            title="Open project workspace"
                        >
                            &rarr;
                        </Link>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-16 bg-surface-secondary border border-dashed border-primary rounded-2xl">
                <span class="text-3xl block mb-2">📦</span>
                <h3 class="text-sm font-bold text-text-primary mb-1">No Artifacts Generated Yet</h3>
                <p class="text-xs text-text-secondary max-w-sm mx-auto mb-4">
                    Complete stages in any project workspace to generate printable blueprints, architecture packages, and AI development files.
                </p>
                <Link :href="route('projects.index')" class="px-4 py-2 rounded-xl brand-button text-xs font-bold inline-block">
                    Go to Projects
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import WorkflowProgressBar from '@/Components/WorkflowProgressBar.vue';
import DiscoveryVerdictCard from '@/Components/DiscoveryVerdictCard.vue';
import CompetitorMatrix from '@/Components/CompetitorMatrix.vue';
import EvidenceRegistry from '@/Components/EvidenceRegistry.vue';
import DocumentViewer from '@/Components/DocumentViewer.vue';
import WebsiteAuditCard from '@/Components/WebsiteAuditCard.vue';
import OpportunityMatrix from '@/Components/OpportunityMatrix.vue';
import GitHubConnectModal from '@/Components/GitHub/GitHubConnectModal.vue';
import RepositoryAuditCard from '@/Components/GitHub/RepositoryAuditCard.vue';
import GitHubExportModal from '@/Components/GitHub/GitHubExportModal.vue';
import type { Project, WorkflowStage } from '@/types';

const props = defineProps<{
    project: Project & {
        context?: any;
        workflow?: { stages: WorkflowStage[] };
        discovery?: any;
        competitors?: any[];
        evidence?: any[];
        opportunities?: any[];
        documents?: any[];
        versions?: any[];
        website_analysis?: any;
        repository_audit?: any;
    };
    githubConnection?: any;
}>();

const activeStage = computed(() => {
    return props.project.workflow?.stages.find(s => s.status === 'active')
        || props.project.workflow?.stages[0];
});

const isRunning = ref(false);
const showGitHubModal = ref(false);
const showExportModal = ref(false);
const currentAudit = ref(props.project.repository_audit);

const handleScanned = (auditData: any) => {
    currentAudit.value = auditData;
};

const advanceStage = (stage: WorkflowStage) => {
    isRunning.value = true;
    router.post(route('workflow.advance', { project: props.project.id, stage: stage.id }), {}, {
        onFinish: () => { isRunning.value = false; }
    });
};

const approveStage = (stage: WorkflowStage) => {
    router.post(route('workflow.approve', { project: props.project.id, stage: stage.id }));
};

const copyMasterPrompt = () => {
    const prompt = `# FORGE MASTER PROMPT: ${props.project.title}\n\nProject: ${props.project.description}\nClassification: ${props.project.classification}\n\nInspect generated PRD, Architecture, and AGENTS.md before writing code. Enforce module-based architecture and test all failure cases.`;
    navigator.clipboard.writeText(prompt);
    alert('Master Prompt copied to clipboard!');
};
</script>

<template>
    <AppLayout>
        <Head :title="`${project.title} — Workspace`" />

        <!-- Project Header -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6 pb-6 border-b border-primary">
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-mono font-bold bg-indigo-500/15 border border-indigo-500/30 text-indigo-400">
                        {{ project.classification }}
                    </span>
                    <span class="text-xs text-text-tertiary font-mono">
                        Workspace #{{ project.id }} &bull; {{ project.status }}
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-display font-extrabold tracking-tight text-text-primary">
                    {{ project.title }}
                </h1>
                <p class="text-xs sm:text-sm text-text-secondary mt-1 max-w-3xl leading-relaxed">
                    {{ project.description }}
                </p>
            </div>

            <!-- Export Actions -->
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <button
                    @click="copyMasterPrompt"
                    class="px-3 py-2 rounded-xl border border-primary bg-surface-secondary hover:bg-surface-tertiary text-text-secondary hover:text-text-primary text-xs font-mono transition-colors"
                    title="Copy AI Master Prompt"
                >
                    📋 Master Prompt
                </button>
                <button
                    @click="showGitHubModal = true"
                    class="px-3 py-2 rounded-xl border border-primary bg-surface-secondary hover:bg-surface-tertiary text-text-secondary hover:text-text-primary text-xs font-mono transition-colors inline-flex items-center gap-1.5"
                    title="Connect & Scan GitHub Repository"
                >
                    <span>🐙</span>
                    <span>{{ currentAudit ? currentAudit.repo_full_name : (githubConnection ? 'GitHub Connected' : 'Connect GitHub') }}</span>
                </button>
                <a
                    :href="route('export.pdf', project.id)"
                    class="px-3 py-2 rounded-xl border border-primary bg-surface-secondary hover:bg-surface-tertiary text-text-secondary hover:text-text-primary text-xs font-mono transition-colors inline-flex items-center gap-1.5"
                    title="Download Printable PDF Blueprint"
                >
                    <span>📄 Blueprint PDF</span>
                </a>
                <a
                    v-if="['BUSINESS_GROWTH', 'WEBSITE_IMPROVEMENT', 'MARKET_EXPANSION', 'STRATEGIC_PLANNING'].includes(project.classification)"
                    :href="route('export.growth-plan', project.id)"
                    class="px-3 py-2 rounded-xl border border-primary bg-surface-secondary hover:bg-surface-tertiary text-text-secondary hover:text-text-primary text-xs font-mono transition-colors inline-flex items-center gap-1.5"
                    title="Download Executive Growth Plan & Strategy PDF"
                >
                    <span>🚀 Growth Plan PDF</span>
                </a>
                <a
                    :href="route('export.package', project.id)"
                    class="px-3.5 py-2 rounded-xl brand-button text-xs font-bold shadow-md inline-flex items-center gap-1.5"
                >
                    <span>📦 AI Package.zip</span>
                </a>
            </div>
        </div>

        <!-- Workflow Progress Bar -->
        <div v-if="project.workflow?.stages" class="mb-8">
            <WorkflowProgressBar
                :stages="project.workflow.stages"
                :current-stage-type="project.current_stage"
            />
        </div>

        <!-- Main Stage Workspace -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Left 2 Cols: Active Stage Detail, Verdict & Intelligence Output -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Discovery Verdict Card (when evaluated) -->
                <DiscoveryVerdictCard :discovery="project.discovery" />

                <!-- Live Website Performance & UX Audit (when analyzed) -->
                <WebsiteAuditCard :analysis="project.website_analysis" />

                <!-- GitHub Repository Code Health & Architecture Audit -->
                <RepositoryAuditCard
                    v-if="currentAudit"
                    :audit="currentAudit"
                    :project="project"
                    @open-export="showExportModal = true"
                    @rescan="showGitHubModal = true"
                />

                <!-- Active Stage Card -->
                <div v-if="activeStage" class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-md">
                    <!-- Stage Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-primary mb-6">
                        <div>
                            <span class="text-[11px] font-mono uppercase text-indigo-400 font-semibold block mb-0.5">
                                Stage #0{{ activeStage.order }} Intelligence Execution
                            </span>
                            <h2 class="text-xl font-display font-bold text-text-primary capitalize">
                                {{ activeStage.stage_type.replace('_', ' ') }}
                            </h2>
                        </div>

                        <div class="flex items-center gap-2">
                            <span
                                class="px-2.5 py-1 rounded-full text-xs font-mono font-medium"
                                :class="activeStage.status === 'completed' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-amber-500/15 text-amber-400 border border-amber-500/30'"
                            >
                                {{ activeStage.status }}
                            </span>
                        </div>
                    </div>

                    <!-- Stage Content / Analysis -->
                    <div class="mb-8">
                        <div v-if="activeStage.content" class="prose prose-sm dark:prose-invert max-w-none">
                            <div class="p-4 rounded-xl bg-surface-primary border border-primary font-mono text-xs leading-relaxed text-text-primary whitespace-pre-wrap">
                                {{ activeStage.content.summary || activeStage.content.analysis || JSON.stringify(activeStage.content, null, 2) }}
                            </div>
                        </div>

                        <div v-else class="text-center py-10 px-4 bg-surface-primary border border-dashed border-primary rounded-xl">
                            <span class="text-3xl mb-2 block">🧠</span>
                            <h3 class="text-sm font-semibold text-text-primary mb-1">Ready for Intelligence Execution</h3>
                            <p class="text-xs text-text-secondary max-w-md mx-auto mb-4">
                                Execute multi-source research, synthesize traceable evidence, and evaluate competitive strategy for this stage.
                            </p>
                            <button
                                @click="advanceStage(activeStage)"
                                :disabled="isRunning"
                                class="px-5 py-2.5 rounded-xl brand-button text-xs font-bold shadow-md disabled:opacity-50"
                            >
                                <span v-if="isRunning">Reasoning Over Evidence...</span>
                                <span v-else>⚡ Execute Stage Analysis (15 Credits)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Stage Controls -->
                    <div v-if="activeStage.content" class="pt-4 border-t border-primary flex items-center justify-between">
                        <span class="text-xs text-text-tertiary font-mono">
                            {{ activeStage.approved_at ? '✓ Approved' : 'Awaiting Review' }}
                        </span>

                        <div class="flex items-center gap-3">
                            <button
                                v-if="!activeStage.approved_at"
                                @click="approveStage(activeStage)"
                                class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold transition-colors shadow-xs"
                            >
                                ✓ Approve Stage Output
                            </button>
                            <button
                                @click="advanceStage(activeStage)"
                                :disabled="isRunning"
                                class="px-4 py-2 rounded-xl brand-button text-xs font-bold transition-colors shadow-xs"
                            >
                                Next Stage &rarr;
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Competitor Intelligence Matrix -->
                <CompetitorMatrix :competitors="project.competitors" />

                <!-- Action Priority Matrix (Opportunities) -->
                <OpportunityMatrix :opportunities="project.opportunities" />

                <!-- Evidence & Research Registry -->
                <EvidenceRegistry :evidence="project.evidence" :project-id="project.id" />
            </div>

            <!-- Right 1 Col: Context & Package Inspector -->
            <div class="space-y-6">
                <!-- Situation Understanding Model -->
                <div class="bg-surface-secondary border border-primary rounded-2xl p-5 shadow-md">
                    <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-text-tertiary mb-3">
                        Unified Context Model
                    </h3>
                    <div class="space-y-2.5 text-xs">
                        <div class="flex justify-between py-1.5 border-b border-primary">
                            <span class="text-text-secondary">Classification:</span>
                            <span class="font-mono font-semibold text-indigo-400">{{ project.classification }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-primary">
                            <span class="text-text-secondary">Workflow Mode:</span>
                            <span class="font-mono text-text-primary capitalize">{{ project.workflow_mode }}</span>
                        </div>
                        <div class="flex justify-between py-1.5 border-b border-primary">
                            <span class="text-text-secondary">Confidence:</span>
                            <span class="font-mono text-emerald-400 font-semibold">92%</span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-text-secondary">Version:</span>
                            <span class="font-mono text-text-primary">v{{ project.versions?.length || 1 }}.0</span>
                        </div>
                    </div>
                </div>

                <!-- Generated Documents Overview Card -->
                <div class="bg-surface-secondary border border-primary rounded-2xl p-5 shadow-md">
                    <h3 class="text-xs font-mono font-bold uppercase tracking-wider text-text-tertiary mb-3">
                        Package Specifications
                    </h3>
                    <div class="space-y-2 text-xs font-mono">
                        <div class="flex items-center justify-between p-2 rounded-lg bg-surface-primary border border-primary">
                            <span>📄 PRD.md</span>
                            <span class="text-emerald-400">Ready</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-surface-primary border border-primary">
                            <span>📄 Architecture.md</span>
                            <span class="text-emerald-400">Ready</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-surface-primary border border-primary">
                            <span>📄 AGENTS.md</span>
                            <span class="text-emerald-400">Ready</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-surface-primary border border-primary">
                            <span>📄 CLAUDE.md</span>
                            <span class="text-emerald-400">Ready</span>
                        </div>
                        <div class="flex items-center justify-between p-2 rounded-lg bg-surface-primary border border-primary">
                            <span>📄 MASTER-PROMPT.md</span>
                            <span class="text-emerald-400">Ready</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Full Document & Specification Viewer -->
        <div class="mt-8">
            <DocumentViewer
                :documents="project.documents"
                :project-title="project.title"
            />
        </div>

        <!-- GitHub Connect & Scan Modal -->
        <GitHubConnectModal
            :show="showGitHubModal"
            :project="project"
            :github-connection="githubConnection"
            @close="showGitHubModal = false"
            @scanned="handleScanned"
        />

        <!-- GitHub Export & Blueprint Branch Modal -->
        <GitHubExportModal
            v-if="currentAudit"
            :show="showExportModal"
            :project="project"
            :audit="currentAudit"
            @close="showExportModal = false"
        />
    </AppLayout>
</template>

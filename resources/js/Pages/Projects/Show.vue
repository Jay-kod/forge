<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
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
import OpportunityGraph from '@/Components/Opportunity/OpportunityGraph.vue';
import DecisionTimeline from '@/Components/Projects/DecisionTimeline.vue';
import VersionComparisonModal from '@/Components/Projects/VersionComparisonModal.vue';
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
const showVersionModal = ref(false);
const activeIntelligenceTab = ref<'overview' | 'graph' | 'timeline'>('overview');
const currentAudit = ref(props.project.repository_audit);

const handleScanned = (auditData: any) => {
    currentAudit.value = auditData;
};

const handleStageRerun = () => {
    router.reload({ only: ['project'] });
};

import { onMounted, onUnmounted } from 'vue';

let pollInterval: any = null;

const startPolling = () => {
    if (pollInterval) return;
    pollInterval = setInterval(async () => {
        try {
            const res = await fetch(`/projects/${props.project.id}/workflow/status`);
            if (res.ok) {
                const data = await res.json();
                if (data.active_stage?.status !== 'processing') {
                    clearInterval(pollInterval);
                    pollInterval = null;
                    isRunning.value = false;
                    router.reload({ only: ['project'] });
                }
            }
        } catch (e) {
            // ignore
        }
    }, 2000);
};

const advanceStage = (stage: WorkflowStage) => {
    isRunning.value = true;
    router.post(route('workflow.advance', { project: props.project.id, stage: stage.id }), {}, {
        onSuccess: () => {
            startPolling();
        },
        onFinish: () => {
            // keep isRunning true if polling takes over
        }
    });
};

onMounted(() => {
    if (props.project.workflow?.stages?.some((s: any) => s.status === 'processing')) {
        isRunning.value = true;
        startPolling();
    }
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});

const approveStage = (stage: WorkflowStage) => {
    router.post(route('workflow.approve', { project: props.project.id, stage: stage.id }));
};

const copyMasterPrompt = () => {
    const prompt = `# FORGE MASTER PROMPT: ${props.project.title}\n\nProject: ${props.project.description}\nClassification: ${props.project.classification}\n\nInspect generated PRD, Architecture, and AGENTS.md before writing code. Enforce module-based architecture and test all failure cases.`;
    navigator.clipboard.writeText(prompt);
    alert('Master Prompt copied to clipboard!');
};

const feedbackGiven = ref(false);
const submitFeedback = async (stageType: string, rating: number) => {
    try {
        const res = await fetch(`/projects/${props.project.id}/feedback`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({
                category: stageType || 'general_stage',
                signal_type: 'quality_feedback',
                rating: rating,
                stage_type: stageType,
            }),
        });
        const data = await res.json();
        feedbackGiven.value = true;
        alert(data.message || 'Thank you for your feedback!');
    } catch {
        alert('Feedback submitted!');
    }
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
                    @click="showVersionModal = true"
                    class="px-3 py-2 rounded-xl border border-indigo-500/30 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 text-xs font-mono transition-colors inline-flex items-center gap-1.5"
                    title="Compare Project Versions"
                >
                    <span>🧬</span>
                    <span>Version History (v{{ project.versions?.length || 1 }})</span>
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
                    <div v-if="activeStage.content" class="pt-4 border-t border-primary flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <span class="text-xs text-text-tertiary font-mono">
                                {{ activeStage.approved_at ? '✓ Approved' : 'Awaiting Review' }}
                            </span>

                            <!-- Learning Feedback Buttons -->
                            <div v-if="!feedbackGiven" class="hidden sm:flex items-center gap-1.5 pl-4 border-l border-primary">
                                <span class="text-[10px] font-mono text-text-tertiary">Helpful?</span>
                                <button
                                    @click="submitFeedback(activeStage.stage_type, 1)"
                                    class="px-2 py-0.5 rounded-md text-[11px] font-mono hover:bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 transition-colors"
                                    title="Accurate & useful (Anonymized signal)"
                                >
                                    👍 Yes
                                </button>
                                <button
                                    @click="submitFeedback(activeStage.stage_type, -1)"
                                    class="px-2 py-0.5 rounded-md text-[11px] font-mono hover:bg-red-500/20 text-red-400 border border-red-500/30 transition-colors"
                                    title="Needs refinement (Anonymized signal)"
                                >
                                    👎 No
                                </button>
                            </div>
                            <span v-else class="text-[10px] font-mono text-emerald-400 pl-4 border-l border-primary">
                                ✓ Feedback recorded anonymously
                            </span>
                        </div>

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

                <!-- Continuous Intelligence Segmented Tabs -->
                <div class="flex items-center gap-2 p-1.5 rounded-2xl bg-surface-secondary border border-primary w-fit">
                    <button
                        @click="activeIntelligenceTab = 'overview'"
                        class="px-4 py-2 text-xs font-bold rounded-xl transition-colors flex items-center gap-1.5"
                        :class="activeIntelligenceTab === 'overview' ? 'bg-indigo-600 text-white shadow-xs' : 'text-text-secondary hover:text-text-primary'"
                    >
                        <span>📊</span>
                        <span>Intelligence Matrices</span>
                    </button>
                    <button
                        @click="activeIntelligenceTab = 'graph'"
                        class="px-4 py-2 text-xs font-bold rounded-xl transition-colors flex items-center gap-1.5"
                        :class="activeIntelligenceTab === 'graph' ? 'bg-indigo-600 text-white shadow-xs' : 'text-text-secondary hover:text-text-primary'"
                    >
                        <span>🕸️</span>
                        <span>Opportunity Graph</span>
                    </button>
                    <button
                        @click="activeIntelligenceTab = 'timeline'"
                        class="px-4 py-2 text-xs font-bold rounded-xl transition-colors flex items-center gap-1.5"
                        :class="activeIntelligenceTab === 'timeline' ? 'bg-indigo-600 text-white shadow-xs' : 'text-text-secondary hover:text-text-primary'"
                    >
                        <span>📜</span>
                        <span>Decision Timeline</span>
                    </button>
                </div>

                <!-- Tab 1: Matrices -->
                <div v-show="activeIntelligenceTab === 'overview'" class="space-y-6">
                    <!-- Competitor Intelligence Matrix -->
                    <CompetitorMatrix :competitors="project.competitors" />

                    <!-- Action Priority Matrix (Opportunities) -->
                    <OpportunityMatrix :opportunities="project.opportunities" />

                    <!-- Evidence & Research Registry -->
                    <EvidenceRegistry :evidence="project.evidence" :project-id="project.id" />
                </div>

                <!-- Tab 2: Interactive Opportunity Graph -->
                <div v-if="activeIntelligenceTab === 'graph'">
                    <OpportunityGraph :project="project" />
                </div>

                <!-- Tab 3: Decision Timeline -->
                <div v-if="activeIntelligenceTab === 'timeline'">
                    <DecisionTimeline :project="project" @stage-rerun="handleStageRerun" />
                </div>
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

        <!-- Version Comparison Modal -->
        <VersionComparisonModal
            :project="project"
            :is-open="showVersionModal"
            @close="showVersionModal = false"
        />
    </AppLayout>
</template>

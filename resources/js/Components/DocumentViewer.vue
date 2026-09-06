<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';

interface Document {
    id?: number;
    title: string;
    type: string;
    content: string;
    version?: number;
    status?: string;
}

const props = defineProps<{
    documents?: Document[];
    projectTitle?: string;
}>();

const activeDocType = ref<string>('prd');
const copied = ref(false);

onMounted(() => {
    const handleDocHash = () => {
        const hash = window.location.hash.replace('#', '');
        if (['prd', 'architecture', 'hard-questions', 'testing', 'agents'].includes(hash)) {
            activeDocType.value = hash;
        }
    };
    handleDocHash();
    window.addEventListener('hashchange', handleDocHash);
});

const availableDocs = computed(() => {
    const baseDocs = props.documents && props.documents.length > 0
        ? [...props.documents]
        : [];

    const defaultTypes = [
        {
            title: 'Product Requirements Document (PRD)',
            type: 'prd',
            content: `# Product Requirements Document: ${props.projectTitle || 'Project'}\n\n## 1. Executive Summary\nSynthesized based on verified real-world market intelligence and strategic discovery verdict.\n\n## 2. Core Capabilities\n- Grounded evidence traceability\n- Multi-provider AI reasoning\n- Zero manual boilerplate overhead`,
        },
        {
            title: 'System Architecture Spec',
            type: 'architecture',
            content: `# System Architecture Specification\n\n## 1. Architecture Style\nModule-based Clean Architecture (Inertia.js + Laravel 12 + Vue 3).\n\n## 2. Data Flow\nThin controllers delegate to single-responsibility Actions. Concurrency-safe credit accounting with row-level locks.`,
        },
        {
            title: 'Hard Architectural Questions',
            type: 'hard-questions',
            content: `# Hard Architectural Questions & Edge Decisions\n\n## 1. What happens on AI provider outage?\nGraceful degradation: fallback from primary provider to secondary (Anthropic -> OpenAI -> Google), retry with exponential backoff, and full credit refund on persistent failure.\n\n## 2. How do we ensure atomic credit deduction?\nDatabase transactions wrapping row-level lock (SELECT FOR UPDATE) on credit_accounts. Reserve -> Execute -> Confirm/Release lifecycle.`,
        },
        {
            title: 'Testing Strategy & Coverage',
            type: 'testing',
            content: `# Testing Strategy & Verification Plan\n\n## 1. Multi-Layer Testing Architecture\n- Unit tests for isolated domain rules and actions\n- Feature tests for HTTP endpoints and authorization policies\n- E2E Playwright verification across user journeys\n\n## 2. Failure Case Verification\nAll rate limits, entitlement barriers, and credit refund flows must have dedicated failure-case tests.`,
        },
        {
            title: 'Agent Instructions (AGENTS.md)',
            type: 'agents',
            content: `# Agent Operating Instructions\n\n1. Always inspect PRD and Architecture before writing code.\n2. Cross-module boundaries strictly use service contracts.\n3. Test all failure and refund cases thoroughly.`,
        },
    ];

    for (const def of defaultTypes) {
        if (!baseDocs.some(d => d.type === def.type)) {
            baseDocs.push(def);
        }
    }

    return baseDocs;
});

const currentDoc = computed(() => {
    return availableDocs.value.find(d => d.type === activeDocType.value) || availableDocs.value[0];
});

const copyContent = () => {
    if (!currentDoc.value?.content) return;
    navigator.clipboard.writeText(currentDoc.value.content);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
};

const downloadDoc = () => {
    if (!currentDoc.value) return;
    const blob = new Blob([currentDoc.value.content], { type: 'text/markdown' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${currentDoc.value.type.toUpperCase()}.md`;
    a.click();
    URL.revokeObjectURL(url);
};
</script>

<template>
    <div class="bg-surface-secondary border border-primary rounded-2xl p-6 shadow-md">
        <!-- Header & Tabs -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-primary mb-6">
            <div>
                <span class="text-xs font-mono font-bold uppercase tracking-wider text-text-tertiary block mb-0.5">
                    Generated AI Specifications
                </span>
                <h3 class="text-lg font-display font-bold text-text-primary">
                    Document & Blueprint Viewer
                </h3>
            </div>

            <!-- Actions: Copy & Download -->
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    @click="copyContent"
                    class="px-3 py-1.5 rounded-lg border border-primary bg-surface-primary hover:bg-surface-tertiary text-text-secondary hover:text-text-primary text-xs font-mono transition-colors flex items-center gap-1.5"
                >
                    <span>{{ copied ? '✓ Copied' : '📋 Copy Content' }}</span>
                </button>
                <button
                    type="button"
                    @click="downloadDoc"
                    class="px-3 py-1.5 rounded-lg border border-primary bg-surface-primary hover:bg-surface-tertiary text-text-secondary hover:text-text-primary text-xs font-mono transition-colors flex items-center gap-1.5"
                >
                    <span>⬇ Download .md</span>
                </button>
            </div>
        </div>

        <!-- Document Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-2 mb-4">
            <button
                v-for="doc in availableDocs"
                :key="doc.type"
                type="button"
                @click="activeDocType = doc.type"
                class="px-3.5 py-1.5 rounded-xl text-xs font-mono font-semibold transition-all whitespace-nowrap"
                :class="activeDocType === doc.type ? 'bg-indigo-600 text-white shadow-xs' : 'bg-surface-primary border border-primary text-text-secondary hover:text-text-primary hover:bg-surface-tertiary'"
            >
                📄 {{ doc.type.toUpperCase() }}.md
            </button>
        </div>

        <!-- Document Content Body -->
        <div class="p-5 rounded-xl bg-surface-primary border border-primary text-xs leading-relaxed font-mono whitespace-pre-wrap text-text-primary overflow-x-auto max-h-[500px] select-text">
            {{ currentDoc?.content }}
        </div>
    </div>
</template>

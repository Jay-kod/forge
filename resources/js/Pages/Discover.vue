<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const form = useForm({
    prompt: '',
    website_url: '',
    mode: 'page_by_page',
});

const starters = [
    { title: 'New Business Idea', prompt: 'I have an idea for a business but I am not sure whether it is worth building or what competitors exist.' },
    { title: 'Website Audit & CRO', prompt: 'I want to audit and improve my existing website for higher conversion, SEO, and better UX.' },
    { title: 'Local Business Growth', prompt: 'I run a local laundry and dry cleaning business and want to expand operations into multiple neighborhoods.' },
    { title: 'Regional Expansion', prompt: 'I want to expand my B2B logistics SaaS platform into Lagos and West African markets.' },
    { title: 'GitHub Code Audit', prompt: 'I have a GitHub repository that needs an architectural audit, technical debt review, and modernization roadmap.' },
    { title: 'Process Automation', prompt: 'I want to automate repetitive manual client onboarding and invoice reconciliation in my company.' },
];

const applyStarter = (starterPrompt: string) => {
    form.prompt = starterPrompt;
};

const submitDiscovery = () => {
    if (!form.prompt.trim()) return;
    form.post(route('discover.submit'));
};
</script>

<template>
    <AppLayout>
        <Head title="Discover What's Possible — FORGE Intelligence" />

        <div class="max-w-4xl mx-auto space-y-8 py-4">
            <!-- Strategic Header -->
            <div class="text-center space-y-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-mono font-bold bg-indigo-500/10 border border-indigo-500/20 text-indigo-400">
                    <span>⚡</span>
                    <span>Continuous Intelligence Engine</span>
                </span>
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-display font-extrabold tracking-tight text-text-primary">
                    What are you trying to achieve?
                </h1>
                <p class="text-sm sm:text-base text-text-secondary max-w-xl mx-auto leading-relaxed">
                    Describe your concept, existing product, website, or business problem in plain language. FORGE synthesizes the context and classifies the opportunity.
                </p>
            </div>

            <!-- Main Interactive Form -->
            <form @submit.prevent="submitDiscovery" class="bg-surface-secondary border border-primary rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
                <!-- Large Natural Language Input Area -->
                <div class="space-y-2">
                    <label for="discovery-prompt" class="block text-xs font-mono uppercase tracking-wider text-text-tertiary font-bold">
                        Your Objective or Product Idea
                    </label>
                    <textarea
                        id="discovery-prompt"
                        v-model="form.prompt"
                        rows="5"
                        class="w-full rounded-2xl bg-surface-primary border border-primary p-4 text-sm text-text-primary placeholder:text-text-tertiary focus:outline-hidden focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors leading-relaxed"
                        placeholder="e.g. I run a physical boutique store and want to launch an online subscription box service, but need competitor research, market pricing validation, and an architecture blueprint..."
                        required
                    ></textarea>
                    <div v-if="form.errors.prompt" class="text-xs text-red-400 font-mono mt-1">
                        {{ form.errors.prompt }}
                    </div>
                </div>

                <!-- Secondary Context Inputs (Website URL) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="space-y-1.5">
                        <label for="website-url" class="block text-xs font-mono uppercase tracking-wider text-text-tertiary font-bold">
                            Live Website or Domain (Optional)
                        </label>
                        <input
                            id="website-url"
                            v-model="form.website_url"
                            type="url"
                            class="w-full rounded-xl bg-surface-primary border border-primary px-3.5 py-2.5 text-xs text-text-primary placeholder:text-text-tertiary focus:outline-hidden focus:border-indigo-500"
                            placeholder="https://yourcompany.com"
                        />
                        <span class="text-[10px] text-text-tertiary block font-mono">
                            If provided, FORGE analyzes SEO, conversion signals, and meta tags.
                        </span>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-xs font-mono uppercase tracking-wider text-text-tertiary font-bold">
                            Workflow Execution Mode
                        </label>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                type="button"
                                @click="form.mode = 'page_by_page'"
                                class="p-2.5 rounded-xl border text-xs font-medium transition-all text-left flex flex-col gap-0.5"
                                :class="form.mode === 'page_by_page' ? 'border-indigo-500 bg-indigo-500/10 text-indigo-400 font-semibold' : 'border-primary bg-surface-primary text-text-secondary'"
                            >
                                <span class="font-bold">Guided</span>
                                <span class="text-[10px] opacity-75">Stage by stage</span>
                            </button>
                            <button
                                type="button"
                                @click="form.mode = 'automatic'"
                                class="p-2.5 rounded-xl border text-xs font-medium transition-all text-left flex flex-col gap-0.5"
                                :class="form.mode === 'automatic' ? 'border-indigo-500 bg-indigo-500/10 text-indigo-400 font-semibold' : 'border-primary bg-surface-primary text-text-secondary'"
                            >
                                <span class="font-bold">Autonomous</span>
                                <span class="text-[10px] opacity-75">End-to-end</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-4 border-t border-primary flex items-center justify-between gap-4">
                    <div class="text-[11px] font-mono text-text-tertiary flex items-center gap-1.5">
                        <span>⚡</span>
                        <span>Estimated cost: 15 Credits for Initial Classification</span>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing || !form.prompt.trim()"
                        class="px-6 py-3 rounded-xl brand-button text-xs font-bold shadow-md disabled:opacity-50 inline-flex items-center gap-2 transition-all hover:scale-[1.02]"
                    >
                        <span v-if="form.processing">Synthesizing Context...</span>
                        <span v-else>Launch Intelligence &rarr;</span>
                    </button>
                </div>
            </form>

            <!-- Curated Starters Grid -->
            <div class="space-y-3">
                <div class="text-xs font-mono uppercase tracking-wider text-text-tertiary font-bold text-center">
                    Or select a common scenario to start
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <button
                        v-for="st in starters"
                        :key="st.title"
                        type="button"
                        @click="applyStarter(st.prompt)"
                        class="p-4 rounded-2xl bg-surface-secondary border border-primary hover:border-indigo-500/50 hover:bg-surface-tertiary transition-all text-left group"
                    >
                        <div class="text-xs font-bold text-text-primary group-hover:text-indigo-400 transition-colors flex items-center justify-between">
                            <span>{{ st.title }}</span>
                            <span class="text-text-tertiary opacity-0 group-hover:opacity-100 transition-opacity">&rarr;</span>
                        </div>
                        <p class="text-xs text-text-secondary line-clamp-2 mt-1.5 leading-relaxed">
                            {{ st.prompt }}
                        </p>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

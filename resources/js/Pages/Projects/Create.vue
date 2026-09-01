<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const form = useForm({
    goal: '',
    mode: 'page_by_page',
});

const examplePrompts = [
    { label: 'New Product', text: 'I want to build an app for university campus events and student ticket resale.' },
    { label: 'Business Growth', text: 'I run a local laundry and dry cleaning business and want to acquire more recurring customers.' },
    { label: 'Website Audit', text: 'My B2B SaaS website exists and gets traffic, but visitor signups are not converting.' },
    { label: 'Codebase Modernization', text: 'I have an existing PHP/Laravel repository that has accumulated technical debt and needs architectural improvement.' },
    { label: 'Market Expansion', text: 'I want to expand my digital payment and invoice collection service to Lagos, Nigeria.' },
    { label: 'Idea Validation', text: 'I have an idea for an AI meeting assistant for solo therapists, but I do not know if they will pay for it.' },
];

const applyExample = (text: string) => {
    form.goal = text;
};

const submit = () => {
    form.post(route('projects.store'));
};
</script>

<template>
    <AppLayout>
        <Head title="What are you trying to achieve? — FORGE" />

        <div class="max-w-3xl mx-auto py-6">
            <!-- Headline -->
            <div class="text-center mb-8">
                <span class="text-xs font-mono font-bold uppercase tracking-widest text-indigo-400 mb-2 block">
                    FORGE Intelligence Engine
                </span>
                <h1 class="text-3xl sm:text-4xl font-display font-extrabold tracking-tight text-text-primary mb-3">
                    WHAT ARE YOU TRYING TO ACHIEVE?
                </h1>
                <p class="text-sm text-text-secondary max-w-xl mx-auto">
                    Arrive with an idea, business, codebase, website, process, or simply uncertainty. FORGE will understand, research real-world evidence, and recommend the best next move.
                </p>
            </div>

            <!-- Main Input Form -->
            <form @submit.prevent="submit" class="bg-surface-secondary border border-primary rounded-2xl p-6 sm:p-8 shadow-xl">
                <!-- Textarea -->
                <div class="mb-6">
                    <label for="goal" class="block text-xs font-mono font-semibold uppercase text-text-tertiary mb-2">
                        Describe your situation or goal naturally
                    </label>
                    <textarea
                        id="goal"
                        v-model="form.goal"
                        rows="5"
                        class="w-full rounded-xl bg-surface-primary border border-primary focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 text-text-primary text-sm p-4 leading-relaxed transition-all placeholder:text-text-tertiary"
                        placeholder="e.g. I run a commercial cleaning business in Atlanta and want to automate our quoting and route scheduling..."
                        required
                    ></textarea>
                    <span v-if="form.errors.goal" class="text-xs text-red-400 mt-1 block">
                        {{ form.errors.goal }}
                    </span>
                </div>

                <!-- Example Pills -->
                <div class="mb-8">
                    <span class="text-[11px] font-mono text-text-tertiary block mb-2">
                        Or select a reference inquiry:
                    </span>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="example in examplePrompts"
                            :key="example.label"
                            type="button"
                            @click="applyExample(example.text)"
                            class="px-3 py-1.5 rounded-lg border border-primary bg-surface-tertiary hover:border-indigo-500/50 hover:bg-surface-elevated text-text-secondary hover:text-text-primary text-xs font-medium transition-all text-left"
                        >
                            <span class="font-semibold text-indigo-400">{{ example.label }}:</span>
                            <span class="ml-1 opacity-80 truncate">{{ example.text.slice(0, 40) }}...</span>
                        </button>
                    </div>
                </div>

                <!-- Workflow Execution Mode -->
                <div class="pt-6 border-t border-primary flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4 text-xs">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="radio"
                                v-model="form.mode"
                                value="page_by_page"
                                class="text-indigo-600 focus:ring-indigo-500 bg-surface-primary border-primary"
                            />
                            <span class="text-text-secondary">Page-by-Page Review (Recommended)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="radio"
                                v-model="form.mode"
                                value="automatic"
                                class="text-indigo-600 focus:ring-indigo-500 bg-surface-primary border-primary"
                            />
                            <span class="text-text-secondary">Automatic Blueprint</span>
                        </label>
                    </div>

                    <!-- Submit Action -->
                    <button
                        type="submit"
                        :disabled="form.processing || !form.goal.trim()"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl brand-button text-sm font-bold shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing">Analyzing Situation...</span>
                        <span v-else>Discover What's Possible →</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

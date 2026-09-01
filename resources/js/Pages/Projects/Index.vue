<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { Project } from '@/types';

defineProps<{
    projects: Project[];
}>();
</script>

<template>
    <AppLayout>
        <Head title="Workspaces — FORGE" />

        <!-- Dashboard Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl sm:text-3xl font-display font-bold tracking-tight text-text-primary">
                    Intelligence Workspaces
                </h1>
                <p class="text-sm text-text-secondary mt-1">
                    Every project is a living workspace backed by real-world evidence and ongoing opportunity discovery.
                </p>
            </div>

            <Link
                :href="route('projects.create')"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg brand-button text-sm font-semibold shadow-md shrink-0"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>What are you trying to achieve?</span>
            </Link>
        </div>

        <!-- Project Grid -->
        <div v-if="projects.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                v-for="project in projects"
                :key="project.id"
                class="flex flex-col bg-surface-secondary border border-primary hover:border-indigo-500/50 rounded-2xl p-6 transition-all duration-200 hover:shadow-lg group"
            >
                <!-- Card Header -->
                <div class="flex items-start justify-between gap-2 mb-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-mono font-medium bg-indigo-500/10 border border-indigo-500/30 text-indigo-400">
                        {{ project.classification }}
                    </span>
                    <span class="text-xs text-text-tertiary font-mono">
                        {{ new Date(project.created_at).toLocaleDateString() }}
                    </span>
                </div>

                <!-- Title & Description -->
                <h2 class="text-lg font-display font-bold text-text-primary group-hover:text-indigo-400 transition-colors line-clamp-1 mb-2">
                    {{ project.title }}
                </h2>
                <p class="text-xs text-text-secondary line-clamp-3 mb-6 flex-1 leading-relaxed">
                    {{ project.description }}
                </p>

                <!-- Footer Stats & CTA -->
                <div class="pt-4 border-t border-primary flex items-center justify-between mt-auto">
                    <div class="flex items-center gap-1.5 text-xs text-text-tertiary font-mono">
                        <span class="w-2 h-2 rounded-full" :class="project.status === 'completed' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                        <span class="capitalize">{{ project.status }}</span>
                    </div>

                    <Link
                        :href="route('projects.show', project.id)"
                        class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition-colors"
                    >
                        <span>Open Workspace</span>
                        <span>→</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-else class="text-center py-16 px-4 bg-surface-secondary border border-primary rounded-2xl">
            <div class="w-16 h-16 rounded-2xl bg-surface-tertiary border border-primary flex items-center justify-center mx-auto text-text-tertiary mb-4">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
            </div>
            <h2 class="text-lg font-display font-bold text-text-primary mb-1">No Active Workspaces Yet</h2>
            <p class="text-xs text-text-secondary max-w-sm mx-auto mb-6">
                Describe an idea, business, codebase, website, or problem to launch your first evidence-backed discovery loop.
            </p>
            <Link
                :href="route('projects.create')"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg brand-button text-xs font-semibold shadow-md"
            >
                Launch First Discovery
            </Link>
        </div>
    </AppLayout>
</template>

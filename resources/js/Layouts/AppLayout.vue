<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import type { SharedProps } from '@/types';

const page = usePage<SharedProps>();
const isDark = ref(true);

const toggleTheme = () => {
    isDark.value = !isDark.value;
    const theme = isDark.value ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('forge_theme', theme);
};

onMounted(() => {
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
    isDark.value = currentTheme === 'dark';
});

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-surface-primary text-text-primary flex flex-col font-sans transition-colors duration-200">
        <!-- Top Navigation Bar -->
        <header class="border-b border-primary bg-surface-secondary/80 backdrop-blur-md sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <!-- Brand Logo & Tagline -->
                <div class="flex items-center gap-6">
                    <Link :href="route('projects.index')" class="flex items-center gap-3 group">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-display font-bold text-lg shadow-md group-hover:bg-indigo-500 transition-colors">
                            F
                        </div>
                        <div class="flex flex-col">
                            <span class="font-display font-bold tracking-tight text-lg leading-none">FORGE</span>
                            <span class="text-[10px] tracking-wider uppercase text-text-tertiary font-mono mt-0.5">Intelligence Platform</span>
                        </div>
                    </Link>

                    <nav v-if="page.props.auth.user" class="hidden md:flex items-center gap-1 pl-4 border-l border-primary">
                        <Link
                            :href="route('projects.index')"
                            class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors"
                            :class="route().current('projects.*') ? 'bg-surface-elevated text-indigo-400 font-semibold shadow-xs' : 'text-text-secondary hover:text-text-primary hover:bg-surface-tertiary'"
                        >
                            Workspaces
                        </Link>
                        <Link
                            :href="route('pricing')"
                            class="px-3 py-1.5 rounded-md text-sm font-medium transition-colors"
                            :class="route().current('pricing') ? 'bg-surface-elevated text-indigo-400 font-semibold shadow-xs' : 'text-text-secondary hover:text-text-primary hover:bg-surface-tertiary'"
                        >
                            Plans & Credits
                        </Link>
                    </nav>
                </div>

                <!-- User Controls & Credit Balance -->
                <div class="flex items-center gap-3">
                    <!-- Credits Display -->
                    <div v-if="page.props.auth.user" class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-surface-tertiary border border-primary text-xs font-mono">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-text-secondary">Credits:</span>
                        <span class="font-semibold text-text-primary">{{ page.props.credits.balance }}</span>
                    </div>

                    <!-- Theme Toggle -->
                    <button
                        type="button"
                        @click="toggleTheme"
                        class="p-2 rounded-lg text-text-secondary hover:text-text-primary hover:bg-surface-tertiary transition-colors"
                        :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
                    >
                        <svg v-if="isDark" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg v-else class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>

                    <!-- Authenticated User Menu -->
                    <div v-if="page.props.auth.user" class="flex items-center gap-3 pl-2 border-l border-primary">
                        <div class="flex flex-col text-right hidden sm:block">
                            <span class="text-xs font-semibold leading-none">{{ page.props.auth.user.name }}</span>
                            <span class="text-[10px] text-text-tertiary mt-0.5 capitalize">{{ page.props.auth.user.role }}</span>
                        </div>
                        <button
                            @click="logout"
                            class="text-xs px-3 py-1.5 rounded-md border border-primary hover:bg-surface-tertiary text-text-secondary hover:text-text-primary transition-colors"
                        >
                            Sign out
                        </button>
                    </div>

                    <!-- Guest Menu -->
                    <div v-else class="flex items-center gap-2">
                        <Link :href="route('login')" class="text-sm font-medium px-3 py-1.5 rounded-md hover:bg-surface-tertiary transition-colors">
                            Sign in
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <div v-if="page.props.flash.success || page.props.flash.error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div v-if="page.props.flash.success" class="p-3 rounded-lg bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ page.props.flash.success }}</span>
            </div>
            <div v-if="page.props.flash.error" class="p-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 text-red-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>{{ page.props.flash.error }}</span>
            </div>
        </div>

        <!-- Main Workspace Content -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>

        <!-- Footer -->
        <footer class="border-t border-primary bg-surface-secondary py-6 mt-auto text-xs text-text-tertiary">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-text-secondary">FORGE</span>
                    <span>— Framework for Opportunity, Research, Growth & Execution</span>
                </div>
                <div class="flex items-center gap-4">
                    <span>Evidence-First Intelligence</span>
                    <span>•</span>
                    <Link :href="route('pricing')" class="hover:underline">Pricing</Link>
                </div>
            </div>
        </footer>
    </div>
</template>

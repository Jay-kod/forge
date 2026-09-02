<script setup lang="ts">
import { ref } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import ReferralModal from '@/Components/ReferralModal.vue';
import type { SharedProps } from '@/types';

const page = usePage<SharedProps>();
const isMobileMenuOpen = ref(false);
const isReferralOpen = ref(false);

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="min-h-screen bg-surface-primary text-text-primary flex flex-col font-sans transition-colors duration-200">
        <!-- Top Navigation Bar -->
        <header class="border-b border-primary bg-surface-secondary/90 backdrop-blur-md sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <!-- Brand Logo & Tagline -->
                <div class="flex items-center gap-6">
                    <Link :href="route('projects.index')" class="flex items-center gap-3 group">
                        <div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-display font-bold text-lg shadow-md group-hover:bg-indigo-500 transition-colors">
                            F
                        </div>
                        <div class="flex flex-col">
                            <span class="font-display font-bold tracking-tight text-lg leading-none text-text-primary">FORGE</span>
                            <span class="text-[10px] tracking-wider uppercase text-text-tertiary font-mono mt-0.5">Intelligence Platform</span>
                        </div>
                    </Link>

                    <!-- Desktop Nav Links -->
                    <nav v-if="page.props.auth.user" class="hidden md:flex items-center gap-1 pl-4 border-l border-primary">
                        <Link
                            :href="route('projects.index')"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
                            :class="route().current('projects.*') ? 'bg-surface-elevated text-indigo-400 font-semibold shadow-xs border border-primary' : 'text-text-secondary hover:text-text-primary hover:bg-surface-tertiary'"
                        >
                            Workspaces
                        </Link>
                        <Link
                            :href="route('pricing')"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
                            :class="route().current('pricing') ? 'bg-surface-elevated text-indigo-400 font-semibold shadow-xs border border-primary' : 'text-text-secondary hover:text-text-primary hover:bg-surface-tertiary'"
                        >
                            Plans & Credits
                        </Link>
                        <Link
                            v-if="page.props.auth.user?.role === 'admin'"
                            :href="route('admin.dashboard')"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors"
                            :class="route().current('admin.*') ? 'bg-amber-500/15 text-amber-400 font-semibold border border-amber-500/30' : 'text-amber-400/80 hover:text-amber-400 hover:bg-surface-tertiary'"
                        >
                            ⚡ Admin Operations
                        </Link>
                    </nav>
                </div>

                <!-- Right Side: Credits, Theme, User Controls -->
                <div class="flex items-center gap-3">
                    <!-- Credits Display -->
                    <div v-if="page.props.auth.user" class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-full bg-surface-tertiary border border-primary text-xs font-mono">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-text-tertiary">Credits:</span>
                        <span class="font-bold text-text-primary">⚡ {{ page.props.credits.balance }}</span>
                    </div>

                    <!-- Referral Invite Button -->
                    <button
                        v-if="page.props.auth.user"
                        type="button"
                        @click="isReferralOpen = true"
                        class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full border border-indigo-500/30 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 text-xs font-mono transition-colors"
                        title="Invite founders & earn 50 bonus credits"
                    >
                        <span>🎁 Invite & Earn</span>
                    </button>

                    <!-- Theme Toggle Component -->
                    <ThemeToggle />

                    <!-- Authenticated User Menu -->
                    <div v-if="page.props.auth.user" class="hidden sm:flex items-center gap-3 pl-2 border-l border-primary">
                        <div class="flex flex-col text-right">
                            <span class="text-xs font-bold text-text-primary leading-none">{{ page.props.auth.user.name }}</span>
                            <span class="text-[10px] font-mono text-text-tertiary mt-0.5 capitalize">{{ page.props.auth.user.role }}</span>
                        </div>
                        <button
                            @click="logout"
                            class="text-xs font-mono px-3 py-1.5 rounded-xl border border-primary bg-surface-secondary hover:bg-surface-tertiary text-text-secondary hover:text-text-primary transition-colors"
                        >
                            Sign out
                        </button>
                    </div>

                    <!-- Mobile Menu Hamburger Button -->
                    <button
                        v-if="page.props.auth.user"
                        type="button"
                        @click="isMobileMenuOpen = !isMobileMenuOpen"
                        class="md:hidden p-2 rounded-xl border border-primary bg-surface-secondary text-text-secondary"
                        aria-label="Toggle navigation menu"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path v-if="!isMobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Guest Menu -->
                    <div v-if="!page.props.auth.user" class="flex items-center gap-2">
                        <Link :href="route('login')" class="text-xs font-bold px-4 py-2 rounded-xl brand-button shadow-xs">
                            Sign in
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div v-if="isMobileMenuOpen && page.props.auth.user" class="md:hidden border-t border-primary bg-surface-secondary px-4 py-4 space-y-3">
                <div class="flex items-center justify-between pb-3 border-b border-primary">
                    <div>
                        <div class="font-bold text-xs text-text-primary">{{ page.props.auth.user.name }}</div>
                        <div class="text-[10px] text-text-tertiary font-mono">{{ page.props.auth.user.email }}</div>
                    </div>
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-surface-tertiary border border-primary text-xs font-mono">
                        <span class="text-emerald-400">⚡ {{ page.props.credits.balance }} credits</span>
                    </div>
                </div>

                <div class="space-y-1">
                    <Link
                        :href="route('projects.index')"
                        @click="isMobileMenuOpen = false"
                        class="block px-3 py-2 rounded-lg text-xs font-semibold text-text-primary hover:bg-surface-tertiary"
                    >
                        Workspaces
                    </Link>
                    <Link
                        :href="route('pricing')"
                        @click="isMobileMenuOpen = false"
                        class="block px-3 py-2 rounded-lg text-xs font-semibold text-text-primary hover:bg-surface-tertiary"
                    >
                        Plans & Credits
                    </Link>
                    <Link
                        v-if="page.props.auth.user?.role === 'admin'"
                        :href="route('admin.dashboard')"
                        @click="isMobileMenuOpen = false"
                        class="block px-3 py-2 rounded-lg text-xs font-semibold text-amber-400 hover:bg-surface-tertiary"
                    >
                        ⚡ Admin Operations
                    </Link>
                </div>

                <div class="pt-3 border-t border-primary">
                    <button
                        @click="logout"
                        class="w-full text-left px-3 py-2 rounded-lg text-xs text-red-400 hover:bg-surface-tertiary font-semibold"
                    >
                        Sign out
                    </button>
                </div>
            </div>
        </header>

        <!-- Flash Messages -->
        <div v-if="page.props.flash.success || page.props.flash.error" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div v-if="page.props.flash.success" class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ page.props.flash.success }}</span>
            </div>
            <div v-if="page.props.flash.error" class="p-3.5 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-xs flex items-center gap-2">
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
                    <span>&bull;</span>
                    <Link :href="route('pricing')" class="hover:underline">Pricing</Link>
                </div>
            </div>
        </footer>
        <ReferralModal
            :is-open="isReferralOpen"
            :referral-code="page.props.auth.user?.referral_code"
            @close="isReferralOpen = false"
        />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import Sidebar from '@/Components/Sidebar.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';
import ReferralModal from '@/Components/ReferralModal.vue';
import NotificationCenter from '@/Components/Notifications/NotificationCenter.vue';
import type { SharedProps } from '@/types';

const page = usePage<SharedProps>();
const isMobileMenuOpen = ref(false);
const isSidebarCollapsed = ref(false);
const isReferralOpen = ref(false);

onMounted(() => {
    const saved = localStorage.getItem('forge_sidebar_collapsed');
    if (saved !== null) {
        isSidebarCollapsed.value = saved === 'true';
    }
});

const toggleHamburger = () => {
    if (typeof window !== 'undefined' && window.innerWidth < 768) {
        isMobileMenuOpen.value = !isMobileMenuOpen.value;
    } else {
        isSidebarCollapsed.value = !isSidebarCollapsed.value;
        localStorage.setItem('forge_sidebar_collapsed', String(isSidebarCollapsed.value));
    }
};

const logout = () => {
    router.post('/logout');
};

const currentViewTitle = computed(() => {
    try {
        if (route().current('dashboard')) return { title: 'Overview', icon: '⚡', subtitle: 'Platform Intelligence & Status' };
        if (route().current('discover')) return { title: 'Discover', icon: '✨', subtitle: 'Intent-Driven Discovery Engine' };
        if (route().current('projects.index')) return { title: 'Projects', icon: '📁', subtitle: 'Living Project Workspaces' };
        if (route().current('projects.create')) return { title: 'Launch Discovery', icon: '🚀', subtitle: 'New Project Workspace' };
        if (route().current('projects.show')) return { title: 'Project Workspace', icon: '🎯', subtitle: 'Stage Execution & Evidence' };
        if (route().current('opportunities.*')) return { title: 'Opportunities', icon: '💡', subtitle: 'Cross-Project Opportunity Radar' };
        if (route().current('research.*')) return { title: 'Research & Sources', icon: '🔬', subtitle: 'Traceable Evidence & Audits' };
        if (route().current('growth.*')) return { title: 'Growth Center', icon: '📈', subtitle: 'Proactive Intelligence & Debt' };
        if (route().current('github.*')) return { title: 'GitHub Integration', icon: '🐙', subtitle: 'Repository Health & Actions' };
        if (route().current('notifications.*')) return { title: 'Notifications', icon: '🔔', subtitle: 'System & Opportunity Alerts' };
        if (route().current('exports.*')) return { title: 'Generated Artifacts', icon: '📦', subtitle: 'Blueprints, Packages & PDFs' };
        if (route().current('usage.*')) return { title: 'Usage & Capacity', icon: '⚡', subtitle: 'Credit Ledger & Workloads' };
        if (route().current('billing.*') || route().current('pricing')) return { title: 'Billing & Plans', icon: '💳', subtitle: 'Entitlements & Subscriptions' };
        if (route().current('settings.*')) return { title: 'Account Settings', icon: '⚙️', subtitle: 'Profile, Privacy & Credentials' };
        if (route().current('help.*')) return { title: 'Help & Documentation', icon: '📖', subtitle: 'Guides & Architecture FAQ' };
        if (route().current('audit-logs.*') || route().current('organizations.audit-logs.*')) return { title: 'Audit Logs', icon: '📜', subtitle: 'Governance & Security' };
        if (route().current('organizations.*')) return { title: 'Teams & Organizations', icon: '🏢', subtitle: 'Multi-Tenant Management' };
        if (route().current('api-keys.*') || route().current('byok.*')) return { title: 'API Keys & BYOK', icon: '🔑', subtitle: 'Credentials & Tokens' };
        if (route().current('privacy.*')) return { title: 'Privacy & Data', icon: '🔒', subtitle: 'GDPR & Portability' };
        if (route().current('admin.dashboard')) return { title: 'Admin Console', icon: '🛡️', subtitle: 'System Operations & KPI Radar' };
        if (route().current('admin.api-keys.*')) return { title: 'System API Keys', icon: '📡', subtitle: 'Connection Probes' };
    } catch {
        // fallback
    }
    return { title: 'FORGE Intelligence', icon: '⚡', subtitle: 'Continuous Product Discovery' };
});
</script>

<template>
    <div class="min-h-screen bg-surface-primary text-text-primary flex font-sans transition-colors duration-200">
        <!-- Dedicated Collapsible Sidebar for Authenticated Users -->
        <Sidebar
            v-if="page.props.auth.user"
            v-model:is-mobile-open="isMobileMenuOpen"
            v-model:is-collapsed="isSidebarCollapsed"
        />

        <!-- Main Viewport (Header + Scrollable Content + Footer) -->
        <div class="flex-1 flex flex-col min-w-0 min-h-screen">
            <!-- Top Sticky Navigation Header -->
            <header class="border-b border-primary bg-surface-secondary/85 backdrop-blur-md sticky top-0 z-30 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8">
                <!-- Left: Hamburger Button & Page View Breadcrumb -->
                <div class="flex items-center gap-3">
                    <!-- Hamburger Button (Active everywhere: mobile opens drawer, desktop toggles collapse) -->
                    <button
                        v-if="page.props.auth.user"
                        type="button"
                        @click="toggleHamburger"
                        class="p-2 rounded-xl border border-primary bg-surface-secondary text-text-secondary hover:text-text-primary hover:bg-surface-tertiary transition-colors shadow-xs"
                        title="Toggle sidebar (expand / collapse)"
                        aria-label="Toggle navigation sidebar"
                    >
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Guest Logo (when unauthenticated) -->
                    <Link v-if="!page.props.auth.user" :href="route('login')" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-display font-bold text-base shadow-sm">
                            F
                        </div>
                        <span class="font-display font-bold text-lg text-text-primary">FORGE</span>
                    </Link>

                    <!-- Active View Title & Category Subtitle -->
                    <div v-if="page.props.auth.user" class="flex items-center gap-2.5">
                        <span class="text-base">{{ currentViewTitle.icon }}</span>
                        <div class="flex flex-col">
                            <h1 class="text-sm font-display font-bold text-text-primary leading-tight">
                                {{ currentViewTitle.title }}
                            </h1>
                            <span class="text-[10px] font-mono text-text-tertiary leading-none">
                                {{ currentViewTitle.subtitle }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right: Credits Counter, Invite, Notifications, Theme, Profile -->
                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Credits Balance Badge -->
                    <div v-if="page.props.auth.user" class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-surface-tertiary border border-primary text-xs font-mono">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-text-tertiary hidden sm:inline">Credits:</span>
                        <span class="font-bold text-text-primary">⚡ {{ page.props.credits.balance }}</span>
                    </div>

                    <!-- Invite & Earn Button -->
                    <button
                        v-if="page.props.auth.user"
                        type="button"
                        @click="isReferralOpen = true"
                        class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full border border-indigo-500/30 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 text-xs font-mono transition-colors"
                        title="Invite founders & earn 50 bonus credits"
                    >
                        <span>🎁 Invite & Earn</span>
                    </button>

                    <!-- Notifications Dropdown -->
                    <NotificationCenter v-if="page.props.auth.user" />

                    <!-- Theme Toggle -->
                    <ThemeToggle />

                    <!-- User Profile Pill -->
                    <div v-if="page.props.auth.user" class="flex items-center gap-2.5 pl-2 border-l border-primary">
                        <div class="hidden md:flex flex-col text-right">
                            <span class="text-xs font-bold text-text-primary leading-none">{{ page.props.auth.user.name }}</span>
                            <span class="text-[10px] font-mono text-text-tertiary mt-0.5 capitalize">{{ page.props.auth.user.role }}</span>
                        </div>
                        <div class="w-8 h-8 rounded-lg bg-indigo-600/20 border border-indigo-500/30 text-indigo-300 flex items-center justify-center font-bold text-xs">
                            {{ page.props.auth.user.name.charAt(0).toUpperCase() }}
                        </div>
                    </div>

                    <!-- Guest Sign In CTA -->
                    <div v-else class="flex items-center gap-2">
                        <Link :href="route('login')" class="text-xs font-bold px-4 py-2 rounded-xl brand-button shadow-xs">
                            Sign in
                        </Link>
                    </div>
                </div>
            </header>

            <!-- Global Flash Messages -->
            <div v-if="page.props.flash.success || page.props.flash.error" class="w-full px-4 sm:px-6 lg:px-8 mt-4">
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

            <!-- Page Body Slot -->
            <main class="flex-1 w-full px-4 sm:px-6 lg:px-8 py-6 max-w-7xl mx-auto">
                <slot />
            </main>

            <!-- Compact Clean Footer -->
            <footer class="border-t border-primary bg-surface-secondary/50 py-4 px-4 sm:px-6 lg:px-8 mt-auto text-xs text-text-tertiary">
                <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-text-secondary">FORGE</span>
                        <span>— Framework for Opportunity, Research, Growth & Execution</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span>Evidence-First Intelligence</span>
                        <span>&bull;</span>
                        <Link :href="route('pricing')" class="hover:underline">Pricing & Plans</Link>
                    </div>
                </div>
            </footer>
        </div>

        <!-- Referral Modal -->
        <ReferralModal
            :is-open="isReferralOpen"
            :referral-code="page.props.auth.user?.referral_code"
            @close="isReferralOpen = false"
        />
    </div>
</template>

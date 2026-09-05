<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import type { SharedProps } from '@/types';

const props = defineProps<{
    isMobileOpen: boolean;
}>();

const emit = defineEmits<{
    (e: 'update:isMobileOpen', value: boolean): void;
    (e: 'toggleCollapse', value: boolean): void;
}>();

const page = usePage<SharedProps>();
const isCollapsed = ref(false);

onMounted(() => {
    const saved = localStorage.getItem('forge_sidebar_collapsed');
    if (saved !== null) {
        isCollapsed.value = saved === 'true';
    }
});

const toggleSidebar = () => {
    isCollapsed.value = !isCollapsed.value;
    localStorage.setItem('forge_sidebar_collapsed', String(isCollapsed.value));
    emit('toggleCollapse', isCollapsed.value);
};

const closeMobile = () => {
    emit('update:isMobileOpen', false);
};

const logout = () => {
    router.post('/logout');
};
</script>

<template>
    <!-- ================================================================= -->
    <!-- DESKTOP SIDEBAR (hidden on mobile, fixed/sticky on lg)            -->
    <!-- ================================================================= -->
    <aside
        class="hidden lg:flex flex-col h-screen sticky top-0 shrink-0 z-40 transition-all duration-300 ease-in-out border-r border-primary bg-surface-secondary select-none"
        :class="isCollapsed ? 'w-20' : 'w-64'"
    >
        <!-- Top macOS Window Controls & Hamburger Header -->
        <div class="p-4 flex items-center justify-between border-b border-primary/50">
            <!-- macOS Window Dots (Inspired by Reference Screenshot) -->
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-[#ff5f56] border border-[#e0443e]/30 shadow-xs inline-block"></span>
                <span class="w-3 h-3 rounded-full bg-[#ffbd2e] border border-[#dea123]/30 shadow-xs inline-block"></span>
                <span class="w-3 h-3 rounded-full bg-[#27c93f] border border-[#1aab29]/30 shadow-xs inline-block"></span>
            </div>

            <!-- Hamburger Button (Desktop Toggle) -->
            <button
                type="button"
                @click="toggleSidebar"
                class="p-1.5 rounded-lg text-text-secondary hover:text-text-primary hover:bg-surface-tertiary transition-colors"
                :title="isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                aria-label="Toggle sidebar collapse"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <!-- Brand Identity Section -->
        <div class="px-4 py-3.5 flex items-center gap-3 border-b border-primary/40 overflow-hidden">
            <Link :href="route('projects.index')" class="flex items-center gap-3 group shrink-0" :class="{ 'mx-auto': isCollapsed }">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-emerald-500 flex items-center justify-center text-white font-display font-extrabold text-base shadow-md group-hover:scale-105 transition-transform shrink-0">
                    F
                </div>
                <div v-if="!isCollapsed" class="flex flex-col whitespace-nowrap overflow-hidden transition-opacity duration-200">
                    <span class="font-display font-bold tracking-tight text-base text-text-primary leading-tight">FORGE</span>
                    <span class="text-[9px] uppercase tracking-widest text-emerald-400 font-mono font-medium">Product Intelligence</span>
                </div>
            </Link>
        </div>

        <!-- Navigation Links (Scrollable if needed) -->
        <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6 overflow-x-hidden custom-scrollbar">
            <!-- SECTION 1: CORE WORKSPACE -->
            <div>
                <div v-if="!isCollapsed" class="px-3 mb-2 text-[10px] uppercase font-mono tracking-wider text-text-tertiary font-semibold">
                    Workspace
                </div>
                <nav class="space-y-1">
                    <!-- Workspaces / Projects Index -->
                    <Link
                        :href="route('projects.index')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all group relative"
                        :class="route().current('projects.index')
                            ? 'bg-surface-elevated text-indigo-400 font-semibold shadow-xs border border-primary'
                            : 'text-text-secondary hover:text-text-primary hover:bg-surface-tertiary'"
                        :title="isCollapsed ? 'Workspaces' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">Workspaces</span>
                        <span v-if="isCollapsed" class="sr-only">Workspaces</span>
                    </Link>

                    <!-- New Discovery / Project -->
                    <Link
                        :href="route('projects.create')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all group relative"
                        :class="route().current('projects.create')
                            ? 'bg-surface-elevated text-emerald-400 font-semibold shadow-xs border border-primary'
                            : 'text-text-secondary hover:text-text-primary hover:bg-surface-tertiary'"
                        :title="isCollapsed ? 'Launch Discovery' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0 text-emerald-400 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4v16m8-8H4" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate font-semibold text-text-primary group-hover:text-emerald-400">Launch Discovery</span>
                        <span v-if="!isCollapsed" class="ml-auto text-[9px] font-mono px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">NEW</span>
                    </Link>

                    <!-- Teams & Organizations -->
                    <Link
                        :href="route('organizations.index')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all group relative"
                        :class="route().current('organizations.*')
                            ? 'bg-surface-elevated text-indigo-400 font-semibold shadow-xs border border-primary'
                            : 'text-text-secondary hover:text-text-primary hover:bg-surface-tertiary'"
                        :title="isCollapsed ? 'Teams & Organizations' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">Teams & Orgs</span>
                    </Link>

                    <!-- Activity & Audit Logs -->
                    <Link
                        :href="route('organizations.audit-logs')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all group relative"
                        :class="route().current('organizations.audit-logs')
                            ? 'bg-surface-elevated text-indigo-400 font-semibold shadow-xs border border-primary'
                            : 'text-text-secondary hover:text-text-primary hover:bg-surface-tertiary'"
                        :title="isCollapsed ? 'Activity Logs' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">Activity & Audits</span>
                    </Link>
                </nav>
            </div>

            <!-- SECTION 2: PLATFORM & INTEGRATIONS -->
            <div>
                <div v-if="!isCollapsed" class="px-3 mb-2 text-[10px] uppercase font-mono tracking-wider text-text-tertiary font-semibold">
                    Developer & Security
                </div>
                <nav class="space-y-1">
                    <!-- API Keys & BYOK -->
                    <Link
                        :href="route('api-keys.index')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all group relative"
                        :class="route().current('api-keys.*') || route().current('byok.*')
                            ? 'bg-surface-elevated text-indigo-400 font-semibold shadow-xs border border-primary'
                            : 'text-text-secondary hover:text-text-primary hover:bg-surface-tertiary'"
                        :title="isCollapsed ? 'API Keys & BYOK' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">API Keys & BYOK</span>
                    </Link>

                    <!-- Privacy & Compliance -->
                    <Link
                        :href="route('privacy.index')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all group relative"
                        :class="route().current('privacy.*')
                            ? 'bg-surface-elevated text-indigo-400 font-semibold shadow-xs border border-primary'
                            : 'text-text-secondary hover:text-text-primary hover:bg-surface-tertiary'"
                        :title="isCollapsed ? 'Privacy & Security' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">Privacy & Data</span>
                    </Link>

                    <!-- Plans & Credits -->
                    <Link
                        :href="route('pricing')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all group relative"
                        :class="route().current('pricing')
                            ? 'bg-surface-elevated text-indigo-400 font-semibold shadow-xs border border-primary'
                            : 'text-text-secondary hover:text-text-primary hover:bg-surface-tertiary'"
                        :title="isCollapsed ? 'Plans & Credits' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">Plans & Credits</span>
                    </Link>
                </nav>
            </div>

            <!-- SECTION 3: ADMIN CONSOLE (Superadmins Only) -->
            <div v-if="page.props.auth.user?.role === 'admin'">
                <div v-if="!isCollapsed" class="px-3 mb-2 text-[10px] uppercase font-mono tracking-wider text-amber-400/90 font-semibold">
                    Admin Operations
                </div>
                <nav class="space-y-1">
                    <Link
                        :href="route('admin.dashboard')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all group relative"
                        :class="route().current('admin.dashboard')
                            ? 'bg-amber-500/15 text-amber-400 font-semibold shadow-xs border border-amber-500/30'
                            : 'text-text-secondary hover:text-amber-400 hover:bg-surface-tertiary'"
                        :title="isCollapsed ? 'Admin Dashboard' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0 text-amber-400 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">Admin Console</span>
                    </Link>
                    <Link
                        :href="route('admin.api-keys.index')"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all group relative"
                        :class="route().current('admin.api-keys.*')
                            ? 'bg-amber-500/15 text-amber-400 font-semibold shadow-xs border border-amber-500/30'
                            : 'text-text-secondary hover:text-amber-400 hover:bg-surface-tertiary'"
                        :title="isCollapsed ? 'System Keys Probe' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0 text-amber-400 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                        </svg>
                        <span v-if="!isCollapsed" class="truncate">System Keys</span>
                    </Link>
                </nav>
            </div>
        </div>

        <!-- Bottom User Card & Quick Actions -->
        <div class="p-3 border-t border-primary/50 bg-surface-primary/60">
            <div v-if="!isCollapsed" class="flex items-center justify-between gap-2 p-2 rounded-xl bg-surface-secondary border border-primary/60">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600/20 border border-indigo-500/30 text-indigo-300 flex items-center justify-center font-bold text-xs shrink-0">
                        {{ page.props.auth.user?.name ? page.props.auth.user.name.charAt(0).toUpperCase() : 'U' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-bold text-text-primary truncate">{{ page.props.auth.user?.name }}</div>
                        <div class="text-[10px] font-mono text-emerald-400 flex items-center gap-1">
                            <span>⚡</span>
                            <span>{{ page.props.credits.balance }} credits</span>
                        </div>
                    </div>
                </div>
                <button
                    @click="logout"
                    class="p-1.5 rounded-lg text-text-tertiary hover:text-red-400 hover:bg-surface-tertiary transition-colors"
                    title="Sign out"
                    aria-label="Sign out"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </div>
            <div v-else class="flex flex-col items-center gap-2">
                <button
                    @click="logout"
                    class="w-10 h-10 rounded-xl bg-surface-secondary border border-primary/60 flex items-center justify-center text-text-secondary hover:text-red-400 hover:bg-surface-tertiary transition-colors"
                    title="Sign out"
                    aria-label="Sign out"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </div>
        </div>
    </aside>

    <!-- ================================================================= -->
    <!-- MOBILE OFF-CANVAS DRAWER                                          -->
    <!-- ================================================================= -->
    <div v-if="isMobileOpen" class="fixed inset-0 z-50 lg:hidden flex">
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
            @click="closeMobile"
        ></div>

        <!-- Slide-Over Content -->
        <div class="relative w-72 max-w-[80vw] h-full bg-surface-secondary border-r border-primary flex flex-col z-10 shadow-2xl">
            <!-- Header with macOS dots and Close button -->
            <div class="p-4 flex items-center justify-between border-b border-primary">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#ff5f56]"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-[#ffbd2e]"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-[#27c93f]"></span>
                    <span class="font-display font-bold text-sm text-text-primary ml-2">FORGE</span>
                </div>
                <button
                    type="button"
                    @click="closeMobile"
                    class="p-1.5 rounded-lg text-text-tertiary hover:text-text-primary hover:bg-surface-tertiary"
                    aria-label="Close navigation"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Mobile Navigation Links -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                <div class="space-y-1">
                    <div class="px-2 text-[10px] font-mono uppercase tracking-wider text-text-tertiary font-bold mb-1">
                        Workspace
                    </div>
                    <Link
                        :href="route('projects.index')"
                        @click="closeMobile"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold"
                        :class="route().current('projects.index') ? 'bg-surface-elevated text-indigo-400 border border-primary' : 'text-text-secondary hover:bg-surface-tertiary'"
                    >
                        <span>🏠</span>
                        <span>Workspaces</span>
                    </Link>
                    <Link
                        :href="route('projects.create')"
                        @click="closeMobile"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-emerald-400 hover:bg-surface-tertiary"
                    >
                        <span>✨</span>
                        <span>Launch Discovery</span>
                    </Link>
                    <Link
                        :href="route('organizations.index')"
                        @click="closeMobile"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold"
                        :class="route().current('organizations.*') ? 'bg-surface-elevated text-indigo-400 border border-primary' : 'text-text-secondary hover:bg-surface-tertiary'"
                    >
                        <span>🏢</span>
                        <span>Teams & Orgs</span>
                    </Link>
                    <Link
                        :href="route('organizations.audit-logs')"
                        @click="closeMobile"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold"
                        :class="route().current('organizations.audit-logs') ? 'bg-surface-elevated text-indigo-400 border border-primary' : 'text-text-secondary hover:bg-surface-tertiary'"
                    >
                        <span>📜</span>
                        <span>Activity & Audits</span>
                    </Link>
                </div>

                <div class="space-y-1 pt-2 border-t border-primary">
                    <div class="px-2 text-[10px] font-mono uppercase tracking-wider text-text-tertiary font-bold mb-1">
                        Integrations & Billing
                    </div>
                    <Link
                        :href="route('api-keys.index')"
                        @click="closeMobile"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold"
                        :class="route().current('api-keys.*') ? 'bg-surface-elevated text-indigo-400 border border-primary' : 'text-text-secondary hover:bg-surface-tertiary'"
                    >
                        <span>🔑</span>
                        <span>API Keys & BYOK</span>
                    </Link>
                    <Link
                        :href="route('privacy.index')"
                        @click="closeMobile"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold"
                        :class="route().current('privacy.*') ? 'bg-surface-elevated text-indigo-400 border border-primary' : 'text-text-secondary hover:bg-surface-tertiary'"
                    >
                        <span>🔒</span>
                        <span>Privacy & Security</span>
                    </Link>
                    <Link
                        :href="route('pricing')"
                        @click="closeMobile"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold"
                        :class="route().current('pricing') ? 'bg-surface-elevated text-indigo-400 border border-primary' : 'text-text-secondary hover:bg-surface-tertiary'"
                    >
                        <span>⚡</span>
                        <span>Plans & Credits</span>
                    </Link>
                </div>

                <div v-if="page.props.auth.user?.role === 'admin'" class="space-y-1 pt-2 border-t border-primary">
                    <div class="px-2 text-[10px] font-mono uppercase tracking-wider text-amber-400 font-bold mb-1">
                        Admin Operations
                    </div>
                    <Link
                        :href="route('admin.dashboard')"
                        @click="closeMobile"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-amber-400 hover:bg-surface-tertiary"
                    >
                        <span>⚡</span>
                        <span>Admin Console</span>
                    </Link>
                    <Link
                        :href="route('admin.api-keys.index')"
                        @click="closeMobile"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold text-amber-400 hover:bg-surface-tertiary"
                    >
                        <span>🔑</span>
                        <span>System Keys</span>
                    </Link>
                </div>
            </div>

            <!-- Mobile Drawer User Profile -->
            <div class="p-4 border-t border-primary bg-surface-tertiary/40">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <div class="font-bold text-xs text-text-primary">{{ page.props.auth.user?.name }}</div>
                        <div class="text-[10px] text-text-tertiary font-mono">{{ page.props.auth.user?.email }}</div>
                    </div>
                    <span class="text-xs font-mono font-bold text-emerald-400">⚡ {{ page.props.credits.balance }}</span>
                </div>
                <button
                    @click="logout"
                    class="w-full py-2 px-3 rounded-xl border border-red-500/30 bg-red-500/10 text-red-400 font-semibold text-xs text-center"
                >
                    Sign out
                </button>
            </div>
        </div>
    </div>
</template>

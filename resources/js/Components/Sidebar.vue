<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import type { SharedProps } from '@/types';

const props = withDefaults(defineProps<{
    isMobileOpen: boolean;
    isCollapsed?: boolean;
}>(), {
    isCollapsed: false,
});

const emit = defineEmits<{
    (e: 'update:isMobileOpen', value: boolean): void;
    (e: 'update:isCollapsed', value: boolean): void;
}>();

const page = usePage<SharedProps & { project?: any }>();
const collapsed = ref(props.isCollapsed);

onMounted(() => {
    const saved = localStorage.getItem('forge_sidebar_collapsed');
    if (saved !== null) {
        collapsed.value = saved === 'true';
        emit('update:isCollapsed', collapsed.value);
    }
});

watch(() => props.isCollapsed, (val) => {
    collapsed.value = val;
});

const toggleSidebar = () => {
    collapsed.value = !collapsed.value;
    localStorage.setItem('forge_sidebar_collapsed', String(collapsed.value));
    emit('update:isCollapsed', collapsed.value);
};

const closeMobile = () => {
    emit('update:isMobileOpen', false);
};

const logout = () => {
    router.post('/logout');
};

// Navigation Context Mode Detection
const isAdminRoute = computed(() => {
    try {
        return route().current('admin.*');
    } catch {
        return false;
    }
});

const isProjectRoute = computed(() => {
    try {
        return route().current('projects.show') ||
            route().current('projects.graph') ||
            route().current('projects.timeline') ||
            route().current('projects.versions.*');
    } catch {
        return false;
    }
});

const currentProject = computed(() => {
    return page.props.project || null;
});

// Level 1: Global Navigation Items
const primaryNav = [
    { name: 'Overview', routeName: 'dashboard', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { name: 'Discover', routeName: 'discover', icon: 'M13 10V3L4 14h7v7l9-11h-7z', badge: 'AI' },
    { name: 'Projects', routeName: 'projects.index', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
    { name: 'Opportunities', routeName: 'opportunities.index', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
    { name: 'Research', routeName: 'research.index', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
    { name: 'Growth', routeName: 'growth.index', icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' },
    { name: 'GitHub', routeName: 'github.index', icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4' },
];

const secondaryNav = [
    { name: 'Notifications', routeName: 'notifications.index', icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' },
    { name: 'Exports', routeName: 'exports.index', icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4' },
];

const accountNav = [
    { name: 'Usage & Credits', routeName: 'usage.index', icon: 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z' },
    { name: 'Billing & Plan', routeName: 'billing.index', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
    { name: 'Settings', routeName: 'settings.index', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z' },
    { name: 'Help & Docs', routeName: 'help.index', icon: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
];

// Level 2: Project Workspace Sub-Navigation Items
const projectNav = [
    { name: 'Overview', anchor: '#overview', icon: 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z' },
    { name: 'Understanding', anchor: '#understanding', icon: 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z' },
    { name: 'Discovery', anchor: '#discovery', icon: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z' },
    { name: 'Research & Evidence', anchor: '#research', icon: 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z' },
    { name: 'Competitors', anchor: '#competitors', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z' },
    { name: 'Strategy', anchor: '#strategy', icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
    { name: 'Opportunity Graph', anchor: '#graph', icon: 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z' },
    { name: 'Recommendations', anchor: '#recommendations', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { name: 'PRD', anchor: '#prd', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { name: 'Architecture', anchor: '#architecture', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
    { name: 'Hard Questions', anchor: '#hard-questions', icon: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { name: 'Testing Strategy', anchor: '#testing', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
    { name: 'GitHub Integration', anchor: '#github', icon: 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4' },
    { name: 'Exports & Artifacts', anchor: '#exports', icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4' },
    { name: 'Activity & Timeline', anchor: '#timeline', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
];

// Level 3: Admin Command Center Navigation
const adminNav = [
    { section: 'OVERVIEW', items: [
        { name: 'Admin Overview', routeName: 'admin.dashboard', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
        { name: 'Users & Roles', routeName: 'admin.dashboard', icon: 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z' },
    ]},
    { section: 'REVENUE', items: [
        { name: 'Plans & Billing', routeName: 'billing.index', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
        { name: 'Credits & Usage', routeName: 'usage.index', icon: 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z' },
    ]},
    { section: 'INTELLIGENCE', items: [
        { name: 'AI Providers & Keys', routeName: 'admin.api-keys.index', icon: 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z' },
        { name: 'Opportunities Radar', routeName: 'opportunities.index', icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z' },
        { name: 'Research Evidence', routeName: 'research.index', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
    ]},
    { section: 'PLATFORM', items: [
        { name: 'Organizations & Teams', routeName: 'organizations.index', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
        { name: 'Export Artifacts', routeName: 'exports.index', icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4' },
    ]},
    { section: 'SECURITY', items: [
        { name: 'Audit Logs', routeName: 'audit-logs.index', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
        { name: 'Privacy Governance', routeName: 'privacy.index', icon: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z' },
    ]},
    { section: 'SYSTEM', items: [
        { name: 'Connection Probes', routeName: 'admin.api-keys.index', icon: 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z' },
    ]},
];

const scrollToAnchor = (anchor: string) => {
    closeMobile();
    window.location.hash = anchor;
    window.dispatchEvent(new HashChangeEvent('hashchange'));
    setTimeout(() => {
        const targetId = anchor.startsWith('#') ? anchor : `#${anchor}`;
        let el = document.querySelector(targetId);
        if (!el && ['#prd', '#architecture', '#hard-questions', '#testing'].includes(targetId)) {
            el = document.querySelector('#documents');
        }
        if (el) {
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, 60);
};
</script>

<template>
    <!-- ================================================================= -->
    <!-- DESKTOP / TABLET COLLAPSIBLE RAIL SIDEBAR                         -->
    <!-- ================================================================= -->
    <aside
        class="hidden md:flex flex-col h-screen sticky top-0 shrink-0 z-40 transition-all duration-300 ease-in-out border-r border-primary bg-surface-secondary select-none"
        :class="collapsed ? 'w-20' : 'w-64'"
    >
        <!-- Brand / Context Header -->
        <div class="h-16 px-4 flex items-center justify-between border-b border-primary/50 overflow-hidden shrink-0">
            <!-- Mode 1: Global Brand -->
            <Link
                v-if="!isProjectRoute && !isAdminRoute"
                :href="route('dashboard')"
                class="flex items-center gap-3 group shrink-0"
                :class="{ 'mx-auto': collapsed }"
            >
                <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-display font-extrabold text-base shadow-sm group-hover:scale-105 transition-transform shrink-0">
                    F
                </div>
                <div v-if="!collapsed" class="flex flex-col whitespace-nowrap overflow-hidden">
                    <span class="font-display font-bold tracking-tight text-base text-text-primary leading-tight">FORGE</span>
                    <span class="text-[9px] uppercase tracking-widest text-emerald-500 dark:text-emerald-400 font-mono font-medium">DISCOVER WHAT'S POSSIBLE</span>
                </div>
            </Link>

            <!-- Mode 2: Project Workspace Context Header -->
            <div v-else-if="isProjectRoute" class="flex items-center gap-2.5 w-full overflow-hidden">
                <Link
                    :href="route('projects.index')"
                    class="p-2 rounded-xl border border-primary hover:bg-surface-tertiary text-text-secondary hover:text-text-primary transition-colors shrink-0"
                    title="Return to Projects Catalog"
                    aria-label="Back to projects"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <div v-if="!collapsed" class="min-w-0 flex-1">
                    <div class="text-[10px] uppercase font-mono tracking-wider text-indigo-400 font-bold leading-none truncate">
                        Workspace
                    </div>
                    <div class="text-xs font-display font-bold text-text-primary truncate mt-0.5" :title="currentProject?.title">
                        {{ currentProject?.title || 'Active Project' }}
                    </div>
                </div>
            </div>

            <!-- Mode 3: Admin Console Context Header -->
            <div v-else class="flex items-center gap-2.5 w-full overflow-hidden">
                <Link
                    :href="route('dashboard')"
                    class="p-2 rounded-xl border border-amber-500/30 bg-amber-500/10 text-amber-400 hover:bg-amber-500/20 transition-colors shrink-0"
                    title="Exit to User Dashboard"
                    aria-label="Exit admin"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 0118 0z" />
                    </svg>
                </Link>
                <div v-if="!collapsed" class="min-w-0 flex-1">
                    <span class="text-[10px] uppercase font-mono tracking-wider text-amber-400 font-bold block leading-none">
                        Ops Center
                    </span>
                    <span class="text-xs font-display font-bold text-text-primary truncate block mt-0.5">
                        Admin Console
                    </span>
                </div>
            </div>
        </div>

        <!-- Scrollable Navigation Area -->
        <div class="flex-1 overflow-y-auto px-2.5 py-3 space-y-4 overflow-x-hidden custom-scrollbar">
            <!-- ========================================================= -->
            <!-- LEVEL 2: PROJECT WORKSPACE SUB-NAVIGATION                 -->
            <!-- ========================================================= -->
            <div v-if="isProjectRoute" class="space-y-1">
                <div v-if="!collapsed" class="px-3 mb-2 text-[10px] uppercase font-mono tracking-wider text-text-tertiary font-semibold flex items-center justify-between">
                    <span>Project Sections</span>
                    <span class="text-[9px] px-1.5 py-0.2 rounded bg-indigo-500/10 text-indigo-400 font-mono">
                        {{ currentProject?.status || 'Active' }}
                    </span>
                </div>
                <nav class="space-y-1">
                    <button
                        v-for="item in projectNav"
                        :key="item.name"
                        @click="scrollToAnchor(item.anchor)"
                        class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all group relative text-left text-text-secondary hover:text-text-primary hover:bg-surface-tertiary"
                        :title="collapsed ? item.name : undefined"
                    >
                        <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="item.icon" />
                        </svg>
                        <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
                    </button>
                </nav>
            </div>

            <!-- ========================================================= -->
            <!-- LEVEL 3: ADMIN COMMAND CENTER NAVIGATION                  -->
            <!-- ========================================================= -->
            <div v-else-if="isAdminRoute" class="space-y-4">
                <div v-for="sec in adminNav" :key="sec.section">
                    <div v-if="!collapsed" class="px-3 mb-1.5 text-[10px] uppercase font-mono tracking-wider text-amber-400/90 font-bold">
                        {{ sec.section }}
                    </div>
                    <nav class="space-y-1">
                        <Link
                            v-for="item in sec.items"
                            :key="item.name"
                            :href="route(item.routeName)"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all group relative"
                            :class="route().current(item.routeName)
                                ? 'bg-amber-500/15 text-amber-400 font-semibold border border-amber-500/30'
                                : 'text-text-secondary hover:text-amber-300 hover:bg-surface-tertiary'"
                            :title="collapsed ? item.name : undefined"
                        >
                            <svg class="w-4 h-4 shrink-0 text-amber-400/80 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="item.icon" />
                            </svg>
                            <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
                        </Link>
                    </nav>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- LEVEL 1: GLOBAL USER NAVIGATION                           -->
            <!-- ========================================================= -->
            <template v-else>
                <!-- Primary Global Navigation -->
                <div>
                    <div v-if="!collapsed" class="px-3 mb-1.5 text-[10px] uppercase font-mono tracking-wider text-text-tertiary font-semibold">
                        Platform
                    </div>
                    <nav class="space-y-1">
                        <Link
                            v-for="item in primaryNav"
                            :key="item.name"
                            :href="route(item.routeName)"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-medium transition-all group relative"
                            :class="route().current(item.routeName)
                                ? 'bg-surface-elevated text-indigo-400 font-semibold shadow-xs border border-primary'
                                : 'text-text-secondary hover:text-text-primary hover:bg-surface-tertiary'"
                            :title="collapsed ? item.name : undefined"
                        >
                            <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="item.icon" />
                            </svg>
                            <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
                            <span
                                v-if="!collapsed && item.badge"
                                class="ml-auto text-[9px] font-mono px-1.5 py-0.5 rounded bg-indigo-500/15 text-indigo-400 border border-indigo-500/30"
                            >
                                {{ item.badge }}
                            </span>
                        </Link>
                    </nav>
                </div>

                <!-- Secondary Global Navigation -->
                <div class="pt-2 border-t border-primary/40">
                    <div v-if="!collapsed" class="px-3 mb-1.5 text-[10px] uppercase font-mono tracking-wider text-text-tertiary font-semibold">
                        Workspace Artifacts
                    </div>
                    <nav class="space-y-1">
                        <Link
                            v-for="item in secondaryNav"
                            :key="item.name"
                            :href="route(item.routeName)"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all group relative"
                            :class="route().current(item.routeName)
                                ? 'bg-surface-elevated text-indigo-400 font-semibold shadow-xs border border-primary'
                                : 'text-text-secondary hover:text-text-primary hover:bg-surface-tertiary'"
                            :title="collapsed ? item.name : undefined"
                        >
                            <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="item.icon" />
                            </svg>
                            <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
                        </Link>
                    </nav>
                </div>

                <!-- Account & Settings Navigation -->
                <div class="pt-2 border-t border-primary/40">
                    <div v-if="!collapsed" class="px-3 mb-1.5 text-[10px] uppercase font-mono tracking-wider text-text-tertiary font-semibold">
                        Account
                    </div>
                    <nav class="space-y-1">
                        <Link
                            v-for="item in accountNav"
                            :key="item.name"
                            :href="route(item.routeName)"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all group relative"
                            :class="route().current(item.routeName)
                                ? 'bg-surface-elevated text-indigo-400 font-semibold shadow-xs border border-primary'
                                : 'text-text-secondary hover:text-text-primary hover:bg-surface-tertiary'"
                            :title="collapsed ? item.name : undefined"
                        >
                            <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="item.icon" />
                            </svg>
                            <span v-if="!collapsed" class="truncate">{{ item.name }}</span>
                        </Link>
                    </nav>
                </div>

                <!-- Admin Quick Switch (For Admin Roles Only) -->
                <div v-if="page.props.auth.user?.role === 'admin'" class="pt-2 border-t border-primary/40">
                    <Link
                        :href="route('admin.dashboard')"
                        class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium transition-all group text-amber-400/90 hover:text-amber-300 hover:bg-amber-500/10 border border-amber-500/20"
                        :title="collapsed ? 'Admin Console' : undefined"
                    >
                        <svg class="w-4 h-4 shrink-0 text-amber-400 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span v-if="!collapsed" class="truncate font-semibold">Admin Console</span>
                    </Link>
                </div>
            </template>
        </div>

        <!-- Bottom User Card / Logout Section -->
        <div class="p-3 border-t border-primary/50 bg-surface-primary/40 shrink-0">
            <div v-if="!collapsed" class="flex items-center justify-between gap-2 p-2 rounded-xl bg-surface-secondary border border-primary/60">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600/20 border border-indigo-500/30 text-indigo-400 flex items-center justify-center font-bold text-xs shrink-0">
                        {{ page.props.auth.user?.name ? page.props.auth.user.name.charAt(0).toUpperCase() : 'U' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-bold text-text-primary truncate">{{ page.props.auth.user?.name }}</div>
                        <div class="text-[10px] font-mono text-emerald-500 dark:text-emerald-400 flex items-center gap-1">
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
    <!-- MOBILE OFF-CANVAS DRAWER (< md)                                   -->
    <!-- ================================================================= -->
    <div v-if="isMobileOpen" class="fixed inset-0 z-50 md:hidden flex">
        <!-- Backdrop -->
        <div
            class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
            @click="closeMobile"
        ></div>

        <!-- Slide-Over Drawer -->
        <div class="relative w-72 max-w-[80vw] h-full bg-surface-secondary border-r border-primary flex flex-col z-10 shadow-2xl">
            <!-- Header -->
            <div class="h-16 px-4 flex items-center justify-between border-b border-primary shrink-0">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-display font-extrabold text-sm shadow-sm">
                        F
                    </div>
                    <span class="font-display font-bold text-sm text-text-primary tracking-tight">FORGE</span>
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

            <!-- Drawer Links -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                <!-- Project Sub-Nav (when on project page) -->
                <div v-if="isProjectRoute" class="space-y-1">
                    <div class="px-2 text-[10px] font-mono uppercase tracking-wider text-text-tertiary font-bold mb-1">
                        Project Navigation
                    </div>
                    <button
                        v-for="item in projectNav"
                        :key="item.name"
                        @click="scrollToAnchor(item.anchor)"
                        class="w-full text-left px-3 py-2 rounded-xl text-xs font-semibold text-text-secondary hover:text-text-primary hover:bg-surface-tertiary flex items-center gap-2.5"
                    >
                        <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="item.icon" />
                        </svg>
                        <span>{{ item.name }}</span>
                    </button>
                    <div class="pt-2">
                        <Link
                            :href="route('projects.index')"
                            @click="closeMobile"
                            class="text-xs text-indigo-400 font-mono px-3 py-1.5 block hover:underline"
                        >
                            &larr; All Projects
                        </Link>
                    </div>
                </div>

                <!-- Global Nav -->
                <template v-else>
                    <div class="space-y-1">
                        <div class="px-2 text-[10px] font-mono uppercase tracking-wider text-text-tertiary font-bold mb-1">
                            Platform
                        </div>
                        <Link
                            v-for="item in primaryNav"
                            :key="item.name"
                            :href="route(item.routeName)"
                            @click="closeMobile"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-semibold"
                            :class="route().current(item.routeName) ? 'bg-surface-elevated text-indigo-400 border border-primary' : 'text-text-secondary hover:bg-surface-tertiary'"
                        >
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="item.icon" />
                            </svg>
                            <span>{{ item.name }}</span>
                        </Link>
                    </div>

                    <div class="space-y-1 pt-2 border-t border-primary">
                        <div class="px-2 text-[10px] font-mono uppercase tracking-wider text-text-tertiary font-bold mb-1">
                            Artifacts & Account
                        </div>
                        <Link
                            v-for="item in [...secondaryNav, ...accountNav]"
                            :key="item.name"
                            :href="route(item.routeName)"
                            @click="closeMobile"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-text-secondary hover:bg-surface-tertiary"
                        >
                            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" :d="item.icon" />
                            </svg>
                            <span>{{ item.name }}</span>
                        </Link>
                    </div>

                    <div v-if="page.props.auth.user?.role === 'admin'" class="space-y-1 pt-2 border-t border-primary">
                        <div class="px-2 text-[10px] font-mono uppercase tracking-wider text-amber-400 font-bold mb-1">
                            Admin Operations
                        </div>
                        <Link
                            :href="route('admin.dashboard')"
                            @click="closeMobile"
                            class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-amber-400 hover:bg-surface-tertiary"
                        >
                            <span>🛡️</span>
                            <span>Admin Console</span>
                        </Link>
                    </div>
                </template>
            </div>

            <!-- Bottom User Profile in Drawer -->
            <div class="p-4 border-t border-primary bg-surface-tertiary/40">
                <div class="flex items-center justify-between mb-3">
                    <div class="min-w-0 pr-2">
                        <div class="font-bold text-xs text-text-primary truncate">{{ page.props.auth.user?.name }}</div>
                        <div class="text-[10px] text-text-tertiary font-mono truncate">{{ page.props.auth.user?.email }}</div>
                    </div>
                    <span class="text-xs font-mono font-bold text-emerald-500 dark:text-emerald-400 shrink-0">⚡ {{ page.props.credits.balance }}</span>
                </div>
                <button
                    @click="logout"
                    class="w-full py-2 px-3 rounded-xl border border-red-500/30 bg-red-500/10 text-red-400 font-semibold text-xs text-center hover:bg-red-500/20 transition-colors"
                >
                    Sign out
                </button>
            </div>
        </div>
    </div>
</template>

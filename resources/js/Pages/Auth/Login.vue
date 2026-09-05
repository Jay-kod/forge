<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const isSignUp = ref(false);
const showPassword = ref(false);
const isLoading = ref(false);
const activePersona = ref<'founder' | 'developer' | 'admin'>('founder');

// Sign In Form
const loginForm = useForm({
    email: '',
    password: '',
    remember: true,
});

// Sign Up Form
const registerForm = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    technical_level: 'vibe_coder',
});

const submitLogin = () => {
    loginForm.post('/login', {
        onStart: () => { isLoading.value = true; },
        onFinish: () => { isLoading.value = false; },
    });
};

const submitRegister = () => {
    registerForm.post('/register', {
        onStart: () => { isLoading.value = true; },
        onFinish: () => { isLoading.value = false; },
    });
};

const triggerPersonaLogin = (persona: 'founder' | 'developer' | 'admin') => {
    activePersona.value = persona;
    isLoading.value = true;
    router.post('/demo-login', { persona }, {
        onFinish: () => { isLoading.value = false; },
    });
};

const personas = {
    founder: {
        id: 'founder' as const,
        name: 'Adaeze Founder',
        email: 'founder@forge.local',
        roleBadge: 'Solo Founder & Vibe Coder',
        icon: '🚀',
        color: 'indigo',
        description: 'Builds with natural language AI. Pre-loaded with 200 Pro credits, active product discovery matrix & PRD generator.',
    },
    developer: {
        id: 'developer' as const,
        name: 'Liam Engineer',
        email: 'developer@forge.local',
        roleBadge: 'Senior Lead Engineer',
        icon: '💻',
        color: 'emerald',
        description: 'Technical builder running codebase audits, digital roadmaps, security reviews & direct GitHub export (AGENTS.md).',
    },
    admin: {
        id: 'admin' as const,
        name: 'Forge Administrator',
        email: 'admin@forge.local',
        roleBadge: 'Platform Superadmin',
        icon: '⚡',
        color: 'amber',
        description: 'Superadmin console: live latency probes for Anthropic/OpenAI/Gemini, system API keys & organization audit logs.',
    },
};
</script>

<template>
    <Head :title="isSignUp ? 'Sign Up Account — FORGE' : 'Sign In Account — FORGE'" />

    <!-- Viewport Container: Strictly 100vh / 99vh on desktop, zero scrollbar -->
    <div class="h-screen max-h-[100dvh] w-screen overflow-hidden bg-[#07090e] text-[#f0f1f3] flex flex-col lg:flex-row font-sans select-none">

        <!-- ================================================================= -->
        <!-- LEFT PANEL: Hero Branding, Ambient Mesh Glow & Bottom Process Cards-->
        <!-- ================================================================= -->
        <div class="hidden lg:flex lg:w-1/2 xl:w-[50%] h-full flex-col justify-between p-8 xl:p-12 relative overflow-hidden bg-[#06120c] border-r border-[#15231a]">
            <!-- Ambient Radial Glow Mesh (Inspired directly by the reference screenshot) -->
            <div
                class="absolute inset-0 pointer-events-none"
                style="background: radial-gradient(circle at 82% 22%, rgba(16, 185, 129, 0.32) 0%, transparent 52%), radial-gradient(circle at 18% 78%, rgba(99, 102, 241, 0.15) 0%, transparent 48%), radial-gradient(circle at 50% 50%, rgba(4, 120, 87, 0.14) 0%, transparent 60%);"
            ></div>
            <div class="absolute top-0 right-0 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Top Brand Header -->
            <div class="relative z-10 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-400 to-indigo-600 flex items-center justify-center text-white font-display font-extrabold text-lg shadow-lg shadow-emerald-500/20">
                    F
                </div>
                <div>
                    <span class="font-display font-bold text-lg tracking-tight text-white">FORGE</span>
                    <span class="block text-[9px] uppercase font-mono tracking-widest text-emerald-400/90 font-medium">Evidence-First Intelligence</span>
                </div>
            </div>

            <!-- Center Headline Copy -->
            <div class="relative z-10 my-auto py-4 max-w-lg">
                <div class="inline-flex items-center gap-2 px-3 py-0.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-[11px] font-mono mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Continuous Product Intelligence</span>
                </div>
                <h1 class="text-3xl xl:text-4xl 2xl:text-5xl font-display font-bold tracking-tight text-white leading-tight">
                    Get Started <br />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-300 via-teal-100 to-indigo-300">
                        with Us
                    </span>
                </h1>
                <p class="mt-3 text-sm xl:text-base text-emerald-100/70 leading-relaxed font-normal">
                    Complete these easy steps to access your workspace and uncover high-conviction product opportunities.
                </p>
            </div>

            <!-- Bottom 3 Process Step Cards (Matching User's Screenshot) -->
            <div class="relative z-10 grid grid-cols-3 gap-3 pt-2">
                <!-- Card 1 (Active Highlighted White Card) -->
                <div class="bg-white text-gray-950 rounded-2xl p-4 shadow-xl flex flex-col justify-between h-28 transform transition hover:-translate-y-0.5">
                    <div class="w-6 h-6 rounded-full bg-gray-950 text-white font-bold text-[11px] flex items-center justify-center">
                        1
                    </div>
                    <div>
                        <div class="font-bold text-xs xl:text-sm text-gray-950 leading-tight">
                            {{ isSignUp ? 'Sign up your account' : 'Sign in your account' }}
                        </div>
                        <div class="text-[10px] text-gray-500 mt-0.5 leading-tight">
                            Instant access
                        </div>
                    </div>
                </div>

                <!-- Card 2 (Glass Translucent Card) -->
                <div class="bg-emerald-950/40 border border-emerald-500/20 backdrop-blur-md rounded-2xl p-4 text-emerald-100 flex flex-col justify-between h-28 transform transition hover:-translate-y-0.5">
                    <div class="w-6 h-6 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 font-bold text-[11px] flex items-center justify-center">
                        2
                    </div>
                    <div>
                        <div class="font-bold text-xs xl:text-sm text-white leading-tight">
                            Set up your workspace
                        </div>
                        <div class="text-[10px] text-emerald-200/60 mt-0.5 leading-tight">
                            Evidence & research
                        </div>
                    </div>
                </div>

                <!-- Card 3 (Glass Translucent Card) -->
                <div class="bg-emerald-950/40 border border-emerald-500/20 backdrop-blur-md rounded-2xl p-4 text-emerald-100 flex flex-col justify-between h-28 transform transition hover:-translate-y-0.5">
                    <div class="w-6 h-6 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 font-bold text-[11px] flex items-center justify-center">
                        3
                    </div>
                    <div>
                        <div class="font-bold text-xs xl:text-sm text-white leading-tight">
                            Set up your profile
                        </div>
                        <div class="text-[10px] text-emerald-200/60 mt-0.5 leading-tight">
                            PRDs & GitHub sync
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- MOBILE HEADER (< lg)                                              -->
        <!-- ================================================================= -->
        <div class="lg:hidden w-full px-5 py-3.5 bg-gradient-to-b from-[#091a13] to-[#07090e] border-b border-[#1a2820] flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-indigo-600 flex items-center justify-center text-white font-display font-extrabold text-base shadow-sm">
                    F
                </div>
                <div>
                    <span class="font-display font-bold text-base text-white">FORGE</span>
                    <span class="block text-[8px] uppercase font-mono text-emerald-400">Intelligence</span>
                </div>
            </div>

            <!-- Mode Switcher -->
            <div class="flex items-center bg-[#131722] border border-[#232838] rounded-lg p-0.5 text-xs font-medium">
                <button
                    type="button"
                    @click="isSignUp = false"
                    class="px-2.5 py-1 rounded-md transition-all text-xs"
                    :class="!isSignUp ? 'bg-emerald-500/20 text-emerald-300 font-bold' : 'text-gray-400'"
                >
                    Sign In
                </button>
                <button
                    type="button"
                    @click="isSignUp = true"
                    class="px-2.5 py-1 rounded-md transition-all text-xs"
                    :class="isSignUp ? 'bg-emerald-500/20 text-emerald-300 font-bold' : 'text-gray-400'"
                >
                    Sign Up
                </button>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- RIGHT PANEL: Perfectly Centered, Non-Scrollable (Fit in 99vh/100vh)-->
        <!-- ================================================================= -->
        <div class="w-full lg:w-1/2 xl:w-[50%] h-full flex flex-col justify-center items-center px-6 sm:px-10 lg:px-12 py-4 overflow-y-auto lg:overflow-hidden bg-[#07090e]">
            <div class="w-full max-w-[380px] xl:max-w-[400px] flex flex-col justify-center my-auto space-y-3.5">

                <!-- Header & Mode Switcher -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl font-display font-bold text-white tracking-tight">
                            {{ isSignUp ? 'Sign Up Account' : 'Sign In Account' }}
                        </h2>

                        <!-- Desktop Toggle Pills -->
                        <div class="hidden sm:flex items-center bg-[#131722] border border-[#232838] rounded-xl p-0.5 text-xs font-medium">
                            <button
                                type="button"
                                @click="isSignUp = false"
                                class="px-3 py-1 rounded-lg transition-all"
                                :class="!isSignUp ? 'bg-white text-gray-950 font-bold shadow-xs' : 'text-gray-400 hover:text-white'"
                            >
                                Sign In
                            </button>
                            <button
                                type="button"
                                @click="isSignUp = true"
                                class="px-3 py-1 rounded-lg transition-all"
                                :class="isSignUp ? 'bg-white text-gray-950 font-bold shadow-xs' : 'text-gray-400 hover:text-white'"
                            >
                                Sign Up
                            </button>
                        </div>
                    </div>
                    <p class="text-xs text-gray-400">
                        {{ isSignUp ? 'Enter your personal data to create your account.' : 'Enter your personal data to access your account.' }}
                    </p>
                </div>

                <!-- Social Authentication Buttons (Google & GitHub Side-by-Side) -->
                <div class="grid grid-cols-2 gap-2.5">
                    <!-- Google -->
                    <a
                        :href="route('auth.redirect', 'google')"
                        class="flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl border border-[#222737] bg-[#12151f] hover:bg-[#181c2b] text-white text-xs font-semibold transition-all shadow-xs group"
                    >
                        <svg class="w-3.5 h-3.5 shrink-0 transition-transform group-hover:scale-110" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z" />
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" />
                        </svg>
                        <span>Google</span>
                    </a>

                    <!-- GitHub -->
                    <a
                        :href="route('auth.redirect', 'github')"
                        class="flex items-center justify-center gap-2 px-3.5 py-2.5 rounded-xl border border-[#222737] bg-[#12151f] hover:bg-[#181c2b] text-white text-xs font-semibold transition-all shadow-xs group"
                    >
                        <svg class="w-3.5 h-3.5 shrink-0 fill-current text-gray-200 transition-transform group-hover:scale-110" viewBox="0 0 24 24">
                            <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z" />
                        </svg>
                        <span>Github</span>
                    </a>
                </div>

                <!-- Divider -->
                <div class="relative flex items-center justify-center my-0.5">
                    <div class="w-full border-t border-[#1a1f2e]"></div>
                    <span class="absolute px-2.5 bg-[#07090e] text-[10px] font-mono text-gray-500 uppercase tracking-wider">Or</span>
                </div>

                <!-- ============================================================= -->
                <!-- SIGN IN FORM                                                  -->
                <!-- ============================================================= -->
                <form v-if="!isSignUp" @submit.prevent="submitLogin" class="space-y-2.5">
                    <!-- Email -->
                    <div class="space-y-1">
                        <label class="block text-[11px] font-semibold text-gray-300">Email</label>
                        <input
                            v-model="loginForm.email"
                            type="email"
                            required
                            placeholder="eg. founder@forge.local"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#111420] border border-[#232838] text-white placeholder:text-gray-500 text-xs focus:outline-none focus:border-emerald-500/80 focus:ring-2 focus:ring-emerald-500/20 transition-colors"
                        />
                        <p v-if="loginForm.errors.email" class="text-[10px] text-red-400 font-mono">{{ loginForm.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div class="space-y-1">
                        <div class="flex items-center justify-between">
                            <label class="block text-[11px] font-semibold text-gray-300">Password</label>
                            <a href="#" class="text-[11px] text-emerald-400 hover:text-emerald-300 transition-colors">Forgot password?</a>
                        </div>
                        <div class="relative">
                            <input
                                v-model="loginForm.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                placeholder="Enter your password"
                                class="w-full px-3.5 py-2.5 pr-10 rounded-xl bg-[#111420] border border-[#232838] text-white placeholder:text-gray-500 text-xs focus:outline-none focus:border-emerald-500/80 focus:ring-2 focus:ring-emerald-500/20 transition-colors"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-200 transition-colors p-0.5"
                                aria-label="Toggle password visibility"
                            >
                                <svg v-if="!showPassword" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="loginForm.errors.password" class="text-[10px] text-red-400 font-mono">{{ loginForm.errors.password }}</p>
                    </div>

                    <!-- White Submit Button (Matching Mockup) -->
                    <button
                        type="submit"
                        :disabled="isLoading || loginForm.processing"
                        class="w-full py-2.5 px-4 rounded-xl bg-white text-gray-950 font-bold text-xs hover:bg-gray-100 active:scale-[0.99] shadow-md transition-all disabled:opacity-50 flex items-center justify-center gap-2 mt-1"
                    >
                        <span v-if="loginForm.processing" class="w-3.5 h-3.5 border-2 border-gray-950 border-t-transparent rounded-full animate-spin"></span>
                        <span>Sign In</span>
                    </button>
                </form>

                <!-- ============================================================= -->
                <!-- SIGN UP FORM                                                  -->
                <!-- ============================================================= -->
                <form v-else @submit.prevent="submitRegister" class="space-y-2.5">
                    <!-- First & Last Name (2 Column Grid) -->
                    <div class="grid grid-cols-2 gap-2.5">
                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold text-gray-300">First Name</label>
                            <input
                                v-model="registerForm.first_name"
                                type="text"
                                required
                                placeholder="eg. John"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-[#111420] border border-[#232838] text-white placeholder:text-gray-500 text-xs focus:outline-none focus:border-emerald-500/80 focus:ring-2 focus:ring-emerald-500/20 transition-colors"
                            />
                        </div>
                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold text-gray-300">Last Name</label>
                            <input
                                v-model="registerForm.last_name"
                                type="text"
                                placeholder="eg. Francisco"
                                class="w-full px-3.5 py-2.5 rounded-xl bg-[#111420] border border-[#232838] text-white placeholder:text-gray-500 text-xs focus:outline-none focus:border-emerald-500/80 focus:ring-2 focus:ring-emerald-500/20 transition-colors"
                            />
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="space-y-1">
                        <label class="block text-[11px] font-semibold text-gray-300">Email</label>
                        <input
                            v-model="registerForm.email"
                            type="email"
                            required
                            placeholder="eg. johnfrans@gmail.com"
                            class="w-full px-3.5 py-2.5 rounded-xl bg-[#111420] border border-[#232838] text-white placeholder:text-gray-500 text-xs focus:outline-none focus:border-emerald-500/80 focus:ring-2 focus:ring-emerald-500/20 transition-colors"
                        />
                        <p v-if="registerForm.errors.email" class="text-[10px] text-red-400 font-mono">{{ registerForm.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div class="space-y-1">
                        <label class="block text-[11px] font-semibold text-gray-300">Password</label>
                        <div class="relative">
                            <input
                                v-model="registerForm.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                placeholder="Enter your password"
                                class="w-full px-3.5 py-2.5 pr-10 rounded-xl bg-[#111420] border border-[#232838] text-white placeholder:text-gray-500 text-xs focus:outline-none focus:border-emerald-500/80 focus:ring-2 focus:ring-emerald-500/20 transition-colors"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-200 transition-colors p-0.5"
                                aria-label="Toggle password visibility"
                            >
                                <svg v-if="!showPassword" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-[10px] text-gray-500 font-mono">Must be at least 8 characters.</p>
                    </div>

                    <!-- White Submit Button -->
                    <button
                        type="submit"
                        :disabled="isLoading || registerForm.processing"
                        class="w-full py-2.5 px-4 rounded-xl bg-white text-gray-950 font-bold text-xs hover:bg-gray-100 active:scale-[0.99] shadow-md transition-all disabled:opacity-50 flex items-center justify-center gap-2 mt-1"
                    >
                        <span v-if="registerForm.processing" class="w-3.5 h-3.5 border-2 border-gray-950 border-t-transparent rounded-full animate-spin"></span>
                        <span>Sign Up</span>
                    </button>
                </form>

                <!-- ============================================================= -->
                <!-- COMPACT PERSONA SHOWCASE: 3 PILLS WITH ACTIVE USER DESCRIPTION-->
                <!-- ============================================================= -->
                <div class="pt-3 border-t border-[#181d2a] space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-mono uppercase tracking-wider text-emerald-400 font-bold flex items-center gap-1">
                            <span>⚡</span>
                            <span>Demo Personas (1-Click Test)</span>
                        </span>
                        <span class="text-[9px] font-mono text-gray-500">Instant Access</span>
                    </div>

                    <!-- 3 Horizontal Persona Selector Buttons -->
                    <div class="grid grid-cols-3 gap-1.5">
                        <!-- Founder -->
                        <button
                            type="button"
                            @click="triggerPersonaLogin('founder')"
                            @mouseenter="activePersona = 'founder'"
                            class="p-2 rounded-xl border text-left transition-all relative group"
                            :class="activePersona === 'founder'
                                ? 'bg-indigo-500/15 border-indigo-400/50 shadow-xs'
                                : 'bg-[#111420] border-[#222636] hover:border-indigo-500/30'"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-sm">🚀</span>
                                <span class="text-[9px] font-mono text-indigo-400 font-semibold group-hover:underline">Login →</span>
                            </div>
                            <div class="font-bold text-[11px] text-white mt-1 leading-tight">Founder</div>
                            <div class="text-[9px] text-gray-400 font-mono truncate">Adaeze</div>
                        </button>

                        <!-- Developer -->
                        <button
                            type="button"
                            @click="triggerPersonaLogin('developer')"
                            @mouseenter="activePersona = 'developer'"
                            class="p-2 rounded-xl border text-left transition-all relative group"
                            :class="activePersona === 'developer'
                                ? 'bg-emerald-500/15 border-emerald-400/50 shadow-xs'
                                : 'bg-[#111420] border-[#222636] hover:border-emerald-500/30'"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-sm">💻</span>
                                <span class="text-[9px] font-mono text-emerald-400 font-semibold group-hover:underline">Login →</span>
                            </div>
                            <div class="font-bold text-[11px] text-white mt-1 leading-tight">Engineer</div>
                            <div class="text-[9px] text-gray-400 font-mono truncate">Liam</div>
                        </button>

                        <!-- Superadmin -->
                        <button
                            type="button"
                            @click="triggerPersonaLogin('admin')"
                            @mouseenter="activePersona = 'admin'"
                            class="p-2 rounded-xl border text-left transition-all relative group"
                            :class="activePersona === 'admin'
                                ? 'bg-amber-500/15 border-amber-400/50 shadow-xs'
                                : 'bg-[#111420] border-[#222636] hover:border-amber-500/30'"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-sm">⚡</span>
                                <span class="text-[9px] font-mono text-amber-400 font-semibold group-hover:underline">Login →</span>
                            </div>
                            <div class="font-bold text-[11px] text-white mt-1 leading-tight">Superadmin</div>
                            <div class="text-[9px] text-gray-400 font-mono truncate">Admin</div>
                        </button>
                    </div>

                    <!-- Active Persona Description Box (Dynamic & Compact) -->
                    <div class="p-2.5 rounded-xl bg-[#0f121d] border border-[#202535] text-[11px] leading-relaxed text-gray-300">
                        <div class="flex items-center gap-1.5 font-bold text-white mb-0.5">
                            <span>{{ personas[activePersona].icon }}</span>
                            <span>{{ personas[activePersona].name }}</span>
                            <span class="text-[9px] font-mono px-1.5 py-0.2 rounded-full border"
                                :class="activePersona === 'founder'
                                    ? 'bg-indigo-500/20 text-indigo-300 border-indigo-500/30'
                                    : activePersona === 'developer'
                                        ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30'
                                        : 'bg-amber-500/20 text-amber-300 border-amber-500/30'"
                            >
                                {{ personas[activePersona].roleBadge }}
                            </span>
                        </div>
                        <p class="text-gray-400 text-[10px] leading-snug">
                            {{ personas[activePersona].description }}
                        </p>
                    </div>
                </div>

                <!-- Footer Switcher Link -->
                <div class="text-center pt-1">
                    <p class="text-[11px] text-gray-400">
                        {{ isSignUp ? 'Already have an account?' : "Don't have an account?" }}
                        <button
                            type="button"
                            @click="isSignUp = !isSignUp"
                            class="text-white font-bold hover:text-emerald-400 transition-colors underline underline-offset-4 ml-1"
                        >
                            {{ isSignUp ? 'Log in' : 'Sign up' }}
                        </button>
                    </p>
                </div>

            </div>
        </div>

    </div>
</template>

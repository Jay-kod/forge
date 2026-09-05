<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';

const isSignUp = ref(false);
const showPassword = ref(false);
const isLoading = ref(false);

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
    isLoading.value = true;
    router.post('/demo-login', { persona }, {
        onFinish: () => { isLoading.value = false; },
    });
};
</script>

<template>
    <Head :title="isSignUp ? 'Sign Up Account — FORGE' : 'Sign In Account — FORGE'" />

    <div class="min-h-screen bg-[#07090e] text-[#f0f1f3] flex flex-col lg:flex-row font-sans selection:bg-emerald-500/30 selection:text-emerald-300">

        <!-- ================================================================= -->
        <!-- LEFT PANEL: Hero Branding, Ambient Mesh Glow & Process Step Cards -->
        <!-- ================================================================= -->
        <div class="hidden lg:flex lg:w-1/2 flex-col justify-between p-10 xl:p-16 relative overflow-hidden bg-[#07140e] border-r border-[#1a2820]">
            <!-- Ambient Glow Mesh Gradients -->
            <div
                class="absolute inset-0 pointer-events-none"
                style="background: radial-gradient(circle at 85% 25%, rgba(16, 185, 129, 0.28) 0%, transparent 55%), radial-gradient(circle at 20% 75%, rgba(99, 102, 241, 0.16) 0%, transparent 50%), radial-gradient(circle at 50% 50%, rgba(5, 150, 105, 0.12) 0%, transparent 65%);"
            ></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute bottom-10 left-10 w-80 h-80 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Top Brand Header -->
            <div class="relative z-10 flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-indigo-600 flex items-center justify-center text-white font-display font-extrabold text-xl shadow-lg shadow-emerald-500/20">
                    F
                </div>
                <div>
                    <span class="font-display font-bold text-xl tracking-tight text-white">FORGE</span>
                    <span class="block text-[10px] uppercase font-mono tracking-widest text-emerald-400/90 font-medium">Evidence-First Intelligence</span>
                </div>
            </div>

            <!-- Center Headline Copy -->
            <div class="relative z-10 my-auto py-12 max-w-lg">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/25 text-emerald-300 text-xs font-mono mb-6">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Intelligent Product & SaaS Platform</span>
                </div>
                <h1 class="text-4xl xl:text-5xl font-display font-bold tracking-tight text-white leading-tight">
                    Get Started <br />
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-300 via-teal-200 to-indigo-300">
                        with Us
                    </span>
                </h1>
                <p class="mt-4 text-base xl:text-lg text-emerald-100/70 leading-relaxed font-normal">
                    Complete these easy steps to access your workspace and uncover high-conviction product opportunities before writing a single line of code.
                </p>
            </div>

            <!-- Bottom 3 Process Step Cards (Matching Screenshot) -->
            <div class="relative z-10 grid grid-cols-3 gap-3.5 pt-6">
                <!-- Card 1 (Active Highlighted) -->
                <div class="bg-white text-gray-900 rounded-2xl p-4 xl:p-5 shadow-2xl transform transition hover:-translate-y-0.5">
                    <div class="w-7 h-7 rounded-full bg-gray-950 text-white font-bold text-xs flex items-center justify-center mb-3">
                        1
                    </div>
                    <div class="font-bold text-xs xl:text-sm text-gray-950 leading-snug">
                        {{ isSignUp ? 'Sign up your account' : 'Sign in your account' }}
                    </div>
                    <div class="text-[10px] xl:text-[11px] text-gray-500 mt-1 leading-tight">
                        Instant secure access
                    </div>
                </div>

                <!-- Card 2 (Glass Translucent) -->
                <div class="bg-emerald-950/40 border border-emerald-500/20 backdrop-blur-md rounded-2xl p-4 xl:p-5 text-emerald-100 transform transition hover:-translate-y-0.5">
                    <div class="w-7 h-7 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 font-bold text-xs flex items-center justify-center mb-3">
                        2
                    </div>
                    <div class="font-bold text-xs xl:text-sm text-white leading-snug">
                        Set up your workspace
                    </div>
                    <div class="text-[10px] xl:text-[11px] text-emerald-200/60 mt-1 leading-tight">
                        Evidence & research
                    </div>
                </div>

                <!-- Card 3 (Glass Translucent) -->
                <div class="bg-emerald-950/40 border border-emerald-500/20 backdrop-blur-md rounded-2xl p-4 xl:p-5 text-emerald-100 transform transition hover:-translate-y-0.5">
                    <div class="w-7 h-7 rounded-full bg-emerald-500/20 border border-emerald-400/40 text-emerald-300 font-bold text-xs flex items-center justify-center mb-3">
                        3
                    </div>
                    <div class="font-bold text-xs xl:text-sm text-white leading-snug">
                        Build & export
                    </div>
                    <div class="text-[10px] xl:text-[11px] text-emerald-200/60 mt-1 leading-tight">
                        PRDs & GitHub sync
                    </div>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- MOBILE HEADER: Compact Branded Aura (< lg)                         -->
        <!-- ================================================================= -->
        <div class="lg:hidden w-full p-6 bg-gradient-to-b from-[#091a13] to-[#07090e] border-b border-[#1a2820] relative overflow-hidden">
            <div
                class="absolute inset-0 pointer-events-none"
                style="background: radial-gradient(circle at 80% 20%, rgba(16, 185, 129, 0.2) 0%, transparent 60%);"
            ></div>
            <div class="relative z-10 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-400 to-indigo-600 flex items-center justify-center text-white font-display font-extrabold text-lg shadow-md">
                        F
                    </div>
                    <div>
                        <span class="font-display font-bold text-lg text-white">FORGE</span>
                        <span class="block text-[9px] uppercase font-mono tracking-wider text-emerald-400">Intelligence Platform</span>
                    </div>
                </div>

                <!-- Mode Switcher -->
                <div class="flex items-center bg-[#131722] border border-[#232838] rounded-xl p-0.5 text-xs font-medium">
                    <button
                        type="button"
                        @click="isSignUp = false"
                        class="px-3 py-1.5 rounded-lg transition-all"
                        :class="!isSignUp ? 'bg-emerald-500/20 text-emerald-300 font-bold' : 'text-gray-400 hover:text-white'"
                    >
                        Sign In
                    </button>
                    <button
                        type="button"
                        @click="isSignUp = true"
                        class="px-3 py-1.5 rounded-lg transition-all"
                        :class="isSignUp ? 'bg-emerald-500/20 text-emerald-300 font-bold' : 'text-gray-400 hover:text-white'"
                    >
                        Sign Up
                    </button>
                </div>
            </div>

            <!-- Mobile Steps Pills -->
            <div class="relative z-10 grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-emerald-950/60">
                <div class="flex items-center gap-1.5 text-[10px] font-mono text-emerald-300">
                    <span class="w-4 h-4 rounded-full bg-white text-gray-950 font-bold flex items-center justify-center text-[9px]">1</span>
                    <span class="truncate">{{ isSignUp ? 'Sign up' : 'Sign in' }}</span>
                </div>
                <div class="flex items-center gap-1.5 text-[10px] font-mono text-gray-400">
                    <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center text-[9px]">2</span>
                    <span class="truncate">Workspace</span>
                </div>
                <div class="flex items-center gap-1.5 text-[10px] font-mono text-gray-400">
                    <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 font-bold flex items-center justify-center text-[9px]">3</span>
                    <span class="truncate">Roadmap</span>
                </div>
            </div>
        </div>

        <!-- ================================================================= -->
        <!-- RIGHT PANEL: Auth Forms & Comprehensive Persona Descriptions      -->
        <!-- ================================================================= -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-10 xl:p-16 min-h-screen overflow-y-auto">
            <div class="max-w-md w-full space-y-7 py-6">

                <!-- Header & Mode Switcher -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h2 class="text-2xl sm:text-3xl font-display font-bold text-white tracking-tight">
                            {{ isSignUp ? 'Sign Up Account' : 'Sign In Account' }}
                        </h2>

                        <!-- Desktop Toggle Pills -->
                        <div class="hidden sm:flex items-center bg-[#131722] border border-[#232838] rounded-xl p-0.5 text-xs font-medium">
                            <button
                                type="button"
                                @click="isSignUp = false"
                                class="px-3.5 py-1.5 rounded-lg transition-all"
                                :class="!isSignUp ? 'bg-white text-gray-950 font-bold shadow-xs' : 'text-gray-400 hover:text-white'"
                            >
                                Sign In
                            </button>
                            <button
                                type="button"
                                @click="isSignUp = true"
                                class="px-3.5 py-1.5 rounded-lg transition-all"
                                :class="isSignUp ? 'bg-white text-gray-950 font-bold shadow-xs' : 'text-gray-400 hover:text-white'"
                            >
                                Sign Up
                            </button>
                        </div>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-400">
                        {{ isSignUp ? 'Enter your personal data to create your workspace account.' : 'Enter your personal data to access your account.' }}
                    </p>
                </div>

                <!-- Social Authentication Buttons (Google & GitHub Side-by-Side) -->
                <div class="grid grid-cols-2 gap-3">
                    <!-- Google -->
                    <a
                        :href="route('auth.redirect', 'google')"
                        class="flex items-center justify-center gap-2.5 px-4 py-3 rounded-xl border border-[#252a3a] bg-[#12151f] hover:bg-[#181c2b] text-white text-xs sm:text-sm font-semibold transition-all shadow-xs group"
                    >
                        <svg class="w-4 h-4 shrink-0 transition-transform group-hover:scale-110" viewBox="0 0 24 24">
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
                        class="flex items-center justify-center gap-2.5 px-4 py-3 rounded-xl border border-[#252a3a] bg-[#12151f] hover:bg-[#181c2b] text-white text-xs sm:text-sm font-semibold transition-all shadow-xs group"
                    >
                        <svg class="w-4 h-4 shrink-0 fill-current text-gray-200 transition-transform group-hover:scale-110" viewBox="0 0 24 24">
                            <path d="M12 0C5.37 0 0 5.37 0 12c0 5.31 3.435 9.795 8.205 11.385.6.105.825-.255.825-.57 0-.285-.015-1.23-.015-2.235-3.015.555-3.795-.735-4.035-1.41-.135-.345-.72-1.41-1.23-1.695-.42-.225-1.02-.78-.015-.795.945-.015 1.62.87 1.845 1.23 1.08 1.815 2.805 1.305 3.495.99.105-.78.42-1.305.765-1.605-2.67-.3-5.46-1.335-5.46-5.925 0-1.305.465-2.385 1.23-3.225-.12-.3-.54-1.53.12-3.18 0 0 1.005-.315 3.3 1.23.96-.27 1.98-.405 3-.405s2.04.135 3 .405c2.295-1.56 3.3-1.23 3.3-1.23.66 1.65.24 2.88.12 3.18.765.84 1.23 1.905 1.23 3.225 0 4.605-2.805 5.625-5.475 5.925.435.375.81 1.095.81 2.22 0 1.605-.015 2.895-.015 3.3 0 .315.225.69.825.57A12.02 12.02 0 0024 12c0-6.63-5.37-12-12-12z" />
                        </svg>
                        <span>Github</span>
                    </a>
                </div>

                <!-- Divider -->
                <div class="relative flex items-center justify-center">
                    <div class="w-full border-t border-[#1d2232]"></div>
                    <span class="absolute px-3 bg-[#07090e] text-xs font-mono text-gray-500 uppercase tracking-wider">Or</span>
                </div>

                <!-- ============================================================= -->
                <!-- SIGN IN FORM                                                  -->
                <!-- ============================================================= -->
                <form v-if="!isSignUp" @submit.prevent="submitLogin" class="space-y-4">
                    <!-- Email -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-gray-300">Email</label>
                        <input
                            v-model="loginForm.email"
                            type="email"
                            required
                            placeholder="eg. founder@forge.local"
                            class="w-full px-4 py-3 rounded-xl bg-[#12151f] border border-[#252a3a] text-white placeholder:text-gray-500 text-sm focus:outline-none focus:border-emerald-500/80 focus:ring-2 focus:ring-emerald-500/20 transition-colors"
                        />
                        <p v-if="loginForm.errors.email" class="text-xs text-red-400 font-mono mt-1">{{ loginForm.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-gray-300">Password</label>
                            <a href="#" class="text-xs text-emerald-400 hover:text-emerald-300 transition-colors">Forgot password?</a>
                        </div>
                        <div class="relative">
                            <input
                                v-model="loginForm.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                placeholder="Enter your password"
                                class="w-full px-4 py-3 pr-11 rounded-xl bg-[#12151f] border border-[#252a3a] text-white placeholder:text-gray-500 text-sm focus:outline-none focus:border-emerald-500/80 focus:ring-2 focus:ring-emerald-500/20 transition-colors"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-200 transition-colors p-1"
                                aria-label="Toggle password visibility"
                            >
                                <svg v-if="!showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="loginForm.errors.password" class="text-xs text-red-400 font-mono mt-1">{{ loginForm.errors.password }}</p>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center gap-2 pt-1">
                        <input
                            v-model="loginForm.remember"
                            id="remember"
                            type="checkbox"
                            class="w-4 h-4 rounded-sm bg-[#12151f] border-[#252a3a] text-emerald-500 focus:ring-emerald-500/20"
                        />
                        <label for="remember" class="text-xs text-gray-400 select-none">Remember this device for 30 days</label>
                    </div>

                    <!-- White Submit Button (Matching Reference) -->
                    <button
                        type="submit"
                        :disabled="isLoading || loginForm.processing"
                        class="w-full py-3.5 px-4 rounded-xl bg-white text-gray-950 font-bold text-sm hover:bg-gray-100 active:scale-[0.99] shadow-lg shadow-white/5 transition-all duration-150 disabled:opacity-50 flex items-center justify-center gap-2 mt-2"
                    >
                        <span v-if="loginForm.processing" class="w-4 h-4 border-2 border-gray-950 border-t-transparent rounded-full animate-spin"></span>
                        <span>Sign In</span>
                    </button>
                </form>

                <!-- ============================================================= -->
                <!-- SIGN UP FORM                                                  -->
                <!-- ============================================================= -->
                <form v-else @submit.prevent="submitRegister" class="space-y-4">
                    <!-- First & Last Name (2 Column Grid) -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-gray-300">First Name</label>
                            <input
                                v-model="registerForm.first_name"
                                type="text"
                                required
                                placeholder="eg. John"
                                class="w-full px-4 py-3 rounded-xl bg-[#12151f] border border-[#252a3a] text-white placeholder:text-gray-500 text-sm focus:outline-none focus:border-emerald-500/80 focus:ring-2 focus:ring-emerald-500/20 transition-colors"
                            />
                            <p v-if="registerForm.errors.first_name" class="text-xs text-red-400 font-mono mt-1">{{ registerForm.errors.first_name }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-gray-300">Last Name</label>
                            <input
                                v-model="registerForm.last_name"
                                type="text"
                                placeholder="eg. Francisco"
                                class="w-full px-4 py-3 rounded-xl bg-[#12151f] border border-[#252a3a] text-white placeholder:text-gray-500 text-sm focus:outline-none focus:border-emerald-500/80 focus:ring-2 focus:ring-emerald-500/20 transition-colors"
                            />
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-gray-300">Email</label>
                        <input
                            v-model="registerForm.email"
                            type="email"
                            required
                            placeholder="eg. johnfrans@gmail.com"
                            class="w-full px-4 py-3 rounded-xl bg-[#12151f] border border-[#252a3a] text-white placeholder:text-gray-500 text-sm focus:outline-none focus:border-emerald-500/80 focus:ring-2 focus:ring-emerald-500/20 transition-colors"
                        />
                        <p v-if="registerForm.errors.email" class="text-xs text-red-400 font-mono mt-1">{{ registerForm.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-gray-300">Password</label>
                        <div class="relative">
                            <input
                                v-model="registerForm.password"
                                :type="showPassword ? 'text' : 'password'"
                                required
                                placeholder="Enter your password"
                                class="w-full px-4 py-3 pr-11 rounded-xl bg-[#12151f] border border-[#252a3a] text-white placeholder:text-gray-500 text-sm focus:outline-none focus:border-emerald-500/80 focus:ring-2 focus:ring-emerald-500/20 transition-colors"
                            />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-200 transition-colors p-1"
                                aria-label="Toggle password visibility"
                            >
                                <svg v-if="!showPassword" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-[11px] text-gray-500 font-mono mt-1">Must be at least 8 characters.</p>
                        <p v-if="registerForm.errors.password" class="text-xs text-red-400 font-mono">{{ registerForm.errors.password }}</p>
                    </div>

                    <!-- Technical Persona Selection -->
                    <div class="space-y-1.5 pt-1">
                        <label class="block text-xs font-semibold text-gray-300">How do you build?</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                type="button"
                                @click="registerForm.technical_level = 'vibe_coder'"
                                class="px-3 py-2 rounded-xl text-left border text-xs transition-all"
                                :class="registerForm.technical_level === 'vibe_coder' ? 'bg-indigo-500/15 border-indigo-400 text-indigo-300 font-bold' : 'bg-[#12151f] border-[#252a3a] text-gray-400 hover:border-gray-600'"
                            >
                                <div class="font-bold">Vibe Coder</div>
                                <div class="text-[10px] text-gray-500">Natural language AI prompts</div>
                            </button>
                            <button
                                type="button"
                                @click="registerForm.technical_level = 'developer'"
                                class="px-3 py-2 rounded-xl text-left border text-xs transition-all"
                                :class="registerForm.technical_level === 'developer' ? 'bg-emerald-500/15 border-emerald-400 text-emerald-300 font-bold' : 'bg-[#12151f] border-[#252a3a] text-gray-400 hover:border-gray-600'"
                            >
                                <div class="font-bold">Engineer</div>
                                <div class="text-[10px] text-gray-500">Code & architecture focus</div>
                            </button>
                        </div>
                    </div>

                    <!-- White Submit Button -->
                    <button
                        type="submit"
                        :disabled="isLoading || registerForm.processing"
                        class="w-full py-3.5 px-4 rounded-xl bg-white text-gray-950 font-bold text-sm hover:bg-gray-100 active:scale-[0.99] shadow-lg shadow-white/5 transition-all duration-150 disabled:opacity-50 flex items-center justify-center gap-2 mt-2"
                    >
                        <span v-if="registerForm.processing" class="w-4 h-4 border-2 border-gray-950 border-t-transparent rounded-full animate-spin"></span>
                        <span>Sign Up</span>
                    </button>
                </form>

                <!-- ============================================================= -->
                <!-- DEMO PERSONAS: USER DESCRIPTIONS & 1-CLICK TEST LOGINS        -->
                <!-- ============================================================= -->
                <div class="pt-6 border-t border-[#1d2232] space-y-3.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-mono uppercase tracking-wider text-emerald-400 font-bold flex items-center gap-1.5">
                            <span>⚡</span>
                            <span>Quick Demo Personas</span>
                        </span>
                        <span class="text-[10px] font-mono text-gray-500">1-Click Test Access</span>
                    </div>

                    <!-- Persona 1: Founder -->
                    <div
                        @click="triggerPersonaLogin('founder')"
                        class="p-3.5 rounded-xl border border-[#23293a] bg-[#111420] hover:bg-[#161a29] hover:border-indigo-500/40 cursor-pointer transition-all group"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 font-bold text-sm flex items-center justify-center shrink-0">
                                    🚀
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-white group-hover:text-indigo-300 transition-colors">Adaeze Founder</span>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-mono bg-indigo-500/15 text-indigo-300 border border-indigo-500/30">
                                            Solo Founder & Vibe Coder
                                        </span>
                                    </div>
                                    <div class="text-[11px] font-mono text-gray-400 mt-0.5">founder@forge.local</div>
                                </div>
                            </div>
                            <span class="text-xs font-mono text-indigo-400 group-hover:translate-x-0.5 transition-transform">Login →</span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-2.5 leading-relaxed pl-10">
                            Builds AI-first products using natural language. Pre-loaded with <strong>200 Pro credits</strong>, an active product intelligence workspace, market discovery matrix, and stage-by-stage PRD generator.
                        </p>
                    </div>

                    <!-- Persona 2: Developer -->
                    <div
                        @click="triggerPersonaLogin('developer')"
                        class="p-3.5 rounded-xl border border-[#23293a] bg-[#111420] hover:bg-[#161a29] hover:border-emerald-500/40 cursor-pointer transition-all group"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 font-bold text-sm flex items-center justify-center shrink-0">
                                    💻
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-white group-hover:text-emerald-300 transition-colors">Liam Engineer</span>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-mono bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">
                                            Senior Lead Developer
                                        </span>
                                    </div>
                                    <div class="text-[11px] font-mono text-gray-400 mt-0.5">developer@forge.local</div>
                                </div>
                            </div>
                            <span class="text-xs font-mono text-emerald-400 group-hover:translate-x-0.5 transition-transform">Login →</span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-2.5 leading-relaxed pl-10">
                            Technical builder executing codebase audits, digital transformation roadmaps, security reviews, and direct export of AI developer packages (<code class="text-emerald-300">AGENTS.md</code>, <code class="text-emerald-300">CLAUDE.md</code>) to GitHub.
                        </p>
                    </div>

                    <!-- Persona 3: Superadmin -->
                    <div
                        @click="triggerPersonaLogin('admin')"
                        class="p-3.5 rounded-xl border border-[#23293a] bg-[#111420] hover:bg-[#161a29] hover:border-amber-500/40 cursor-pointer transition-all group"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 font-bold text-sm flex items-center justify-center shrink-0">
                                    ⚡
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-white group-hover:text-amber-300 transition-colors">Forge Administrator</span>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-mono bg-amber-500/15 text-amber-300 border border-amber-500/30">
                                            Platform Superadmin
                                        </span>
                                    </div>
                                    <div class="text-[11px] font-mono text-gray-400 mt-0.5">admin@forge.local</div>
                                </div>
                            </div>
                            <span class="text-xs font-mono text-amber-400 group-hover:translate-x-0.5 transition-transform">Login →</span>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-2.5 leading-relaxed pl-10">
                            Superadmin console access: monitors live AI provider latency (Anthropic, OpenAI, Gemini), manages system API keys, grants credit allocations, and reviews organizational audit trails.
                        </p>
                    </div>
                </div>

                <!-- Footer Switcher Link -->
                <div class="text-center pt-2">
                    <p class="text-xs text-gray-400">
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

                <!-- Privacy & Transparency Note -->
                <p class="text-[11px] text-gray-500 text-center leading-relaxed">
                    By accessing FORGE, you agree to our evidence-first consent policy. Customer workspaces and proprietary blueprints are never used to train public AI models.
                </p>

            </div>
        </div>

    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';

const isDark = ref(false);

const applyTheme = (dark: boolean) => {
    isDark.value = dark;
    if (dark) {
        document.documentElement.classList.add('dark');
        document.documentElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('forge-theme', 'dark');
    } else {
        document.documentElement.classList.remove('dark');
        document.documentElement.setAttribute('data-theme', 'light');
        localStorage.setItem('forge-theme', 'light');
    }
};

const toggleTheme = () => {
    applyTheme(!isDark.value);
};

onMounted(() => {
    const saved = localStorage.getItem('forge-theme');
    if (saved) {
        applyTheme(saved === 'dark');
    } else {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        applyTheme(prefersDark);
    }
});
</script>

<template>
    <button
        type="button"
        @click="toggleTheme"
        class="p-2 rounded-xl border border-primary bg-surface-secondary hover:bg-surface-tertiary text-text-secondary hover:text-text-primary transition-colors flex items-center justify-center focus:outline-hidden"
        :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
        aria-label="Toggle visual theme"
    >
        <!-- Sun icon for Dark Mode (click to go light) -->
        <svg
            v-if="isDark"
            class="w-4 h-4 text-amber-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <circle cx="12" cy="12" r="4" stroke-width="2" />
            <path stroke-width="2" stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41" />
        </svg>

        <!-- Moon icon for Light Mode (click to go dark) -->
        <svg
            v-else
            class="w-4 h-4 text-text-secondary"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
        >
            <path stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
    </button>
</template>

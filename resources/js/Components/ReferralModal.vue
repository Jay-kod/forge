<script setup lang="ts">
import { ref, computed } from 'vue';

const props = defineProps<{
    isOpen: boolean;
    referralCode?: string | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const copied = ref(false);

const inviteUrl = computed(() => {
    if (!props.referralCode) return '';
    return `${window.location.origin}/login?ref=${props.referralCode}`;
});

const copyLink = () => {
    if (!inviteUrl.value) return;
    navigator.clipboard.writeText(inviteUrl.value);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2500);
};

const copyCode = () => {
    if (!props.referralCode) return;
    navigator.clipboard.writeText(props.referralCode);
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2500);
};
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs">
        <div class="bg-surface-secondary border border-primary rounded-2xl max-w-md w-full p-6 shadow-2xl relative">
            <!-- Close Button -->
            <button
                @click="emit('close')"
                class="absolute top-4 right-4 text-text-tertiary hover:text-text-primary text-sm font-mono p-1"
                aria-label="Close modal"
            >
                ✕
            </button>

            <!-- Modal Header -->
            <div class="text-center mb-5">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/15 border border-indigo-500/30 flex items-center justify-center text-2xl mx-auto mb-3">
                    🎁
                </div>
                <h3 class="text-lg font-display font-bold text-text-primary">
                    Invite Founders & Earn Credits
                </h3>
                <p class="text-xs text-text-secondary mt-1 max-w-sm mx-auto leading-relaxed">
                    Share your invite link with founders. When they launch their first workspace, you both receive <strong class="text-emerald-400">50 bonus intelligence credits</strong>.
                </p>
            </div>

            <!-- Referral Code Card -->
            <div class="p-4 rounded-xl bg-surface-primary border border-primary mb-4 text-center">
                <span class="text-[10px] font-mono uppercase tracking-wider text-text-tertiary block mb-1">
                    Your Personal Referral Code
                </span>
                <div class="text-2xl font-mono font-black text-indigo-400 tracking-widest my-1">
                    {{ referralCode || 'FORGEVIP' }}
                </div>
                <button
                    type="button"
                    @click="copyCode"
                    class="mt-2 text-xs font-mono text-text-secondary hover:text-text-primary underline"
                >
                    {{ copied ? '✓ Copied to clipboard!' : 'Copy code only' }}
                </button>
            </div>

            <!-- Shareable Invite Link -->
            <div class="space-y-1.5 mb-6">
                <label class="text-xs font-mono text-text-tertiary block">
                    Direct Invite Link
                </label>
                <div class="flex items-center gap-2">
                    <input
                        type="text"
                        readonly
                        :value="inviteUrl"
                        class="flex-1 px-3 py-2 rounded-xl bg-surface-primary border border-primary text-xs font-mono text-text-primary select-all focus:outline-hidden"
                    />
                    <button
                        type="button"
                        @click="copyLink"
                        class="px-3.5 py-2 rounded-xl brand-button text-xs font-bold shrink-0"
                    >
                        {{ copied ? '✓ Copied' : 'Copy Link' }}
                    </button>
                </div>
            </div>

            <!-- Done Action -->
            <button
                type="button"
                @click="emit('close')"
                class="w-full py-2.5 rounded-xl border border-primary bg-surface-primary hover:bg-surface-tertiary text-text-secondary hover:text-text-primary text-xs font-mono transition-colors"
            >
                Done
            </button>
        </div>
    </div>
</template>

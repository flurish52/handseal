<script setup>
import { useInstallPrompt } from '@/composables/useInstallPrompt';
import { ref } from 'vue';

const { canInstall, install } = useInstallPrompt();
const dismissed = ref(false);

const handleInstall = async () => {
    await install();
};
</script>

<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 translate-y-4"
        enter-to-class="opacity-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0"
        leave-to-class="opacity-0 translate-y-4"
    >
        <div
            v-if="canInstall && !dismissed"
            class="fixed bottom-20 left-1/2 z-50 flex w-[calc(100%-2rem)] max-w-sm -translate-x-1/2 items-center gap-3 rounded-2xl border border-seal-brass/30 bg-seal-navy px-4 py-3 shadow-xl shadow-black/20 sm:bottom-20 sm:left-auto sm:right-6 sm:w-auto sm:translate-x-0"
        >
            <!-- Seal icon badge -->
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-seal-brass/15">
                <svg viewBox="0 0 40 40" fill="none" class="h-6 w-6">
                    <circle cx="20" cy="20" r="19" fill="#c79a46" />
                    <circle cx="20" cy="20" r="19" stroke="#e7c577" stroke-width="1" />
                    <text x="20" y="26" text-anchor="middle" font-family="Fraunces, serif" font-size="16" font-weight="600" fill="#101a30">HS</text>
                </svg>
            </div>

            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-white">Install HandSeal</p>
                <p class="text-xs text-white/60">Faster access, right from your home screen</p>
            </div>

            <button
                @click="handleInstall"
                class="shrink-0 rounded-lg bg-seal-brass px-3.5 py-2 text-sm font-semibold text-seal-navy transition hover:bg-seal-brass/90 active:scale-95"
            >
                Install
            </button>

            <button
                @click="dismissed = true"
                aria-label="Dismiss"
                class="shrink-0 text-white/40 transition hover:text-white/70"
            >
                <svg viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4">
                    <path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" />
                </svg>
            </button>
        </div>
    </Transition>
</template>

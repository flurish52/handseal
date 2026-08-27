<script setup>
import { useToast } from '@/composables/useToast.js';

const { toasts, dismiss } = useToast();

const variantStyles = {
    success: 'bg-white border-l-4 border-emerald-500 text-seal-ink',
    danger: 'bg-white border-l-4 border-seal-danger text-seal-ink',
    warning: 'bg-white border-l-4 border-amber-500 text-seal-ink',
    info: 'bg-white border-l-4 border-seal-navy text-seal-ink',
};

const icons = {
    success: '✓',
    danger: '✕',
    warning: '!',
    info: 'i',
};

const iconStyles = {
    success: 'bg-emerald-500 text-white',
    danger: 'bg-seal-danger text-white',
    warning: 'bg-amber-500 text-white',
    info: 'bg-seal-navy text-white',
};
</script>

<template>
    <Teleport to="body">
        <div class="fixed top-4 right-4 z-[100] flex flex-col gap-2 w-[calc(100%-2rem)] max-w-sm">
            <TransitionGroup name="toast">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="rounded-card shadow-lg p-3 flex items-start gap-3"
                    :class="variantStyles[toast.variant]"
                >
                    <span
                        class="shrink-0 w-5 h-5 rounded-full flex items-center justify-center text-[11px] font-bold mt-0.5"
                        :class="iconStyles[toast.variant]"
                    >
                        {{ icons[toast.variant] }}
                    </span>
                    <p class="text-sm flex-1">{{ toast.message }}</p>
                    <button
                        @click="dismiss(toast.id)"
                        class="shrink-0 text-seal-muted hover:text-seal-ink text-sm leading-none"
                    >
                        ✕
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.2s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateX(20px);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(20px);
}
</style>

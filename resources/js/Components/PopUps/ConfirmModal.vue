<script setup>
import { useConfirm } from '@/composables/useConfirm';

const { state, resolve } = useConfirm();

const variantButton = {
    danger: 'bg-seal-danger text-white',
    warning: 'bg-amber-500 text-white',
    default: 'bg-seal-navy text-white',
};
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="state.open"
                class="fixed inset-0 z-[110] bg-black/40 flex items-center justify-center p-4"
                @click.self="resolve(false)"
            >
                <div class="bg-white rounded-card w-full max-w-sm p-5 space-y-4 shadow-xl">
                    <div>
                        <p class="font-serif text-lg font-semibold text-seal-navy">{{ state.title }}</p>
                        <p class="text-sm text-seal-muted mt-1">{{ state.message }}</p>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button
                            @click="resolve(false)"
                            class="text-sm text-seal-muted px-4 py-2 rounded-lg"
                        >
                            {{ state.cancelLabel }}
                        </button>
                        <button
                            @click="resolve(true)"
                            class="text-sm font-medium px-4 py-2 rounded-lg"
                            :class="variantButton[state.variant] ?? variantButton.default"
                        >
                            {{ state.confirmLabel }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>

<template>
    <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4" @click.self="$emit('close')">
        <div class="w-full max-w-sm bg-white rounded-card p-5 space-y-4">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-seal-ink">{{ title }}</p>
                <button @click="$emit('close')" class="text-seal-muted">
                    <Icon name="close" :size="18" />
                </button>
            </div>

            <p class="text-sm text-seal-ink">{{ message }}</p>

            <div class="space-y-2">
                <component
                    v-for="action in actions"
                    :key="action.key"
                    :is="action.href ? 'a' : 'button'"
                    :href="action.href"
                    :disabled="!action.href && action.loading"
                    @click="action.href ? null : action.onClick?.()"
                    class="block w-full text-center text-sm font-medium px-4 py-2.5 rounded-lg"
                    :class="action.style === 'primary' ? 'bg-seal-navy text-white' : 'border border-seal-line text-seal-ink'"
                >
                    {{ action.loading ? (action.loadingLabel ?? 'Redirecting…') : action.label }}
                </component>

                <p v-if="hint" class="text-center text-xs text-seal-ink/60">{{ hint }}</p>
            </div>

            <div v-if="showLegal" class="mt-3">
                <LegalLinks prefix="By subscribing, you agree to our" />
            </div>
        </div>
    </div>
</template>

<script setup>
import Icon from '@/Components/Icons/Icon.vue';
import LegalLinks from '@/Components/LegalLinks.vue';

defineProps({
    title: { type: String, required: true },
    message: { type: String, required: true },
    hint: { type: String, default: null },
    showLegal: { type: Boolean, default: false },
    actions: { type: Array, required: true },
});

defineEmits(['close']);
</script>

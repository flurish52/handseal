<script setup>
defineProps({
    // { title, url, type: 'html' | 'image', actionable, isPremium, owned, priceNaira }
    preview: { type: Object, default: null },
    isFirst: { type: Boolean, default: true },
    isLast: { type: Boolean, default: true },
});
defineEmits(['close', 'next', 'prev', 'buy', 'use']);
</script>

<template>
    <div v-if="preview" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="$emit('close')">
        <div class="bg-white rounded-card w-full max-w-3xl overflow-hidden relative">
            <div class="flex items-center justify-between px-4 py-3 border-b border-seal-line">
                <p class="text-sm font-medium text-seal-ink truncate">{{ preview.title }}</p>
                <button @click="$emit('close')" class="text-seal-muted text-lg leading-none shrink-0 ml-3">×</button>
            </div>

            <div class="w-full h-[70vh] overflow-auto bg-seal-paper">
                <iframe
                    v-if="preview.type === 'html'"
                    :src="preview.url"
                    class="w-full h-full border-0"
                    loading="lazy"
                    sandbox=""
                ></iframe>
                <img v-else :src="preview.url" class="w-full h-full object-contain" alt="" />
            </div>

            <div v-if="preview.actionable" class="flex items-center gap-3 px-4 py-3 border-t border-seal-line">
                <p v-if="preview.isPremium && !preview.owned" class="text-xs text-seal-muted">
                    ₦{{ preview.priceNaira }}
                </p>
                <button
                    v-if="preview.owned"
                    type="button"
                    class="ml-auto text-xs font-medium text-white bg-seal-navy rounded px-3 py-1.5 hover:bg-seal-navy/90"
                    @click="$emit('use')"
                >
                    Use this template
                </button>
                <button
                    v-else
                    type="button"
                    class="ml-auto text-xs font-medium text-white bg-seal-navy rounded px-3 py-1.5 hover:bg-seal-navy/90"
                    @click="$emit('buy')"
                >
                    Buy — ₦{{ preview.priceNaira }}
                </button>
            </div>

            <button
                v-if="!isFirst"
                @click="$emit('prev')"
                class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white rounded-full w-9 h-9 flex items-center justify-center shadow"
            >‹</button>
            <button
                v-if="!isLast"
                @click="$emit('next')"
                class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/90 hover:bg-white rounded-full w-9 h-9 flex items-center justify-center shadow"
            >›</button>
        </div>
    </div>
</template>

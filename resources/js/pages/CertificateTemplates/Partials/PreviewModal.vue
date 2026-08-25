<script setup>
defineProps({
    preview: { type: Object, default: null }, // { title, url }
});

const emit = defineEmits(['close']);
</script>

<template>
    <div
        v-if="preview"
        class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
        @click.self="emit('close')"
    >
        <div class="bg-white rounded-card w-full max-w-3xl overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-seal-line">
                <p class="text-sm font-medium text-seal-ink">{{ preview.title }}</p>
                <button @click="emit('close')" class="text-seal-muted text-lg leading-none">×</button>
            </div>
            <iframe :src="preview.url" class="w-full h-[70vh] border-0" sandbox=""></iframe>
        </div>
    </div>
</template>

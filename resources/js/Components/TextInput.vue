<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({ type: String, required: true });

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        v-model="model"
        ref="input"
        class="w-full rounded-lg border border-seal-line bg-white px-3.5 py-2.5 text-sm text-seal-ink placeholder:text-seal-muted transition-colors focus:border-seal-brass focus:outline-none focus:ring-2 focus:ring-seal-brass/30"
    />
</template>

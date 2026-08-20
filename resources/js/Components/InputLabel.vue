<script setup>
defineProps({ value: { type: String, default: '' } });
</script>

<template>
    <label class="mb-1.5 block text-sm font-medium text-seal-ink">
        <slot v-if="$slots.default" />
        <template v-else>{{ value }}</template>
    </label>
</template>

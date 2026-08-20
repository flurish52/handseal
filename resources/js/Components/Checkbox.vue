<script setup>
const checked = defineModel('checked', { type: [Array, Boolean], required: true });

defineProps({ value: { default: null } });
</script>

<template>
    <input
        v-model="checked"
        type="checkbox"
        :value="value"
        class="h-4 w-4 rounded border-seal-line text-seal-navy focus:ring-2 focus:ring-seal-brass/30 focus:ring-offset-0"
    />
</template>

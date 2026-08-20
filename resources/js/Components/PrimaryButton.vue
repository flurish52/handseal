<script setup>
defineProps({ disabled: { type: Boolean, default: false } });
</script>

<template>
    <button
        type="submit"
        :disabled="disabled"
        class="inline-flex items-center justify-center rounded-lg bg-seal-navy px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-seal-navy-light focus:outline-none focus:ring-2 focus:ring-seal-navy/40 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
    >
        <slot />
    </button>
</template>

<script setup>
defineProps({ message: { type: String, default: '' } });
</script>

<template>
    <p v-if="message" class="mt-1.5 text-sm text-seal-danger">
        {{ message }}
    </p>
</template>

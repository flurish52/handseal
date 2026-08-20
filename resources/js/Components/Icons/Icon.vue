<script setup>
import { computed } from 'vue';
import icons from './icons.js';

const props = defineProps({
    name: { type: String, required: true },
    size: { type: [Number, String], default: 20 },
    strokeWidth: { type: [Number, String], default: 2 },
});

const elements = computed(() => {
    const icon = icons[props.name];

    if (!icon && import.meta.env.DEV) {
        console.warn(`[Icon] Unknown icon name: "${props.name}"`);
    }

    return icon ?? [];
});
</script>

<template>
    <svg
        :width="size"
        :height="size"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        :stroke-width="strokeWidth"
        stroke-linecap="round"
        stroke-linejoin="round"
        xmlns="http://www.w3.org/2000/svg"
        aria-hidden="true"
    >
        <component :is="el.tag" v-for="(el, i) in elements" :key="i" v-bind="el.attrs" />
    </svg>
</template>

<script setup>
/**
 * Standalone "add new" affordance for use next to a select/field when the
 * thing they need might not exist yet (e.g. "Programme" when there are no
 * programmes). Not tied to programmes/students — mount it anywhere.
 *
 * Two modes:
 *  - Pass `href` -> renders as a link (defaults to opening in a new tab,
 *    so whatever form the user is filling out on the current page is
 *    left untouched).
 *  - Omit `href` -> renders as a button and emits `click`, for cases
 *    where you'd rather open a modal or run custom logic instead of
 *    navigating away.
 *
 * Usage:
 *   <AddNewLink label="New programme" :href="route('programmes.index')" />
 *   <AddNewLink label="New tag" @click="openTagModal" />
 */
defineProps({
    label: { type: String, default: 'Add new' },
    href: { type: String, default: null },
    newTab: { type: Boolean, default: false },
});

const emit = defineEmits(['click']);

function handleClick() {
    emit('click');
}
</script>

<template>
    <a
        v-if="href"
        :href="href"
        :target="newTab ? '_blank' : undefined"
        :rel="newTab ? 'noopener noreferrer' : undefined"
        class="inline-flex items-center gap-1 text-xs font-medium text-seal-navy hover:underline shrink-0"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <path d="M12 5v14M5 12h14" />
        </svg>
        {{ label }}
    </a>

    <button
        v-else
        type="button"
        @click="handleClick"
        class="inline-flex items-center gap-1 text-xs font-medium text-seal-navy hover:underline shrink-0"
    >
        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <path d="M12 5v14M5 12h14" />
        </svg>
        {{ label }}
    </button>
</template>

<script setup>
/**
 * Standalone, reusable search box.
 *
 * It doesn't know what fields it's searching — the parent decides that.
 * Just bind v-model and read the (debounced) value in a computed filter.
 *
 * Usage:
 *   <SearchInput v-model="searchQuery" placeholder="Search students..." />
 *
 * Different pages can mount this with completely different placeholders
 * and match against completely different fields — this component has
 * zero knowledge of "students", "programmes", etc.
 */
import { ref, watch } from 'vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    placeholder: { type: String, default: 'Search...' },
    // Set to 0 for instant, no-debounce updates.
    debounceMs: { type: Number, default: 250 },
});

const emit = defineEmits(['update:modelValue']);

const internalValue = ref(props.modelValue);
let debounceTimer = null;

// Keep in sync if parent resets modelValue externally (e.g. a "Clear all" button).
watch(
    () => props.modelValue,
    (val) => {
        if (val !== internalValue.value) internalValue.value = val;
    },
);

watch(internalValue, (val) => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        emit('update:modelValue', val);
    }, props.debounceMs);
});

function clear() {
    internalValue.value = '';
}
</script>

<template>
    <div class="relative">
        <svg
            xmlns="http://www.w3.org/2000/svg"
            class="w-4 h-4 text-seal-muted absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <circle cx="11" cy="11" r="7" />
            <path d="M21 21l-4.35-4.35" />
        </svg>

        <input
            v-model="internalValue"
            type="text"
            :placeholder="placeholder"
            class="w-full pl-9 pr-8 py-2.5 text-sm bg-white border border-seal-line rounded-lg text-seal-ink placeholder:text-seal-muted focus:outline-none focus:ring-2 focus:ring-seal-navy/20 focus:border-seal-navy transition-colors"
        />

        <button
            v-if="internalValue"
            @click="clear"
            type="button"
            aria-label="Clear search"
            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-seal-muted hover:text-seal-ink p-0.5 rounded-full hover:bg-seal-line/50 transition-colors"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <path d="M18 6L6 18M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>

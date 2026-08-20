<script setup>
/**
 * Standalone, config-driven filter panel.
 *
 * The parent describes what's filterable via the `filters` prop and owns
 * the actual filter state via v-model. This component just renders the
 * right control per filter type and emits changes — it has no idea what
 * "students" or "status" mean.
 *
 * Supported filter types:
 *   - 'select'    -> single-value dropdown. value: string ('' = no filter)
 *   - 'dateRange' -> from/to date pair.       value: { from: string, to: string }
 *
 * Usage:
 *   const filtersConfig = [
 *     { key: 'status', label: 'Status', type: 'select', options: [
 *         { value: 'active', label: 'Active' },
 *         { value: 'completed', label: 'Completed' },
 *     ]},
 *     { key: 'start_at', label: 'Start date', type: 'dateRange' },
 *   ];
 *
 *   const filterValues = ref(buildDefaultFilterValues(filtersConfig));
 *
 *   <FilterPanel :filters="filtersConfig" v-model="filterValues" />
 *
 * Helper `buildDefaultFilterValues` is exported below so any page can
 * build a matching initial state without repeating the shape by hand.
 */
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    filters: { type: Array, required: true },
    modelValue: { type: Object, required: true },
    label: { type: String, default: 'Filters' },
});

const emit = defineEmits(['update:modelValue']);

const open = ref(false);
const panelRef = ref(null);
const triggerRef = ref(null);

function isFilterActive(filter) {
    const val = props.modelValue[filter.key];
    if (filter.type === 'dateRange') {
        return !!(val && (val.from || val.to));
    }
    return !!val;
}

const activeCount = computed(() => props.filters.filter(isFilterActive).length);

function updateSelect(key, value) {
    emit('update:modelValue', { ...props.modelValue, [key]: value });
}

function updateDateRange(key, part, value) {
    const current = props.modelValue[key] ?? { from: '', to: '' };
    emit('update:modelValue', {
        ...props.modelValue,
        [key]: { ...current, [part]: value },
    });
}

function clearFilter(key, filter) {
    emit('update:modelValue', {
        ...props.modelValue,
        [key]: filter.type === 'dateRange' ? { from: '', to: '' } : '',
    });
}

function clearAll() {
    emit('update:modelValue', buildDefaultFilterValues(props.filters));
}

function toggle() {
    open.value = !open.value;
}

function handleClickOutside(event) {
    if (!open.value) return;
    if (panelRef.value?.contains(event.target)) return;
    if (triggerRef.value?.contains(event.target)) return;
    open.value = false;
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside));
</script>

<script>
// Exported so pages can build initial filter state without duplicating the shape.
export function buildDefaultFilterValues(filters) {
    return filters.reduce((acc, filter) => {
        acc[filter.key] = filter.type === 'dateRange' ? { from: '', to: '' } : '';
        return acc;
    }, {});
}
</script>

<template>
    <div class="relative inline-block">
        <button
            ref="triggerRef"
            type="button"
            @click="toggle"
            class="flex items-center gap-1.5 text-sm font-medium px-3.5 py-2.5 rounded-lg border transition-colors"
            :class="activeCount > 0
                ? 'bg-seal-navy/5 border-seal-navy/30 text-seal-navy'
                : 'bg-white border-seal-line text-seal-ink hover:border-seal-navy/30'"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 6h16M7 12h10M10 18h4" />
            </svg>
            {{ label }}
            <span
                v-if="activeCount > 0"
                class="inline-flex items-center justify-center min-w-[1.1rem] h-[1.1rem] px-1 text-[10px] font-mono rounded-full bg-seal-brass text-white"
            >
                {{ activeCount }}
            </span>
        </button>

        <div
            v-if="open"
            ref="panelRef"
            class="absolute z-20 mt-2 w-80 max-w-[90vw] bg-white border border-seal-line rounded-card shadow-lg p-4 space-y-4 right-0 sm:left-0 sm:right-auto"
        >
            <div v-for="filter in filters" :key="filter.key" class="space-y-1.5">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-medium text-seal-ink">{{ filter.label }}</label>
                    <button
                        v-if="isFilterActive(filter)"
                        @click="clearFilter(filter.key, filter)"
                        type="button"
                        class="text-[11px] text-seal-muted hover:text-seal-danger"
                    >
                        Clear
                    </button>
                </div>

                <select
                    v-if="filter.type === 'select'"
                    :value="modelValue[filter.key]"
                    @change="updateSelect(filter.key, $event.target.value)"
                    class="w-full text-sm bg-white border border-seal-line rounded-lg px-3 py-2 text-seal-ink focus:outline-none focus:ring-2 focus:ring-seal-navy/20 focus:border-seal-navy"
                >
                    <option value="">All</option>
                    <option v-for="opt in filter.options" :key="opt.value" :value="opt.value">
                        {{ opt.label }}
                    </option>
                </select>

                <div v-else-if="filter.type === 'dateRange'" class="flex items-center gap-2">
                    <input
                        type="date"
                        :value="modelValue[filter.key]?.from ?? ''"
                        @input="updateDateRange(filter.key, 'from', $event.target.value)"
                        class="w-full text-sm bg-white border border-seal-line rounded-lg px-2.5 py-2 text-seal-ink focus:outline-none focus:ring-2 focus:ring-seal-navy/20 focus:border-seal-navy"
                    />
                    <span class="text-seal-muted text-xs shrink-0">to</span>
                    <input
                        type="date"
                        :value="modelValue[filter.key]?.to ?? ''"
                        @input="updateDateRange(filter.key, 'to', $event.target.value)"
                        class="w-full text-sm bg-white border border-seal-line rounded-lg px-2.5 py-2 text-seal-ink focus:outline-none focus:ring-2 focus:ring-seal-navy/20 focus:border-seal-navy"
                    />
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-seal-line">
                <button
                    @click="clearAll"
                    type="button"
                    class="text-xs font-medium text-seal-muted hover:text-seal-danger"
                    :disabled="activeCount === 0"
                    :class="activeCount === 0 && 'opacity-40 cursor-not-allowed'"
                >
                    Clear all
                </button>
                <button
                    @click="open = false"
                    type="button"
                    class="text-xs font-medium text-seal-navy hover:underline"
                >
                    Done
                </button>
            </div>
        </div>
    </div>
</template>

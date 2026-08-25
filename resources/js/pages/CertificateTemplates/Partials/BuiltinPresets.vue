<script setup>
const props = defineProps({
    builtins: { type: Array, default: () => [] },
    activeKey: { type: String, default: null }, // business's current default_builtin_template_key
});

const emit = defineEmits(['preview', 'select']);

</script>

<template>
    <div v-if="builtins.length">
        <p class="text-sm font-medium text-seal-ink mb-2">Built-in presets</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div
                v-for="b in builtins"
                :key="b.key"
                class="bg-white rounded-card border overflow-hidden group"
                :class="b.key === activeKey ? 'border-seal-navy ring-1 ring-seal-navy' : 'border-seal-line'"
            >
                <div class="relative h-28 bg-seal-line/20 overflow-hidden cursor-pointer" @click="emit('preview', b.label, b.preview_url)">
                    <iframe
                        v-if="b.preview_url"
                        :src="b.preview_url"
                        class="absolute top-0 left-0 w-[420px] h-[297px] origin-top-left scale-[0.27] pointer-events-none"
                        loading="lazy"
                        sandbox=""
                    ></iframe>
                    <div v-else class="w-full h-full flex items-center justify-center text-[10px] text-seal-muted">
                        No preview
                    </div>
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center">
            <span class="opacity-0 group-hover:opacity-100 text-[11px] font-medium text-white bg-black/60 px-2 py-1 rounded transition-opacity">
                Preview
            </span>
                    </div>
                    <span v-if="b.key === activeKey" class="absolute top-1.5 left-1.5 text-[10px] font-medium text-white bg-seal-navy px-1.5 py-0.5 rounded">
            Active
        </span>
                </div>
                <div class="px-2 py-2 space-y-1.5">
                    <p class="text-xs text-seal-ink truncate">{{ b.label }}</p>
                    <button
                        v-if="b.key !== activeKey"
                        type="button"
                        class="w-full text-[11px] font-medium text-seal-navy border border-seal-navy/30 rounded px-2 py-1 hover:bg-seal-navy/5"
                        @click="emit('select', b.key)"
                    >
                        Use this template
                    </button>
                    <p v-else class="text-[11px] text-seal-muted text-center py-1">Currently active</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

defineProps({
    templates: { type: Array, default: () => [] },
});

const emit = defineEmits(['preview']);

function activate(template) {
    router.patch(route('certificate-templates.activate', template.id));
}

function reject(template) {
    if (confirm(`Reject "${template.name}"? This uses one of your 3 AI attempts.`)) {
        router.post(route('certificate-templates.reject', template.id));
    }
}

function destroy(template) {
    if (confirm(`Delete "${template.name}"? This can't be undone.`)) {
        router.delete(route('certificate-templates.destroy', template.id));
    }
}

function statusLabel(status) {
    if (status === 'active') return 'Active';
    if (status === 'inactive') return 'Not in use';
    return 'Draft, needs review';
}

function sourceLabel(source) {
    return source === 'admin' ? 'By our team' : 'By AI';
}
</script>

<template>
    <div>
        <p class="text-sm font-medium text-seal-ink mb-2">Your custom templates</p>
        <div v-if="templates.length" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div
                v-for="template in templates"
                :key="template.id"
                class="bg-white rounded-card border border-seal-line overflow-hidden"
            >
                <div
                    class="relative h-28 bg-seal-line/20 overflow-hidden cursor-pointer group"
                    @click="emit('preview', template.name, template.preview_url)"
                >
                    <iframe
                        v-if="template.preview_url"
                        :src="template.preview_url"
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
                </div>
                <div class="p-3">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs font-medium text-seal-ink truncate">{{ template.name }}</p>
                        <span class="shrink-0 text-[9px] font-mono uppercase text-seal-muted">{{ sourceLabel(template.source) }}</span>
                    </div>
                    <span
                        class="inline-block text-[10px] font-mono uppercase px-1.5 py-0.5 rounded mt-1"
                        :class="template.status === 'active' ? 'bg-seal-sage/15 text-seal-sage' : 'bg-seal-brass/15 text-seal-brass'"
                    >
                        {{ statusLabel(template.status) }}
                    </span>
                    <div class="flex flex-wrap gap-3 text-xs font-medium mt-2">
                        <button v-if="template.status !== 'active'" @click="activate(template)" class="text-seal-sage">
                            {{ template.status === 'inactive' ? 'Use this template' : 'Approve' }}
                        </button>
                        <button
                            v-if="template.status === 'draft' && template.source === 'ai'"
                            @click="reject(template)"
                            class="text-seal-brass"
                        >
                            Reject
                        </button>
                        <button @click="destroy(template)" class="text-seal-danger">Delete</button>
                    </div>
                </div>
            </div>
        </div>

        <p v-else class="text-sm text-seal-muted text-center py-8">
            No custom templates yet. Generate one above.
        </p>
    </div>
</template>

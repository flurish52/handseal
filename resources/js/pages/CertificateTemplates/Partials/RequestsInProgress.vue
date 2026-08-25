<script setup>
import { router } from '@inertiajs/vue3';

defineProps({
    requests: { type: Array, default: () => [] },
});

function discardRequest(req) {
    if (confirm(`Discard the request "${req.name}"?`)) {
        router.delete(route('certificate-template-requests.destroy', req.id));
    }
}

function requestStatusLabel(status) {
    return {
        pending: 'With our design team',
        in_review: 'Being designed',
        declined: 'Needs changes',
    }[status] ?? status ?? 'With our design team';
}

function requestStatusHint(status) {
    return {
        pending: "Our design team has it, we'll notify you the moment your certificate is ready to approve.",
        in_review: 'One of our designers is actively working on your certificate right now.',
        declined: 'See the note below, then feel free to send us an updated request.',
    }[status] ?? '';
}
</script>

<template>
    <div v-if="requests.length">
        <p class="text-sm font-medium text-seal-ink mb-2">Requests in progress</p>
        <div class="space-y-2">
            <div
                v-for="req in requests"
                :key="req.id"
                class="bg-white rounded-card border border-seal-line p-4"
            >
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-seal-ink">{{ req.name }}</p>
                    <span
                        class="text-[10px] font-mono uppercase px-2 py-0.5 rounded"
                        :class="{
                            'bg-seal-brass/15 text-seal-brass': req.status !== 'declined',
                            'bg-seal-danger/15 text-seal-danger': req.status === 'declined',
                        }"
                    >
                        {{ requestStatusLabel(req.status) }}
                    </span>
                </div>
                <p class="text-xs text-seal-muted mt-1">{{ req.description }}</p>
                <p class="text-xs text-seal-muted mt-2">{{ requestStatusHint(req.status) }}</p>
                <p v-if="req.admin_note" class="text-xs text-seal-danger mt-2">{{ req.admin_note }}</p>
                <div v-if="req.status === 'pending'" class="flex gap-3 text-xs font-medium mt-3">
                    <button @click="discardRequest(req)" class="text-seal-danger">Cancel request</button>
                </div>
            </div>
        </div>
    </div>
</template>

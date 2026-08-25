<script setup>
import { computed } from 'vue';

const props = defineProps({
    quota: { type: Object, required: true },
});

const emit = defineEmits(['go-to-team']);

const message = computed(() => {
    if (props.quota.has_active_template) {
        return "You've already got an active custom certificate. Need another design? Reach out to our team.";
    }
    if (props.quota.maxed_out) {
        return props.quota.has_draft_template
            ? "You've used all 3 AI attempts. Accept one of your drafts below, or request a template from our team."
            : "You've used all 3 AI attempts. Request a template from our team to keep going.";
    }
    return null;
});
</script>

<template>
    <div
        v-if="message"
        class="bg-seal-brass/10 border border-seal-brass/30 text-seal-ink text-sm rounded-lg px-3 py-2.5 flex items-center justify-between gap-3"
    >
        <span>{{ message }}</span>
        <button
            v-if="!quota.has_active_template"
            type="button"
            @click="emit('go-to-team')"
            class="shrink-0 text-xs font-medium text-seal-navy underline whitespace-nowrap cursor-pointer"
        >
            Request from team
        </button>
    </div>
    <p v-else-if="quota.attempts_remaining < 3" class="text-xs text-seal-muted">
        {{ quota.attempts_remaining }} AI attempt(s) left before you'll need to accept a draft or request one from our team.
    </p>
</template>

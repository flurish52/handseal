<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useConfirm } from '@/composables/useConfirm';

const props = defineProps({
    programmes: { type: Array, required: true },
});

const emit = defineEmits(['edit']);

const { confirm } = useConfirm();

const showArchived = ref(false);

const visibleProgrammes = computed(() =>
    props.programmes.filter((p) => showArchived.value || !p.is_archived)
);

function destroy(programme) {
    confirm({
        title: 'Remove programme?',
        message: `"${programme.name}" will be permanently deleted. This can't be undone.`,
        confirmLabel: 'Remove',
        variant: 'danger',
    }).then((ok) => {
        if (!ok) return;
        router.delete(route('programmes.destroy', programme.id), { preserveScroll: true });
    });
}

function archive(programme) {
    router.patch(route('programmes.archive', programme.id), {}, { preserveScroll: true });
}

function restore(programme) {
    router.patch(route('programmes.restore', programme.id), {}, { preserveScroll: true });
}
</script>

<template>
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-seal-ink">Programmes</p>
            <label class="flex items-center gap-1.5 text-xs text-seal-muted">
                <input type="checkbox" v-model="showArchived" class="rounded border-seal-line" />
                Show archived
            </label>
        </div>

        <div class="space-y-2">
            <div
                v-for="programme in visibleProgrammes"
                :key="programme.id"
                class="bg-white rounded-card border border-seal-line p-4 flex items-center justify-between"
                :class="{ 'opacity-60': programme.is_archived }"
            >
                <div>
                    <p class="text-sm font-medium text-seal-ink flex items-center gap-2">
                        {{ programme.name }}
                        <span
                            v-if="programme.is_archived"
                            class="text-[10px] uppercase tracking-wide bg-seal-line text-seal-muted px-1.5 py-0.5 rounded"
                        >
                            Archived
                        </span>
                    </p>
                    <p class="text-xs text-seal-muted mt-0.5">
                        <span v-if="programme.price">₦{{ programme.price }}</span>
                        <span v-if="programme.price && programme.typical_duration"> · </span>
                        <span v-if="programme.typical_duration">{{ programme.typical_duration }} weeks</span>
                    </p>
                </div>
                <div class="flex gap-3 text-xs font-medium">
                    <button v-if="!programme.is_archived" @click="emit('edit', programme)" class="text-seal-navy">Edit</button>
                    <button v-if="!programme.is_archived" @click="archive(programme)" class="text-seal-muted">Archive</button>
                    <button v-else @click="restore(programme)" class="text-seal-navy">Restore</button>
                    <button @click="destroy(programme)" class="text-seal-danger">Remove</button>
                </div>
            </div>

            <p v-if="visibleProgrammes.length === 0" class="text-sm text-seal-muted text-center py-8">
                No programmes yet. Add your first one above.
            </p>
        </div>
    </div>
</template>

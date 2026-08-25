<script setup>
import { ref, watch } from 'vue';
import NewProgrammeModal from '@/Components/Programme/NewProgrammeModal.vue';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    programmes: { type: Array, required: true },
    error: { type: String, default: '' },
});
const emit = defineEmits(['update:modelValue']);

const NEW_PROGRAMME_VALUE = '__new_programme__';
const localProgrammes = ref([...props.programmes]);
const showModal = ref(false);

watch(() => props.programmes, (list) => { localProgrammes.value = list; });

function onChange(event) {
    const value = event.target.value;
    if (value === NEW_PROGRAMME_VALUE) {
        event.target.value = props.modelValue ?? '';
        showModal.value = true;
        return;
    }
    emit('update:modelValue', value);
}

function onCreated(programme) {
    localProgrammes.value = [...localProgrammes.value, programme];
    emit('update:modelValue', programme.id);
    showModal.value = false;
}
</script>

<template>
    <div>
        <select :value="modelValue" @change="onChange"
                class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm text-seal-ink focus:outline-none focus:ring-2 focus:ring-seal-navy/40 focus:border-seal-navy">
            <option value="" disabled>Select a programme</option>
            <option v-for="p in localProgrammes" :key="p.id" :value="p.id">{{ p.name }}</option>
            <option :value="NEW_PROGRAMME_VALUE">+ Add new programme…</option>
        </select>
        <p v-if="error" class="text-xs text-seal-danger mt-1">{{ error }}</p>

        <NewProgrammeModal v-if="showModal" @close="showModal = false" @created="onCreated" />
    </div>
</template>

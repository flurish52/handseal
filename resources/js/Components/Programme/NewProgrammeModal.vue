<script setup>
import { ref } from 'vue';
import { useCreateProgramme } from '@/composables/useCreateProgramme.js';

const emit = defineEmits(['close', 'created']);

const name = ref('');
const price = ref('');
const duration = ref('');
const { creating, errors, createProgramme } = useCreateProgramme();

async function submit() {
    const programme = await createProgramme({
        name: name.value,
        price: price.value,
        typical_duration: duration.value,
    });
    if (programme) emit('created', programme);
}
</script>

<template>
    <div class="fixed inset-0 bg-black/50 z-50 flex items-end sm:items-center justify-center p-4" @click.self="emit('close')">
        <div class="bg-white rounded-card w-full max-w-sm p-4 space-y-3">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-seal-ink">New programme</p>
                <button @click="emit('close')" class="text-seal-muted text-lg leading-none">×</button>
            </div>

            <div>
                <input v-model="name" type="text" placeholder="Programme name" autofocus
                       class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy" />
                <p v-if="errors.name" class="text-xs text-seal-danger mt-1">{{ errors.name[0] }}</p>
            </div>

            <div>
                <input v-model="price" type="number" step="0.01" placeholder="Price (optional)"
                       class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy" />
                <p v-if="errors.price" class="text-xs text-seal-danger mt-1">{{ errors.price[0] }}</p>
            </div>

            <div>
                <input v-model="duration" type="text" placeholder="Typical duration in weeks (optional)"
                       class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy" />
                <p v-if="errors.typical_duration" class="text-xs text-seal-danger mt-1">{{ errors.typical_duration[0] }}</p>
            </div>

            <button @click="submit" :disabled="creating"
                    class="w-full bg-seal-navy text-white text-sm font-medium py-2.5 rounded-lg disabled:opacity-50">
                {{ creating ? 'Adding…' : 'Add programme' }}
            </button>
        </div>
    </div>
</template>

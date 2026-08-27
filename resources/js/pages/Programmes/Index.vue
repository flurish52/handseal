<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import ProgrammeList from './Partials/ProgrammeList.vue';

const props = defineProps({
    programmes: { type: Array, required: true },
});

const editingId = ref(null);

const form = useForm({
    name: '',
    price: '',
    typical_duration: '',
});

function submit() {
    if (editingId.value) {
        form.put(route('programmes.update', editingId.value), { onSuccess: resetForm });
    } else {
        form.post(route('programmes.store'), { onSuccess: resetForm });
    }
}

function edit(programme) {
    editingId.value = programme.id;
    form.name = programme.name;
    form.price = programme.price ?? '';
    form.typical_duration = programme.typical_duration ?? '';
}

function resetForm() {
    editingId.value = null;
    form.reset();
    form.clearErrors();
}
</script>

<template>
    <Head title="Programmes" />

    <div class="p-4 space-y-6">
        <h1 class="font-serif text-xl font-semibold text-seal-navy">Programmes</h1>

        <form @submit.prevent="submit" class="bg-white rounded-card border border-seal-line p-4 space-y-4">
            <p class="text-sm font-medium text-seal-ink">
                {{ editingId ? 'Edit programme' : 'Add a programme' }}
            </p>

            <p v-if="form.errors.programme" class="text-xs text-seal-danger bg-red-50 rounded-lg px-3 py-2">
                {{ form.errors.programme }}
            </p>

            <div>
                <label for="programme-name" class="block text-xs font-medium text-seal-ink mb-1">
                    Programme name
                </label>
                <input
                    id="programme-name"
                    v-model="form.name"
                    type="text"
                    placeholder="e.g. Bridal Makeup"
                    :aria-invalid="!!form.errors.name"
                    class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                />
                <p v-if="form.errors.name" class="text-xs text-seal-danger mt-1">{{ form.errors.name }}</p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="programme-price" class="block text-xs font-medium text-seal-ink mb-1">
                        Price
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-seal-muted">₦</span>
                        <input
                            id="programme-price"
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            placeholder="0.00"
                            :aria-invalid="!!form.errors.price"
                            class="w-full rounded-lg border border-seal-line pl-7 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                        />
                    </div>
                    <p v-if="form.errors.price" class="text-xs text-seal-danger mt-1">{{ form.errors.price }}</p>
                </div>

                <div>
                    <label for="programme-duration" class="block text-xs font-medium text-seal-ink mb-1">
                        Duration
                    </label>
                    <div class="relative">
                        <input
                            id="programme-duration"
                            v-model="form.typical_duration"
                            type="number"
                            min="1"
                            placeholder="6"
                            :aria-invalid="!!form.errors.typical_duration"
                            class="w-full rounded-lg border border-seal-line pl-3 pr-14 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                        />
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-seal-muted">weeks</span>
                    </div>
                    <p v-if="form.errors.typical_duration" class="text-xs text-seal-danger mt-1">{{ form.errors.typical_duration }}</p>
                </div>
            </div>

            <div class="flex gap-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-seal-navy text-white text-sm font-medium px-4 py-2 rounded-lg disabled:opacity-50"
                >
                    {{ editingId ? 'Save changes' : 'Add programme' }}
                </button>
                <button
                    v-if="editingId"
                    type="button"
                    @click="resetForm"
                    class="text-sm text-seal-muted px-4 py-2"
                >
                    Cancel
                </button>
            </div>
        </form>
        <ProgrammeList :programmes="props.programmes" @edit="edit" />
    </div>
</template>

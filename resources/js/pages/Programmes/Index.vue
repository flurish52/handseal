<script setup>
import { ref } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

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
        form.put(route('programmes.update', editingId.value), {
            onSuccess: resetForm,
        });
    } else {
        form.post(route('programmes.store'), {
            onSuccess: resetForm,
        });
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
}

function destroy(programme) {
    if (confirm(`Remove "${programme.name}"? This can't be undone.`)) {
        form.delete(route('programmes.destroy', programme.id));
    }
}
</script>

<template>
    <Head title="Programmes" />

        <div class="p-4 space-y-6">
            <h1 class="font-serif text-xl font-semibold text-seal-navy">Programmes</h1>

            <!-- Add / edit form -->
            <form @submit.prevent="submit" class="bg-white rounded-card border border-seal-line p-4 space-y-3">
                <p class="text-sm font-medium text-seal-ink">
                    {{ editingId ? 'Edit programme' : 'Add a programme' }}
                </p>

                <div>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Programme name (e.g. Bridal Makeup)"
                        class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                    />
                    <p v-if="form.errors.name" class="text-xs text-seal-danger mt-1">{{ form.errors.name }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <input
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            placeholder="Price (₦)"
                            class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                        />
                        <p v-if="form.errors.price" class="text-xs text-seal-danger mt-1">{{ form.errors.price }}</p>
                    </div>
                    <div>
                        <input
                            v-model="form.typical_duration"
                            type="text"
                            placeholder="Duration (e.g. 6 weeks)"
                            class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                        />
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

            <!-- List -->
            <div class="space-y-2">
                <div
                    v-for="programme in props.programmes"
                    :key="programme.id"
                    class="bg-white rounded-card border border-seal-line p-4 flex items-center justify-between"
                >
                    <div>
                        <p class="text-sm font-medium text-seal-ink">{{ programme.name }}</p>
                        <p class="text-xs text-seal-muted mt-0.5">
                            <span v-if="programme.price">₦{{ programme.price }}</span>
                            <span v-if="programme.price && programme.typical_duration"> · </span>
                            <span v-if="programme.typical_duration">{{ programme.typical_duration }} weeks</span>
                        </p>
                    </div>
                    <div class="flex gap-3 text-xs font-medium">
                        <button @click="edit(programme)" class="text-seal-navy">Edit</button>
                        <button @click="destroy(programme)" class="text-seal-danger">Remove</button>
                    </div>
                </div>

                <p v-if="props.programmes.length === 0" class="text-sm text-seal-muted text-center py-8">
                    No programmes yet. Add your first one above.
                </p>
            </div>
        </div>
</template>

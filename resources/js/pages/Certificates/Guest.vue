<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    programmes: { type: Array, required: true },
    builtins: { type: Array, required: true },
    customTemplates: { type: Array, required: true },
});

const form = useForm({
    recipient_name: '',
    programme_id: '',
    start_date: '',
    end_date: '',
    template_choice: '',
});

function templateParts() {
    const [type, value] = form.template_choice.split(':');
    return {
        builtin_template_key: type === 'builtin' ? value : null,
        certificate_template_id: type === 'custom' ? value : null,
    };
}

function preview() {
    const parts = templateParts();
    const params = new URLSearchParams({
        recipient_name: form.recipient_name,
        programme_id: form.programme_id,
        start_date: form.start_date,
        end_date: form.end_date,
        ...(parts.builtin_template_key ? { builtin_template_key: parts.builtin_template_key } : {}),
        ...(parts.certificate_template_id ? { certificate_template_id: parts.certificate_template_id } : {}),
    });

    window.open(route('certificates.guest.preview') + '?' + params.toString(), '_blank');
}

const NEW_PROGRAMME_VALUE = '__new_programme__';
function onProgrammeChange(event) {
    const value = event.target.value;

    if (value === NEW_PROGRAMME_VALUE) {
        event.target.value = form.programme_id ?? '';
        window.open(route('programmes.index'), '_blank', 'noopener,noreferrer');
        return;
    }

    form.programme_id = value;
}

function submit() {
    form.transform((data) => ({
        recipient_name: data.recipient_name,
        programme_id: data.programme_id,
        start_date: data.start_date,
        end_date: data.end_date,
        ...templateParts(),
    })).post(route('certificates.guest.store'));
}


</script>

<template>
    <Head title="Issue guest certificate" />

        <div class="p-4 space-y-4">
            <div>
                <h1 class="font-serif text-xl font-semibold text-seal-navy">Issue guest certificate</h1>
                <p class="text-xs text-seal-muted">For students not tracked in-app</p>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-card border border-seal-line p-4 space-y-3">
                <div>
                    <label class="text-xs text-seal-muted">Full name</label>
                    <input
                        v-model="form.recipient_name"
                        type="text"
                        placeholder="Type name…"
                        class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                    />
                    <p v-if="form.errors.recipient_name" class="text-xs text-seal-danger mt-1">{{ form.errors.recipient_name }}</p>
                </div>

                <div>
                    <label class="text-xs text-seal-muted">Programme</label>
                    <select
                        v-if="programmes.length"
                        :value="form.programme_id"
                        @change="onProgrammeChange"
                        class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm text-seal-ink focus:outline-none focus:ring-2 focus:ring-seal-navy/40 focus:border-seal-navy"
                    >
                        <option value="" disabled>Select a programme</option>
                        <option :value="NEW_PROGRAMME_VALUE">+ Add new programme…</option>
                        <option v-for="p in programmes" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <p v-if="form.errors.programme_id" class="text-xs text-seal-danger mt-1">{{ form.errors.programme_id }}</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-seal-muted">Start date</label>
                        <input
                            v-model="form.start_date"
                            type="date"
                            class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                        />
                        <p v-if="form.errors.start_date" class="text-xs text-seal-danger mt-1">{{ form.errors.start_date }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-seal-muted">Completion date</label>
                        <input
                            v-model="form.end_date"
                            type="date"
                            class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                        />
                        <p v-if="form.errors.end_date" class="text-xs text-seal-danger mt-1">{{ form.errors.end_date }}</p>
                    </div>
                </div>

                <div>
                    <label class="text-xs text-seal-muted">Template</label>
                    <select
                        v-model="form.template_choice"
                        class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                    >
                        <option value="" disabled>Select template…</option>
                        <optgroup label="Built-in">
                            <option v-for="b in props.builtins" :key="b.key" :value="`builtin:${b.key}`">
                                {{ b.label }}
                            </option>
                        </optgroup>
                        <optgroup label="Custom" v-if="props.customTemplates.length">
                            <option v-for="t in props.customTemplates" :key="t.id" :value="`custom:${t.id}`">
                                {{ t.name }}
                            </option>
                        </optgroup>
                    </select>
                    <p v-if="form.errors.template" class="text-xs text-seal-danger mt-1">{{ form.errors.template }}</p>
                </div>

                <div class="flex gap-2">
                    <button
                        type="button"
                        @click="preview"
                        :disabled="!form.template_choice"
                        class="flex-1 border border-seal-line text-seal-ink text-sm font-medium py-3 rounded-lg disabled:opacity-50"
                    >
                        Preview
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex-1 bg-seal-navy text-white text-sm font-medium py-3 rounded-lg disabled:opacity-50"
                    >
                        Generate certificate now
                    </button>
                </div>
                <p class="text-[11px] text-seal-muted text-center">
                    No tracking history needed, issued instantly
                </p>
            </form>

            <Link :href="route('certificates.index')" class="block text-center text-xs text-seal-muted">
                ← Back to certificates
            </Link>
        </div>
</template>

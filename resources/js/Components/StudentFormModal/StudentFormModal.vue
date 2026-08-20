<script setup>
import { computed, ref, watch, onMounted, onUnmounted } from 'vue';
import ProgrammeSelect from '@/Components/Programme/ProgrammeSelect.vue';
import {useForm} from "@inertiajs/vue3";

const props = defineProps({
    open: { type: Boolean, required: true },
    student: { type: Object, default: null },
    programmes: { type: Array, required: true },
});

const emit = defineEmits(['close']);

const form = useForm({
    programme_id: '',
    name: '',
    phone: '',
    start_at: '',
    end_at: '',
});

// Tracks whether the user has manually edited the end date directly —
// once true, we stop auto-calculating it from programme + start date.
const endDateTouched = ref(false);

const selectedProgramme = computed(() =>
    props.programmes.find((p) => String(p.id) === String(form.programme_id)) ?? null,
);

function todayStr() {
    return new Date().toISOString().slice(0, 10);
}

function addWeeks(dateStr, weeks) {
    const d = new Date(`${dateStr}T00:00:00`);
    d.setDate(d.getDate() + Number(weeks) * 7);
    return d.toISOString().slice(0, 10);
}

function recalcEndDate() {
    if (props.student) return; // don't touch dates in edit mode
    if (endDateTouched.value) return; // user already overrode it
    const programme = selectedProgramme.value;
    if (!programme?.typical_duration || !form.start_at) return;
    form.end_at = addWeeks(form.start_at, programme.typical_duration);
}

watch(
    () => props.open,
    (isOpen) => {
        if (!isOpen) return;

        if (props.student) {
            form.programme_id = props.student.programme_id;
            form.name = props.student.name;
            form.phone = props.student.phone ?? '';
            form.start_at = props.student.start_at?.slice(0, 10) ?? '';
            form.end_at = props.student.end_at?.slice(0, 10) ?? '';
        } else {
            form.reset();
            form.clearErrors();
        }
        endDateTouched.value = false;
    },
);

// Recalculate end date whenever the start date changes (add mode only).
watch(
    () => form.start_at,
    () => {
        if (!props.open) return;
        recalcEndDate();
    },
);

// Recalculate end date (and default the start date) whenever the
// programme changes — covers both picking an existing one and
// picking one just created via ProgrammeSelect's inline modal.
watch(
    () => form.programme_id,
    () => {
        if (!props.open || props.student) return;
        if (!form.start_at) form.start_at = todayStr();
        recalcEndDate();
    },
);

function close() {
    if (form.processing) return;
    emit('close');
}

function submit() {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.clearErrors();
            emit('close');
        },
    };

    if (props.student) {
        form.put(route('students.update', props.student.id), options);
    } else {
        form.post(route('students.store'), options);
    }
}

function onKeydown(e) {
    if (e.key === 'Escape' && props.open) close();
}

onMounted(() => document.addEventListener('keydown', onKeydown));
onUnmounted(() => document.removeEventListener('keydown', onKeydown));
</script>

<template>
    <Transition name="modal-fade">
        <div
            v-if="open"
            class="fixed inset-0 z-50 flex items-center justify-center bg-seal-navy/40 backdrop-blur-[2px] p-4"
            @click.self="close"
        >
            <div class="bg-white rounded-card border border-seal-line shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 pt-5 pb-4 border-b border-seal-line">
                    <h2 class="font-serif text-lg font-semibold text-seal-navy">
                        {{ student ? 'Edit student' : 'Add student' }}
                    </h2>
                    <button
                        type="button"
                        @click="close"
                        aria-label="Close"
                        class="text-seal-muted hover:text-seal-ink transition-colors rounded-full p-1 hover:bg-seal-line/40"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <path d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submit" class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-seal-muted mb-1">Full name</label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="e.g. Amaka Obi"
                            class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm text-seal-ink focus:outline-none focus:ring-2 focus:ring-seal-navy/40 focus:border-seal-navy"
                        />
                        <p v-if="form.errors.name" class="text-xs text-seal-danger mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div v-if="student">
                            <label class="block text-xs font-medium text-seal-muted mb-1">Enrollment no.</label>
                            <input
                                :value="student?.enrollment_number"
                                type="text"
                                readonly
                                class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm text-seal-muted bg-seal-paper cursor-not-allowed"
                            />
                        </div>
                        <div :class="student ? '' : 'col-span-2'">
                            <label class="block text-xs font-medium text-seal-muted mb-1">Phone</label>
                            <input
                                v-model="form.phone"
                                type="text"
                                placeholder="Optional"
                                class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm text-seal-ink focus:outline-none focus:ring-2 focus:ring-seal-navy/40 focus:border-seal-navy"
                            />
                            <p v-if="form.errors.phone" class="text-xs text-seal-danger mt-1">{{ form.errors.phone }}</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-seal-muted mb-1">Programme</label>
                        <ProgrammeSelect
                            v-model="form.programme_id"
                            :programmes="programmes"
                            :error="form.errors.programme_id"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-seal-muted mb-1">Start date</label>
                            <input
                                v-model="form.start_at"
                                type="date"
                                class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm text-seal-ink focus:outline-none focus:ring-2 focus:ring-seal-navy/40 focus:border-seal-navy"
                            />
                            <p v-if="form.errors.start_at" class="text-xs text-seal-danger mt-1">{{ form.errors.start_at }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-seal-muted mb-1">End date</label>
                            <input
                                v-model="form.end_at"
                                type="date"
                                @input="endDateTouched = true"
                                class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm text-seal-ink focus:outline-none focus:ring-2 focus:ring-seal-navy/40 focus:border-seal-navy"
                            />
                            <p v-if="form.errors.end_at" class="text-xs text-seal-danger mt-1">{{ form.errors.end_at }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-seal-line -mx-6 px-6 pt-4">
                        <button
                            type="button"
                            @click="close"
                            class="text-sm font-medium text-seal-muted px-4 py-2 rounded-lg hover:bg-seal-line/40 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-seal-navy text-white text-sm font-medium px-5 py-2 rounded-lg disabled:opacity-50 hover:bg-seal-navy/90 transition-colors"
                        >
                            {{ form.processing ? 'Saving…' : (student ? 'Save changes' : 'Add student') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.15s ease;
}
.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}
</style>

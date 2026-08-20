<template>
    <div class="fixed inset-0 z-30 flex items-center justify-center bg-black/40 p-4" @click.self="close">
        <div class="w-full max-w-sm bg-white rounded-card p-5 space-y-4">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-seal-ink">
                    {{ student.latest_certificate ? 'Reissue certificate' : 'Issue certificate' }}
                </p>
                <button @click="close" class="text-seal-muted">
                    <Icon name="close" :size="18" />
                </button>
            </div>

            <div class="rounded-lg bg-seal-paper px-3 py-2">
                <p class="text-sm text-seal-ink">{{ student.name }}</p>
            </div>

            <div
                v-if="student.latest_certificate"
                class="rounded-lg bg-seal-brass/10 border border-seal-brass/30 px-3 py-2.5 text-xs text-seal-ink space-y-1"
            >
                <p class="font-medium">This student already has a certificate</p>
                <p class="text-seal-muted font-mono">{{ student.latest_certificate.certificate_number }}</p>
                <p class="text-seal-muted">
                    Issuing a new one replaces it — only the newest certificate number will be valid on download. The previous record stays in your history, but won't be reissued again automatically.
                </p>
            </div>

            <div>
                <label class="block text-xs font-medium text-seal-muted mb-1">Template</label>
                <select
                    v-model="form.template_choice"
                    class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                >
                    <option value="" disabled>Select a template</option>
                    <optgroup label="Built-in">
                        <option v-for="b in builtins" :key="b.key" :value="`builtin:${b.key}`">
                            {{ b.label }}
                        </option>
                    </optgroup>
                    <optgroup label="Custom" v-if="customTemplates.length">
                        <option v-for="t in customTemplates" :key="t.id" :value="`custom:${t.id}`">
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
                    class="flex-1 border border-seal-line text-seal-ink text-sm font-medium px-4 py-2 rounded-lg disabled:opacity-50"
                >
                    Preview
                </button>
                <button
                    @click="submit"
                    :disabled="form.processing || !form.template_choice"
                    class="flex-1 bg-seal-navy text-white text-sm font-medium px-4 py-2 rounded-lg disabled:opacity-50"
                >
                    {{ form.processing ? 'Issuing…' : (student.latest_certificate ? 'Reissue' : 'Issue certificate') }}
                </button>
            </div>
        </div>
    </div>

    <PaywallModal
        v-if="paywall"
        :reason="paywall?.reason"
        :message="paywall?.message"
        :student-id="student.id"
        :builtin-template-key="templateParts().builtin_template_key"
        :certificate-template-id="templateParts().certificate_template_id"
        @close="paywall = null"
    />
    </template>

    <script setup>
        import { ref } from 'vue';
        import { useForm, usePage } from '@inertiajs/vue3';
        import Icon from '@/Components/Icons/Icon.vue';
        import PaywallModal from '@/Components/PaywallModal.vue';

        const props = defineProps({
            student: { type: Object, required: true },
            builtins: { type: Array, required: true },
            customTemplates: { type: Array, required: true },
        });

        const emit = defineEmits(['close', 'issued']);

        const page = usePage();
        const paywall = ref(null);


        const form = useForm({
            student_id: props.student.id,
            template_choice: '',
        });

        function close() {
            emit('close');
        }

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
                student_id: props.student.id,
                ...(parts.builtin_template_key ? { builtin_template_key: parts.builtin_template_key } : {}),
                ...(parts.certificate_template_id ? { certificate_template_id: parts.certificate_template_id } : {}),
            });

            window.open(route('certificates.preview') + '?' + params.toString(), '_blank');
        }

        function submit() {
            if (props.student.latest_certificate) {
                const ok = confirm(
                    `${props.student.name} already has certificate ${props.student.latest_certificate.certificate_number}. Issuing a new one makes this the only valid certificate going forward. Continue?`
                );
                if (!ok) return;
            }

            form.transform((data) => ({
                student_id: data.student_id,
                ...templateParts(),
            })).post(route('certificates.store'), {
                preserveScroll: true,
                onSuccess: () => {
                    const reason = page.props.flash?.paywall;

                    if (reason) {
                        paywall.value = {
                            reason,
                            message: page.props.flash?.error ?? 'Payment required to continue.',
                        };
                        return; // stay mounted, don't emit('issued')
                    }

                    paywall.value = null;
                    emit('issued');
                },
            });
        }
    </script>

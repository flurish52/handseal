<template>
    <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-sm bg-white rounded-card p-5 space-y-4">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-seal-ink">
                    {{ reason === 'needs_onboarding' ? 'One-time activation' : 'Free certificates used up' }}
                </p>
                <button @click="$emit('close')" class="text-seal-muted">
                    <Icon name="close" :size="18" />
                </button>
            </div>

            <p class="text-sm text-seal-ink">{{ message }}</p>

            <!-- Needs onboarding fee -->

          <a  v-if="reason === 'needs_onboarding'"
            :href="route('payments.onboarding')"
            class="block w-full text-center bg-seal-navy text-white text-sm font-medium px-4 py-2.5 rounded-lg"
            >
            Pay ₦2,000 to activate
            </a>

            <!-- Needs per-cert or subscription -->
            <div v-else class="space-y-2">
                <button @click="payCertificate" :disabled="payingCert"
                        class="block w-full text-center border border-seal-line text-seal-ink text-sm font-medium px-4 py-2.5 rounded-lg"
                >
                    {{ payingCert ? 'Redirecting…' : 'Pay ₦200 for this certificate' }}
                </button>
                <button @click="paySubscription" :disabled="payingSub"
                        class="block w-full text-center bg-seal-navy text-white text-sm font-medium px-4 py-2.5 rounded-lg"
                >
                    {{ payingSub ? 'Redirecting…' : 'Unlimited Certificates — ₦1000/mo (Best Value)' }}
                </button>
                <p class="text-center text-xs text-seal-ink/60">
                    Pay for 5, get unlimited.
                </p>
            </div>
            <div class="mt-3">
                <LegalLinks prefix="By subscribing, you agree to our" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Icon from '@/Components/Icons/Icon.vue';
import LegalLinks from "@/Components/LegalLinks.vue";

const props = defineProps({
    reason: { type: String, required: true },
    message: { type: String, required: true },
    studentId: { type: [Number, String], default: null },
    builtinTemplateKey: { type: String, default: null },
    certificateTemplateId: { type: [Number, String], default: null },
});

defineEmits(['close']);

const payingCert = ref(false);
const payingSub = ref(false);

function payCertificate() {
    payingCert.value = true;
    router.post(route('payments.certificate'), {
        student_id: props.studentId,
        builtin_template_key: props.builtinTemplateKey,
        certificate_template_id: props.certificateTemplateId,
    }, { onFinish: () => (payingCert.value = false) });
}

function paySubscription() {
    payingSub.value = true;
    router.post(route('payments.subscribe'), {}, { onFinish: () => (payingSub.value = false) });
}
</script>

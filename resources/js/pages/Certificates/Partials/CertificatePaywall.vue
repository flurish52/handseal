<script setup>
import { computed, ref } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import PaywallModal from '@/Components/PaywallModal.vue';

const props = defineProps({
    reason: { type: String, required: true },
    message: { type: String, required: true },
    studentId: { type: [Number, String], default: null },
    recipientName: { type: String, default: null },
    programmeId: { type: [Number, String], default: null },
    startDate: { type: String, default: null },
    endDate: { type: String, default: null },
    builtinTemplateKey: { type: String, default: null },
    certificateTemplateId: { type: [Number, String], default: null },
});
const emit = defineEmits(['close']);

const page = usePage();
const billing = computed(() => page.props.auth?.billing);

const naira = (kobo) => (kobo / 100).toLocaleString();
const walletSufficient = computed(() =>
    billing.value && billing.value.wallet_balance_kobo >= billing.value.payg_price_kobo
);

const payingCert = ref(false);
const payingWallet = ref(false);

function payload() {
    return {
        student_id: props.studentId,
        recipient_name: props.recipientName,
        programme_id: props.programmeId,
        start_date: props.startDate,
        end_date: props.endDate,
        builtin_template_key: props.builtinTemplateKey,
        certificate_template_id: props.certificateTemplateId,
    };
}

// Card/bank via Paystack. Redirects out, lands back via payments.callback.
function payCertificate() {
    payingCert.value = true;
    router.post(route('payments.certificate'), payload(), {
        preserveScroll: true,
        onFinish: () => {
            payingCert.value = false;
        },
        onSuccess: () => {
            router.reload({
                preserveScroll: true,
                onSuccess: () => emit('close'),
            });
        }
    });
}


// Wallet balance. Separate route now (no more shared `source` field) —
// deducts immediately server-side, issues the cert, redirects straight back
// with the same success/download_url flash shape as the Paystack path.
function payFromWallet() {
    payingWallet.value = true;
    router.post(route('payments.certificate.wallet'), payload(), {
        onFinish: () => (payingWallet.value = false),
        onSuccess: () => emit('close'),
    });
}
const actions = computed(() => {
    const certPrice = billing.value ? naira(billing.value.payg_price_kobo) : '200';
    const list = [];

    if (walletSufficient.value) {
        list.push({
            key: 'wallet',
            label: `Pay ₦${certPrice} from wallet (₦${naira(billing.value.wallet_balance_kobo)} available)`,
            style: 'primary',
            loading: payingWallet.value,
            onClick: payFromWallet,
        });
        list.push({
            key: 'per_cert',
            label: `Pay ₦${certPrice} for this certificate`,
            style: 'secondary',
            loading: payingCert.value,
            onClick: payCertificate,
        });
    } else {
        list.push({
            key: 'per_cert',
            label: `Pay ₦${certPrice} for this certificate`,
            style: 'primary',
            loading: payingCert.value,
            onClick: payCertificate,
        });
    }

    list.push({ key: 'plans', label: 'View plans', style: 'secondary', href: route('billing.index') });

    return list;
});
</script>

<template>
    <PaywallModal
        title="Payment required"
        :message="message"
        hint="Or subscribe to a plan for certificates included every month."
        show-legal
        :actions="actions"
    />
</template>

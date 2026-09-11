<script setup>
import { computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import PaywallModal from '@/Components/PaywallModal.vue';

const props = defineProps({
    template: { type: Object, required: true }, // { id, name, price, price_naira }
    walletBalanceKobo: { type: Number, required: true },
});
const emit = defineEmits(['close']);

const canUseWallet = computed(() => props.walletBalanceKobo >= props.template.price);
const payingWallet = ref(false);
const payingCard = ref(false);

function payFromWallet() {
    payingWallet.value = true;
    router.post(route('payments.template-purchase-wallet', props.template.id), {}, {
        onSuccess: () => emit('close'),
        onFinish: () => (payingWallet.value = false),
    });
}

function payWithCard() {
    payingCard.value = true;
    router.post(route('payments.template-purchase', props.template.id), {}, {
        onSuccess: () => emit('close'),
        onFinish: () => (payingCard.value = false),
    });
}
const actions = computed(() => [
    {
        key: 'wallet',
        label: canUseWallet.value ? `Pay ₦${props.template.price_naira} from wallet` : 'Insufficient wallet balance',
        style: canUseWallet.value ? 'secondary' : 'disabled',
        disabled: ! canUseWallet.value,
        loading: payingWallet.value,
        onClick: canUseWallet.value ? payFromWallet : null,
    },
    {
        key: 'card',
        label: `Pay ₦${props.template.price_naira} with card`,
        style: 'primary',
        loading: payingCard.value,
        onClick: payWithCard,
    },
]);
</script>

<template>
    <PaywallModal
        :title="`Buy \u201C${template.name}\u201D`"
        :message="canUseWallet ? 'Choose how to pay.' : `Your wallet balance (₦${(walletBalanceKobo / 100).toFixed(0)}) doesn't cover this template — pay by card instead, or top up your wallet first.`"
        :actions="actions"
        @close="$emit('close')"
    />
</template>

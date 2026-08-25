<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Icon from '@/Components/Icons/Icon.vue';

const page = usePage();
const billing = computed(() => page.props.auth?.billing);

const naira = (kobo) => (kobo / 100).toLocaleString();

const banner = computed(() => {
    const b = billing.value;
    if (!b) return null;

    switch (b.state) {
        case 'quota_exhausted_overage':
            return `You've used all your ${b.plan_name} plan certificates this period — ₦${naira(b.extra_cert_price_kobo)} will be deducted from your wallet for this certificate.`;
        case 'quota_exhausted_no_overage':
            return `You've used all your included certificates on the ${b.plan_name} plan. Upgrade to issue more.`;
        case 'no_subscription':
            if (b.wallet_balance_kobo >= b.payg_price_kobo) {
                return `No active subscription — ₦${naira(b.payg_price_kobo)} will be deducted from your wallet for this certificate.`;
            }
            return null;
        default:
            return null;
    }
});
</script>

<template>
    <div
        v-if="banner"
        class="rounded-lg bg-seal-brass/10 border border-seal-brass/30 text-seal-ink text-xs px-3 py-2 flex items-center gap-2"
    >
        <Icon name="info" :size="14" class="shrink-0 text-seal-brass" />
        <span>{{ banner }}</span>
    </div>
</template>

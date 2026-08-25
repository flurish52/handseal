<script setup>
import { computed, ref } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import Icon from '@/Components/Icons/Icon.vue';

const page = usePage();
const billing = computed(() => page.props.auth?.billing);
const sub = computed(() => billing.value?.subscription ?? null);
const open = ref(false);

const naira = (kobo) => '₦' + (kobo / 100).toLocaleString();

const formattedEndsAt = computed(() => {
    if (!sub.value) return null;
    return new Date(sub.value.current_period_ends_at)
        .toLocaleDateString(undefined, { day: 'numeric', month: 'short' });
});

const quotaLabel = computed(() => {
    if (!sub.value) return null;
    return sub.value.is_unlimited ? 'Unlimited' : `${sub.value.certs_used}/${sub.value.included_certs}`;
});

const walletLabel = computed(() => billing.value ? naira(billing.value.wallet_balance_kobo) : null);
</script>

<template>
    <div v-if="billing" class="relative">
        <button
            type="button"
            @click="open = !open"
            class="flex items-center gap-2 rounded-full bg-white/10 px-2.5 py-1 text-xs font-medium text-white"
        >
            <span class="flex items-center gap-1">
                <Icon name="award" :size="13" />
                {{ quotaLabel }}
            </span>
            <span class="w-px h-3 bg-white/25"></span>
            <span class="flex items-center gap-1">
                <Icon name="wallet" :size="13" />
                {{ walletLabel }}
            </span>
        </button>

        <div v-if="open" class="fixed inset-0 z-10" @click="open = false"></div>

        <div
            v-if="open"
            class="absolute right-0 mt-2 w-60 rounded-lg bg-white text-seal-ink shadow-lg border border-seal-line z-20 p-3 space-y-2"
        >
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium">{{ sub.plan_name }} plan</span>
                <span v-if="sub.is_unlimited" class="text-[10px] font-semibold text-seal-sage">Unlimited</span>
                <span v-else-if="sub.is_free" class="text-[10px] font-semibold text-seal-muted">Free</span>
            </div>
            <p v-if="!sub.is_unlimited" class="text-xs text-seal-muted">
                {{ sub.certs_used }} / {{ sub.included_certs }} certificates used this {{ sub.is_free ? 'month' : 'period' }}
            </p>
            <p class="text-xs text-seal-muted">Renews {{ formattedEndsAt }}</p>

            <div class="pt-2 border-t border-seal-line flex items-center justify-between">
                <span class="text-xs text-seal-muted">Wallet balance</span>
                <span class="text-sm font-semibold">{{ naira(billing.wallet_balance_kobo) }}</span>
            </div>

            <Link :href="route('billing.index')" class="block text-center text-xs text-seal-navy underline pt-1">
                Manage billing
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Icon from '@/Components/Icons/Icon.vue';
import FundWalletCard from "@/Components/FundWalletCard.vue";

const props = defineProps({
    subscription: { type: Object, required: true }, // same shape as shared `billing.subscription`
    wallet: { type: Object, required: true },        // { balance_kobo, payg_price_kobo }
    plans: { type: Array, required: true },          // Plan::active()->get()
    transactions: { type: Array, required: true },   // recent wallet_transactions, newest first
});

const naira = (kobo) => '₦' + (kobo / 100).toLocaleString();

const usagePct = computed(() => {
    if (props.subscription.is_unlimited) return 100;
    if (!props.subscription.included_certs) return 0;
    return Math.min(100, Math.round((props.subscription.certs_used / props.subscription.included_certs) * 100));
});

const formattedEndsAt = computed(() =>
    new Date(props.subscription.current_period_ends_at)
        .toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' })
);

const switching = ref(null); // plan slug currently being switched to

function switchPlan(slug) {
    switching.value = slug;
    router.post(route('payments.subscribe', slug), {}, {
        onFinish: () => (switching.value = null),
    });
}

function cancelSubscription() {
    if (!confirm('Cancel your subscription? You\'ll keep access until the end of your current billing period, then drop to the Free plan.')) return;
    router.post(route('subscriptions.cancel'));
}

const txnMeta = {
    cert_issued: { label: 'Certificate issued', icon: 'award' },
    plan_overage: { label: 'Plan overage charge', icon: 'award' },
    custom_cert_fee: { label: 'Custom template fee', icon: 'sparkles' },
    subscription_renewal: { label: 'Subscription renewal', icon: 'refresh' },
    topup: { label: 'Wallet top-up', icon: 'plus' },
    guest_purchase: { label: 'Guest certificate', icon: 'award' },
    referral_reward: { label: 'Referral reward', icon: 'gift' },
    rollover_expire: { label: 'Rollover expired', icon: 'clock' },
    cert_issue_failed: { label: 'Refund — issue failed', icon: 'refresh' },
};

function describeTxn(t) {
    return txnMeta[t.reason]?.label ?? t.reason;
}
function iconFor(t) {
    return txnMeta[t.reason]?.icon ?? 'wallet';
}
</script>

<template>
    <Head title="Billing" />


        <div class="p-4 space-y-6">
            <h1 class="font-serif text-xl font-semibold text-seal-navy">Billing & wallet</h1>

            <!-- Current plan -->
            <div class="bg-white rounded-card border border-seal-line p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-seal-muted">Current plan</p>
                        <p class="font-serif text-lg font-semibold text-seal-navy">{{ subscription.plan_name }}</p>
                    </div>
                    <span
                        v-if="subscription.is_unlimited"
                        class="text-[10px] font-semibold text-seal-sage bg-seal-sage/10 px-2 py-1 rounded-full"
                    >
                        Unlimited
                    </span>
                    <span
                        v-else-if="subscription.is_free"
                        class="text-[10px] font-semibold text-seal-muted bg-seal-paper px-2 py-1 rounded-full"
                    >
                        Free
                    </span>
                </div>

                <div v-if="!subscription.is_unlimited" class="space-y-1.5">
                    <div class="flex items-center justify-between text-xs text-seal-muted">
                        <span>{{ subscription.certs_used }} / {{ subscription.included_certs }} certificates used</span>
                        <span>{{ subscription.remaining }} left</span>
                    </div>
                    <div class="h-1.5 rounded-full bg-seal-paper overflow-hidden">
                        <div
                            class="h-full rounded-full transition-all"
                            :class="usagePct >= 100 ? 'bg-seal-danger' : 'bg-seal-navy'"
                            :style="{ width: usagePct + '%' }"
                        ></div>
                    </div>
                    <p v-if="subscription.remaining === 0 && subscription.extra_cert_price_kobo" class="text-xs text-seal-brass">
                        Extra certificates are {{ naira(subscription.extra_cert_price_kobo) }} each, drawn from your wallet.
                    </p>
                    <p v-else-if="subscription.remaining === 0" class="text-xs text-seal-danger">
                        Quota used up this period — upgrade to issue more.
                    </p>
                </div>

                <p class="text-xs text-seal-muted">
                    {{ subscription.is_free ? 'Resets' : 'Renews' }} {{ formattedEndsAt }}
                </p>

<!--                <button-->
<!--                    v-if="!subscription.is_free"-->
<!--                    type="button"-->
<!--                    @click="cancelSubscription"-->
<!--                    class="text-xs text-seal-danger underline"-->
<!--                >-->
<!--                    Cancel subscription-->
<!--                </button>-->
            </div>

            <!-- Wallet -->
            <div class="bg-white rounded-card border border-seal-line p-4 space-y-3">
                <p class="text-xs text-seal-muted">Wallet balance</p>
                <p class="text-3xl font-serif font-semibold text-seal-navy">{{ naira(wallet.balance_kobo) }}</p>
                <p class="text-xs text-seal-muted">
                    Used automatically for pay-as-you-go certificates ({{ naira(wallet.payg_price_kobo) }} each without a plan) and plan overage charges.
                </p>
            </div>

            <FundWalletCard />


            <!-- Plans -->
            <div class="space-y-3">
                <p class="text-sm font-medium text-seal-ink">Plans</p>
                <div class="space-y-2">
                    <div
                        v-for="plan in plans"
                        :key="plan.slug"
                        class="bg-white rounded-card border p-4 flex items-center justify-between gap-3"
                        :class="plan.slug === subscription.plan_slug ? 'border-seal-navy ring-1 ring-seal-navy' : 'border-seal-line'"
                    >
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-seal-ink">{{ plan.name }}</p>
                                <span v-if="plan.slug === subscription.plan_slug" class="text-[10px] font-semibold text-seal-navy">Current</span>
                            </div>
                            <p class="text-xs text-seal-muted mt-0.5">
                                {{ plan.included_certs ? `${plan.included_certs} certificates/mo` : 'Unlimited certificates' }}
                                <span v-if="plan.price > 0"> · {{ naira(plan.price) }}/mo</span>
                                <span v-else> · Free</span>
                            </p>
                        </div>
                        <button
                            v-if="plan.slug === 'free'"
                            type="button"
                            class="shrink-0 text-xs font-medium border border-seal-line px-3 py-1.5 rounded-lg text-seal-navy disabled:opacity-50"
                        >
                            Free
                        </button>
                        <button
                            v-else-if="plan.slug !== subscription.plan_slug"
                            type="button"
                            :disabled="switching === plan.slug"
                            @click="switchPlan(plan.slug)"
                            class="shrink-0 text-xs font-medium border border-seal-line px-3 py-1.5 rounded-lg text-seal-navy disabled:opacity-50"
                        >
                            {{ switching === plan.slug ? 'Switching…' : 'Switch' }}
                        </button>
                        <button
                            v-else
                            type="button"
                            class="shrink-0 text-xs font-medium border border-seal-line px-3 py-1.5 rounded-lg text-seal-navy disabled:opacity-50"
                        >
                            Active
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent activity -->
            <div class="space-y-2">
                <p class="text-sm font-medium text-seal-ink">Recent wallet activity</p>
                <div v-if="!transactions.length" class="text-xs text-seal-muted">No activity yet.</div>
                <div v-else class="bg-white rounded-card border border-seal-line divide-y divide-seal-line">
                    <div v-for="t in transactions" :key="t.id" class="flex items-center gap-3 px-4 py-3">
                        <div class="shrink-0 w-8 h-8 rounded-full bg-seal-paper flex items-center justify-center">
                            <Icon :name="iconFor(t)" :size="14" class="text-seal-muted" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-seal-ink truncate">{{ describeTxn(t) }}</p>
                            <p class="text-xs text-seal-muted">{{ new Date(t.created_at).toLocaleDateString(undefined, { day: 'numeric', month: 'short' }) }}</p>
                        </div>
                        <p class="text-sm font-medium" :class="t.type === 'credit' ? 'text-seal-sage' : 'text-seal-ink'">
                            {{ t.type === 'credit' ? '+' : '−' }}{{ naira(t.amount) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
</template>

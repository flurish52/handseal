<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    referralCode: { type: String, required: true },
    referralLink: { type: String, required: true },
    referrerEligible: { type: Boolean, required: true },
    referrals: { type: Array, required: true },
});

const copied = ref(false);
const requesting = ref(null);
const isActivating = ref(false)

function copyLink() {
    navigator.clipboard.writeText(props.referralLink);
    copied.value = true;
    setTimeout(() => (copied.value = false), 1500);
}

function requestPayout(referral) {
    requesting.value = referral.id;
    router.post(route('referrals.request-payout', referral.id), {}, {
        preserveScroll: true,
        onFinish: () => (requesting.value = null),
    });
}

// A referral can only show the "Request payout" button when both are true:
// the referred business has actually paid, AND the referrer themselves is
// eligible (has made their own onboarding payment). Referring always works
// regardless of either flag — this only gates the payout request itself.
function canRequestPayout(referral) {
    return referral.eligible && props.referrerEligible;
}

function statusLabel(referral) {
    if (referral.status === 'paid') return 'paid';
    if (referral.status === 'requested') return 'requested';

    // status === 'pending' from here on
    if (referral.eligible && !props.referrerEligible) return 'locked — finish your payment';
    return 'awaiting payment';
}

function statusClass(referral) {
    if (referral.status === 'paid') return 'bg-seal-sage/15 text-seal-sage';
    if (referral.status === 'requested') return 'bg-seal-navy/10 text-seal-navy';
    if (referral.eligible && !props.referrerEligible) return 'bg-seal-brass/15 text-seal-brass';
    return 'bg-seal-muted/15 text-seal-muted';
}

function activateAccount() {
    if (isActivating.value) return
    isActivating.value = true

    router.visit(route('payments.onboarding'), {
        onFinish: () => {
            isActivating.value = false
        },
    })
}
</script>

<template>
    <Head title="Referrals" />
     <div class="p-4 space-y-6">
            <h1 class="font-serif text-xl font-semibold text-seal-navy">Referrals</h1>

            <div class="bg-white rounded-card border border-seal-line p-4 space-y-3">
                <p class="text-sm text-seal-ink">
                    Earn <b class="text-seal-brass">25%</b> of any business's first payment when they sign up through your link.
                </p>

                <div class="flex gap-2">
                    <input
                        readonly
                        :value="referralLink"
                        class="flex-1 rounded-lg border border-seal-line px-3 py-2 text-xs font-mono bg-seal-paper"
                    />
                    <button
                        @click="copyLink"
                        class="bg-seal-navy text-white text-xs font-medium px-3 py-2 rounded-lg whitespace-nowrap"
                    >
                        {{ copied ? 'Copied!' : 'Copy' }}
                    </button>
                </div>
            </div>

            <div v-if="!referrerEligible" class="bg-seal-brass/10 border border-seal-brass/30 rounded-card p-4 flex gap-3">
                <div class="bg-seal-brass/10 border border-seal-brass/30 rounded-card p-4 flex items-start gap-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-seal-brass shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-seal-ink">Activate your account to unlock payouts</p>
                        <p class="text-xs text-seal-muted mt-1">
                            Your link is already live and earning you credit — activating just unlocks the ability to request payouts. Complete your onboarding payment to activate.
                        </p>
                        <button
                            @click="activateAccount"
                            :disabled="isActivating"
                            class="mt-3 bg-seal-brass text-white text-xs font-medium px-4 py-2 rounded-lg hover:bg-seal-brass/90 transition-colors disabled:opacity-70 disabled:cursor-not-allowed flex items-center gap-2"
                        >
                            <svg
                                v-if="isActivating"
                                class="animate-spin h-3.5 w-3.5"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                            </svg>
                            {{ isActivating ? 'Activating…' : 'Activate account' }}
                        </button>
                    </div>
                </div>
            </div>

            <div>
                <p class="text-sm font-medium text-seal-ink mb-2">Your referrals</p>
                <div class="space-y-2">
                    <div
                        v-for="r in props.referrals"
                        :key="r.id"
                        class="bg-white rounded-card border border-seal-line p-4 flex items-center justify-between gap-3"
                    >
                        <p class="text-sm text-seal-ink">{{ r.referred_name }}</p>

                        <button
                            v-if="canRequestPayout(r)"
                            @click="requestPayout(r)"
                            :disabled="requesting === r.id"
                            class="text-xs font-medium px-3 py-1.5 rounded-lg bg-seal-brass text-white disabled:opacity-50"
                        >
                            {{ requesting === r.id ? 'Requesting…' : 'Request payout' }}
                        </button>

                        <span
                            v-else
                            class="text-[10px] font-mono uppercase px-2 py-0.5 rounded"
                            :class="statusClass(r)"
                        >
                            {{ statusLabel(r) }}
                        </span>
                    </div>

                    <p v-if="props.referrals.length === 0" class="text-sm text-seal-muted text-center py-8">
                        No referrals yet — share your link above.
                    </p>
                </div>
            </div>
        </div>
</template>

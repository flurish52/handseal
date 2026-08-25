<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    canRegister: { type: Boolean, default: true },
    plans: { type: Array, required: true }, // Plan::active()->get()
    payg_price_naira: { type: String, required: true }, // e.g. "₦200"
});

const freePlan = computed(() => props.plans.find(p => p.slug === 'free'));
const paidPlans = computed(() => props.plans.filter(p => p.slug !== 'free'));

const Check = 'M4 10l4 4 8-8';
</script>

<template>
    <section id="pricing" class="bg-seal-paper px-6 py-24">
        <div class="mx-auto max-w-6xl">
            <div class="mb-14 max-w-xl">
                <span class="mb-3.5 block font-mono text-xs uppercase tracking-[.14em] text-seal-brass-dim">Pricing</span>
                <h2 class="mb-3.5 font-serif text-[clamp(28px,3.4vw,38px)] font-semibold leading-tight tracking-tight text-seal-ink">
                    Try it free. Scale when you're ready.
                </h2>
                <p class="text-base leading-relaxed text-seal-muted">
                    No card required to sign up, add trainees and issue test certificates first. Then pick
                    what fits — a monthly plan with certificates included, or pay only for what you issue.
                </p>
            </div>

            <!-- Plan cards -->
            <div class="grid gap-6 lg:grid-cols-4">
                <div
                    v-for="plan in plans"
                    :key="plan.slug"
                    class="relative flex flex-col rounded-2xl border p-7"
                    :class="plan.slug === 'growth'
                        ? 'border-seal-brass bg-seal-paper shadow-[0_20px_50px_-25px_rgba(199,154,70,.4)]'
                        : 'border-seal-line bg-seal-paper'"
                >
                    <span
                        v-if="plan.slug === 'growth'"
                        class="absolute -top-3 left-7 rounded-full bg-seal-navy px-3 py-1.5 font-mono text-[10.5px] tracking-[.06em] text-seal-brass-light"
                    >
                        Most popular
                    </span>
                    <span
                        v-else-if="plan.slug === 'free'"
                        class="absolute -top-3 left-7 rounded-full bg-seal-sage px-3 py-1.5 font-mono text-[10.5px] tracking-[.06em] text-white"
                    >
                        Start here
                    </span>

                    <h3 class="mb-1 font-serif text-lg text-seal-ink">{{ plan.name }}</h3>
                    <p class="mb-4 font-mono text-2xl text-seal-ink">
                        {{ plan.price_naira }}<span v-if="plan.price > 0" class="ml-1 font-sans text-xs font-normal text-seal-muted">/mo</span>
                    </p>

                    <ul class="mb-6 flex flex-1 flex-col gap-2.5">
                        <li class="flex items-start gap-2.5 text-sm text-seal-ink">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path :d="Check" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            <span v-if="plan.included_certs === null">Unlimited certificates</span>
                            <span v-else>{{ plan.included_certs }} certificate{{ plan.included_certs === 1 ? '' : 's' }}{{ plan.slug === 'free' ? '/month' : '/mo included' }}</span>
                        </li>
                        <li v-if="plan.extra_cert_price" class="flex items-start gap-2.5 text-sm text-seal-muted">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path :d="Check" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            ₦{{ (plan.extra_cert_price / 100).toFixed(0) }} per certificate beyond that
                        </li>
                        <li v-else-if="plan.included_certs === null" class="flex items-start gap-2.5 text-sm text-seal-muted">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path :d="Check" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            No extra certificate charges
                        </li>
                        <li v-else class="flex items-start gap-2.5 text-sm text-seal-muted">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path :d="Check" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            Top up your wallet to go further
                        </li>
                        <li class="flex items-start gap-2.5 text-sm text-seal-ink">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path :d="Check" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            Public verification and directory listing
                        </li>
                    </ul>

                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="block w-full rounded-lg py-3 text-center text-sm font-semibold transition"
                        :class="plan.slug === 'growth'
                            ? 'bg-seal-navy text-seal-paper hover:bg-seal-navy-2'
                            : 'border border-seal-line text-seal-ink hover:bg-seal-paper-2'"
                    >
                        {{ plan.slug === 'free' ? 'Create your free account' : `Start with ${plan.name}` }}
                    </Link>
                </div>
            </div>

            <!-- Supporting details -->
            <div class="mt-6 grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-seal-line bg-seal-paper p-8">
                    <h3 class="mb-2 font-serif text-xl text-seal-ink">Prefer to pay as you go?</h3>
                    <div class="mb-4.5 font-serif text-sm text-seal-muted">No plan required</div>
                    <ul class="mb-2 flex flex-col gap-2.5">
                        <li class="flex items-start gap-2.5 text-sm text-seal-ink">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path :d="Check" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            Fund your wallet and pay {{ payg_price_naira }} per certificate, only when you issue one
                        </li>
                        <li class="flex items-start gap-2.5 text-sm text-seal-ink">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path :d="Check" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            No monthly commitment — switch to a plan anytime
                        </li>
                        <li class="flex items-start gap-2.5 text-sm text-seal-ink">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path :d="Check" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            Same wallet also covers extra certificates beyond a plan's included quota
                        </li>
                    </ul>
                </div>

                <div class="rounded-2xl border border-seal-line bg-seal-paper p-8">
                    <h3 class="mb-2 font-serif text-xl text-seal-ink">Refer another business</h3>
                    <div class="mb-4.5 font-serif text-sm text-seal-muted">Earn when they activate</div>
                    <ul class="mb-6 flex flex-col gap-2.5">
                        <li class="flex items-start gap-2.5 text-sm text-seal-ink">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path :d="Check" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            Share your referral link from your dashboard
                        </li>
                        <li class="flex items-start gap-2.5 text-sm text-seal-ink">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path :d="Check" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            Payout unlocks once you're both active
                        </li>
                        <li class="flex items-start gap-2.5 text-sm text-seal-ink">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path :d="Check" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            Track every request from your dashboard
                        </li>
                    </ul>
                    <Link
                        :href="route('login')"
                        class="block w-full rounded-lg border border-seal-line py-3 text-center text-sm font-semibold text-seal-ink transition hover:bg-seal-paper-2"
                    >
                        Sign in to refer a business
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>

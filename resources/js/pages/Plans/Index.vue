<script setup>
import { computed } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    plans: { type: Array, required: true },
    subscription: { type: Object, default: null },
    wallet_balance_kobo: { type: Number, required: true },
    wallet_balance_naira: { type: String, required: true },
})

const currentPlanId = computed(() => props.subscription?.plan_id ?? null)

const isAuthenticated = computed(() => !!props.subscription || props.wallet_balance_kobo > 0)

const subscribing = new Set()

function subscribe(plan) {
    if (subscribing.has(plan.id)) return

    subscribing.add(plan.id)

    router.post(route('payments.subscribe', plan.slug), {}, {
        onFinish: () => subscribing.delete(plan.id),
    })
}

const fundForm = useForm({
    amount_naira: 1000,
})

function fundWallet() {
    fundForm.post(route('payments.fund-wallet'), {
        preserveScroll: true,
    })
}
</script>

<template>
    <div class="min-h-full bg-seal-paper px-4 py-12 sm:px-8">
        <div class="mx-auto max-w-5xl space-y-12">

            <header class="space-y-2 border-b border-seal-line pb-6">
                <p class="font-mono text-[11px] uppercase tracking-[0.2em] text-seal-muted">
                    Account — Plans &amp; Wallet
                </p>

                <h1 class="font-serif text-3xl text-seal-ink">
                    Plans &amp; wallet
                </h1>

                <p class="max-w-xl text-sm leading-relaxed text-seal-muted">
                    Subscribe for certificates included every month, or fund your wallet
                    for pay-as-you-go issuance and overage beyond your plan.
                </p>
            </header>

            <section
                v-if="subscription || wallet_balance_kobo > 0"
                class="grid overflow-hidden rounded-card border border-seal-line bg-seal-paper-2 sm:grid-cols-2 sm:divide-x sm:divide-seal-line"
            >
                <div class="flex items-center justify-between gap-4 px-6 py-5">
                    <div>
                        <p class="font-mono text-[11px] uppercase tracking-[0.15em] text-seal-muted">
                            Current plan
                        </p>

                        <p class="mt-1 font-serif text-xl text-seal-ink">
                            {{ subscription?.is_active ? subscription.plan_name : 'None' }}
                        </p>

                        <p
                            v-if="subscription?.is_active && subscription.current_period_ends_at"
                            class="mt-1 text-sm text-seal-muted"
                        >
                            Active until
                            <span class="font-mono text-xs text-seal-ink">
                                {{ subscription.current_period_ends_at }}
                            </span>
                        </p>
                    </div>

                    <svg
                        v-if="subscription?.is_active"
                        viewBox="0 0 64 64"
                        class="h-14 w-14 shrink-0 -rotate-6 text-seal-brass"
                        aria-hidden="true"
                    >
                        <circle
                            cx="32"
                            cy="32"
                            r="30"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />

                        <circle
                            cx="32"
                            cy="32"
                            r="24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1"
                        />

                        <text
                            x="32"
                            y="29"
                            text-anchor="middle"
                            font-family="Fraunces, serif"
                            font-size="8.5"
                            letter-spacing="1.5"
                            fill="currentColor"
                        >
                            SEALED
                        </text>

                        <text
                            x="32"
                            y="41"
                            text-anchor="middle"
                            font-family="IBM Plex Mono, monospace"
                            font-size="6.5"
                            letter-spacing="1.5"
                            fill="currentColor"
                        >
                            ACTIVE
                        </text>
                    </svg>
                </div>

                <div class="px-6 py-5">
                    <p class="font-mono text-[11px] uppercase tracking-[0.15em] text-seal-muted">
                        Wallet balance
                    </p>

                    <p class="mt-1 font-mono text-2xl text-seal-ink">
                        {{ wallet_balance_naira }}
                    </p>
                </div>
            </section>

            <section>
                <div class="mb-4 flex items-end justify-between gap-4">
                    <div>
                        <h2 class="font-serif text-lg text-seal-ink">
                            Choose a plan
                        </h2>

                        <p class="mt-1 text-sm text-seal-muted">
                            Pick the plan that fits the volume of certificates you issue.
                        </p>
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-3">
                    <article
                        v-for="plan in plans"
                        :key="plan.id"
                        class="relative flex flex-col overflow-hidden rounded-card border p-6 transition-all duration-200"
                        :class="
                            currentPlanId === plan.id
                                ? 'border-seal-brass bg-seal-paper-2 shadow-[0_8px_30px_rgba(184,134,59,0.12)]'
                                : plan.slug === 'growth'
                                    ? 'border-seal-brass/60 bg-seal-paper-2 hover:border-seal-brass hover:shadow-[0_8px_30px_rgba(184,134,59,0.10)]'
                                    : 'border-seal-line bg-seal-paper-2/60 hover:border-seal-brass-dim hover:shadow-sm'
                        "
                    >
                        <div
                            v-if="plan.slug === 'growth'"
                            class="absolute right-0 top-0 rounded-bl-card bg-seal-brass px-4 py-1.5 font-mono text-[10px] font-semibold uppercase tracking-[0.15em] text-seal-ink"
                        >
                            Most popular
                        </div>

                        <svg
                            v-if="currentPlanId === plan.id"
                            viewBox="0 0 64 64"
                            class="absolute -right-3 -top-3 h-16 w-16 -rotate-6 text-seal-brass"
                            aria-hidden="true"
                        >
                            <circle
                                cx="32"
                                cy="32"
                                r="30"
                                fill="var(--seal-paper, #f7f1e1)"
                                stroke="currentColor"
                                stroke-width="1.5"
                            />

                            <circle
                                cx="32"
                                cy="32"
                                r="24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1"
                            />

                            <text
                                x="32"
                                y="29"
                                text-anchor="middle"
                                font-family="Fraunces, serif"
                                font-size="8.5"
                                letter-spacing="1.5"
                                fill="currentColor"
                            >
                                SEALED
                            </text>

                            <text
                                x="32"
                                y="41"
                                text-anchor="middle"
                                font-family="IBM Plex Mono, monospace"
                                font-size="6.5"
                                letter-spacing="1.5"
                                fill="currentColor"
                            >
                                ACTIVE
                            </text>
                        </svg>

                        <div class="pr-12">
                            <p
                                v-if="plan.slug === 'growth'"
                                class="mb-2 font-mono text-[10px] uppercase tracking-[0.18em] text-seal-brass"
                            >
                                Best value
                            </p>

                            <h3 class="font-serif text-xl text-seal-ink">
                                {{ plan.name }}
                            </h3>

                            <p class="mt-3 min-h-[72px] text-sm leading-relaxed text-seal-muted">
                                {{ plan.description }}
                            </p>
                        </div>

                        <div class="mt-6 border-t border-seal-line pt-5">
                            <p class="font-mono text-3xl text-seal-ink">
                                {{ plan.price_naira }}

                                <span class="ml-1 font-sans text-xs font-normal text-seal-muted">
                                    /mo
                                </span>
                            </p>

                            <div class="mt-5 space-y-3">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-seal-sage/10 text-seal-sage"
                                    >
                                        <svg
                                            viewBox="0 0 20 20"
                                            class="h-3 w-3"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path d="m5 10 3 3 7-7" />
                                        </svg>
                                    </span>

                                    <span class="text-sm text-seal-ink">
                                        <template v-if="plan.included_certs === null">
                                            Unlimited certificates
                                        </template>

                                        <template v-else>
                                            {{ plan.included_certs }} certificates included
                                        </template>
                                    </span>
                                </div>

                                <div
                                    v-if="plan.included_certs !== null && plan.extra_cert_price"
                                    class="flex items-center gap-2"
                                >
                                    <span
                                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-seal-sage/10 text-seal-sage"
                                    >
                                        <svg
                                            viewBox="0 0 20 20"
                                            class="h-3 w-3"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path d="m5 10 3 3 7-7" />
                                        </svg>
                                    </span>

                                    <span class="text-sm text-seal-muted">
                                        ₦{{ (plan.extra_cert_price / 100).toFixed(0) }} per extra certificate
                                    </span>
                                </div>

                                <div
                                    v-if="plan.included_certs === null"
                                    class="flex items-center gap-2"
                                >
                                    <span
                                        class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-seal-sage/10 text-seal-sage"
                                    >
                                        <svg
                                            viewBox="0 0 20 20"
                                            class="h-3 w-3"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path d="m5 10 3 3 7-7" />
                                        </svg>
                                    </span>

                                    <span class="text-sm text-seal-muted">
                                        No extra certificate charges
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button
                            v-if="currentPlanId !== plan.id"
                            type="button"
                            class="mt-7 w-full rounded-card py-2.5 text-sm font-semibold transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-seal-brass focus-visible:ring-offset-2 focus-visible:ring-offset-seal-paper"
                            :class="
                                plan.slug === 'growth'
                                    ? 'bg-seal-brass text-seal-ink hover:bg-seal-brass-dim hover:text-seal-paper'
                                    : 'border border-seal-line bg-seal-paper text-seal-ink hover:border-seal-brass hover:bg-seal-paper-2'
                            "
                            @click="subscribe(plan)"
                        >
                            Choose {{ plan.name }}
                        </button>

                        <p
                            v-else
                            class="mt-7 flex items-center justify-center gap-2 rounded-card border border-seal-sage/30 bg-seal-sage/5 py-2.5 font-mono text-xs uppercase tracking-[0.15em] text-seal-sage"
                        >
                            <span class="h-1.5 w-1.5 rounded-full bg-seal-sage"></span>
                            Active plan
                        </p>
                    </article>
                </div>
            </section>

            <section
                v-if="isAuthenticated"
                class="max-w-sm rounded-card border border-seal-line bg-seal-paper-2 p-6"
            >
                <h2 class="font-serif text-lg text-seal-ink">
                    Fund wallet
                </h2>

                <p class="mt-1 text-sm text-seal-muted">
                    For pay-as-you-go certificates, and any extras beyond your plan.
                </p>

                <div class="mt-5 border-t border-dashed border-seal-line pt-5">
                    <form
                        class="flex gap-2"
                        @submit.prevent="fundWallet"
                    >
                        <div class="relative flex-1">
                            <span
                                class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 font-mono text-seal-muted"
                            >
                                ₦
                            </span>

                            <input
                                v-model.number="fundForm.amount_naira"
                                type="number"
                                min="100"
                                step="100"
                                class="w-full rounded-card border border-seal-line bg-seal-paper py-2 pl-7 pr-3 font-mono text-sm text-seal-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-seal-brass"
                            />
                        </div>

                        <button
                            type="submit"
                            class="rounded-card bg-seal-brass px-4 py-2 text-sm font-semibold text-seal-ink transition-colors hover:bg-seal-brass-dim hover:text-seal-paper focus:outline-none focus-visible:ring-2 focus-visible:ring-seal-brass focus-visible:ring-offset-2 focus-visible:ring-offset-seal-paper-2 disabled:opacity-50"
                            :disabled="fundForm.processing"
                        >
                            Fund
                        </button>
                    </form>

                    <p
                        v-if="fundForm.errors.amount_naira"
                        class="mt-2 text-sm text-seal-danger"
                    >
                        {{ fundForm.errors.amount_naira }}
                    </p>
                </div>
            </section>

        </div>
    </div>
</template>

<!-- resources/js/Components/FundWalletCard.vue -->
<script setup>
import { useForm } from '@inertiajs/vue3'

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
    <section class="max-w-sm rounded-card border border-seal-line bg-seal-paper-2 p-6">
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
</template>

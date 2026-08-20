<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    canRegister: { type: Boolean, default: true },
});
</script>

<template>
    <section id="pricing" class="bg-seal-paper px-6 py-24">
        <div class="mx-auto max-w-6xl">
            <div class="mb-14 max-w-xl">
                <span class="mb-3.5 block font-mono text-xs uppercase tracking-[.14em] text-seal-brass-dim">Pricing</span>
                <h2 class="mb-3.5 font-serif text-[clamp(28px,3.4vw,38px)] font-semibold leading-tight tracking-tight text-seal-ink">
                    Try it free. Activate when you're ready.
                </h2>
                <p class="text-base leading-relaxed text-seal-muted">
                    No card required to sign up, add trainees and issue test certificates first, then
                    activate your account to go live.
                </p>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <div class="relative rounded-2xl border border-seal-brass bg-seal-paper p-8 shadow-[0_20px_50px_-25px_rgba(199,154,70,.4)]">
                    <span class="absolute -top-3 left-8 rounded-full bg-seal-navy px-3 py-1.5 font-mono text-[10.5px] tracking-[.06em] text-seal-brass-light">
                        Most businesses start here
                    </span>
                    <h3 class="mb-2 font-serif text-xl text-seal-ink">Start free</h3>
                    <div class="mb-4.5 font-serif text-sm text-seal-muted">No cost to sign up and test</div>
                    <ul class="mb-6 flex flex-col gap-2.5">
                        <li class="flex items-start gap-2.5 text-sm text-seal-ink">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path d="M4 10l4 4 8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            Add trainees and issue test certificates before you activate
                        </li>
                        <li class="flex items-start gap-2.5 text-sm text-seal-ink">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path d="M4 10l4 4 8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            Built-in certificate templates, ready on day one
                        </li>
                        <li class="flex items-start gap-2.5 text-sm text-seal-ink">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path d="M4 10l4 4 8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            Public verification and a directory listing for every active business
                        </li>
                        <li class="flex items-start gap-2.5 text-sm text-seal-ink">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path d="M4 10l4 4 8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            One-time activation fee when you're ready to issue real certificates
                        </li>
                    </ul>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="block w-full rounded-lg bg-seal-navy py-3 text-center text-sm font-semibold text-seal-paper transition hover:bg-seal-navy-2"
                    >
                        Create your free account
                    </Link>
                </div>

                <div class="rounded-2xl border border-seal-line bg-seal-paper p-8">
                    <h3 class="mb-2 font-serif text-xl text-seal-ink">Refer another business</h3>
                    <div class="mb-4.5 font-serif text-sm text-seal-muted">Earn when they activate</div>
                    <ul class="mb-6 flex flex-col gap-2.5">
                        <li class="flex items-start gap-2.5 text-sm text-seal-ink">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path d="M4 10l4 4 8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            Share your referral link from your dashboard
                        </li>
                        <li class="flex items-start gap-2.5 text-sm text-seal-ink">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path d="M4 10l4 4 8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            Payout unlocks once you're both active
                        </li>
                        <li class="flex items-start gap-2.5 text-sm text-seal-ink">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path d="M4 10l4 4 8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
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

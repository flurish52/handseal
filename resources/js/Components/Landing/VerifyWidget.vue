<script setup>
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const certNumber = ref('');
const checking = ref(false);

function submitVerify() {
    const value = certNumber.value.trim();
    checking.value = true;

    if (value.length > 0) {
        // A number was typed — go straight to that certificate's result.
        router.get(route('verify.show', value), {}, { onFinish: () => (checking.value = false) });
    } else {
        // Nothing typed — send them to the main lookup page instead.
        router.get(route('verify.lookup'), {}, { onFinish: () => (checking.value = false) });
    }
}
</script>

<template>
    <section id="verify" class="bg-seal-paper px-6 py-24">
        <div class="mx-auto max-w-6xl">
            <div class="grid items-center gap-12 rounded-[20px] bg-seal-navy p-8 text-seal-paper lg:grid-cols-[.85fr_1.15fr] lg:p-12">
                <div>
                    <span class="mb-3.5 block font-mono text-xs uppercase tracking-[.14em] text-seal-brass-light">Why it matters</span>
                    <h2 class="mb-3.5 font-serif text-[clamp(28px,3.4vw,38px)] font-semibold leading-tight">This is the whole point.</h2>
                    <p class="mb-6 text-base leading-relaxed text-seal-muted-dark">
                        A fake certificate costs your graduate a job offer and costs your business its name.
                        HandSeal replaces "trust me" with something anyone can check themselves, try it now
                        with any certificate number.
                    </p>
                    <ul class="flex flex-col gap-3 text-sm text-seal-muted-dark">
                        <li class="flex items-start gap-2.5">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path d="M4 10l4 4 8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            Free, public page, no account or login needed to check one
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path d="M4 10l4 4 8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            A made-up or altered number is caught instantly
                        </li>
                        <li class="flex items-start gap-2.5">
                            <svg viewBox="0 0 20 20" fill="none" class="mt-0.5 h-4 w-4 shrink-0 text-seal-sage"><path d="M4 10l4 4 8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                            Shows the trainee's name, programme and issuing business
                        </li>
                    </ul>
                </div>

                <div class="rounded-2xl border border-seal-line-dark bg-seal-navy-2 p-6">
                    <div class="mb-4 font-mono text-[11px] uppercase tracking-[.1em] text-[#7d87a0]">Check a certificate</div>

                    <form class="mb-3 flex flex-col gap-2.5 sm:flex-row" @submit.prevent="submitVerify">
                        <input
                            v-model="certNumber"
                            type="text"
                            placeholder="Enter the certificate number"
                            class="w-full flex-1 rounded-lg border border-seal-line-dark bg-white/[.03] px-4 py-3 font-mono text-sm text-seal-paper placeholder:text-[#7d87a0] focus:border-seal-brass-light focus:outline-none"
                        />
                        <button
                            type="submit"
                            :disabled="checking"
                            class="shrink-0 rounded-lg bg-gradient-to-b from-seal-brass-light to-seal-brass px-6 py-3 text-sm font-semibold text-seal-navy-3 transition disabled:opacity-70"
                        >
                            {{ checking ? 'Checking…' : 'Verify' }}
                        </button>
                    </form>

                    <Link :href="route('verify.lookup')" class="mb-6 inline-flex items-center gap-1.5 text-sm text-seal-brass-light hover:underline">
                        Don't have the number? Scan the QR code on the certificate instead →
                    </Link>

                    <div class="border-t border-seal-line-dark pt-5">
                        <p class="mb-3 font-mono text-[11px] uppercase tracking-[.1em] text-[#7d87a0]">What you'll see, example</p>
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <div class="flex-1 rounded-xl border border-seal-sage/40 bg-seal-sage/10 p-4">
                                <div class="mb-2 flex items-center gap-2 text-sm font-semibold text-seal-sage">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-seal-sage/25">
                                        <svg viewBox="0 0 20 20" fill="none" class="h-3 w-3"><path d="M4 10l4 4 8-8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    </span>
                                    Certificate verified
                                </div>
                                <p class="text-xs leading-relaxed text-seal-muted-dark">Shows the trainee's name, programme, issuing business and date.</p>
                            </div>
                            <div class="flex-1 rounded-xl border border-[#c2705c]/40 bg-[#a8442e]/10 p-4">
                                <div class="mb-2 flex items-center gap-2 text-sm font-semibold text-[#e69684]">
                                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#a8442e]/25">
                                        <svg viewBox="0 0 20 20" fill="none" class="h-3 w-3"><path d="M6 6l8 8M14 6l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round" /></svg>
                                    </span>
                                    No certificate found
                                </div>
                                <p class="text-xs leading-relaxed text-seal-muted-dark">That number doesn't match anything on HandSeal, caught in seconds.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

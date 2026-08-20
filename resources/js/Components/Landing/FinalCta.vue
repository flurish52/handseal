<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    canRegister: { type: Boolean, default: true },
});
</script>

<template>
    <section class="relative overflow-hidden bg-seal-navy px-6 py-28 text-center text-seal-paper">
        <div
            class="pointer-events-none absolute inset-0"
            style="background: radial-gradient(ellipse 700px 400px at 50% 0%, rgba(231,197,119,.12), transparent 60%);"
        ></div>
        <div class="relative mx-auto max-w-lg">
            <svg viewBox="0 0 120 120" fill="none" class="mx-auto mb-7 h-16 w-16">
                <circle cx="60" cy="60" r="52" fill="#c79a46" />
                <circle cx="60" cy="60" r="52" stroke="#e7c577" stroke-width="1.5" />
                <circle cx="60" cy="60" r="44" fill="none" stroke="#8a6a30" stroke-width="1" stroke-dasharray="2 4" />
                <text x="60" y="55" text-anchor="middle" font-family="Fraunces, serif" font-size="26" font-weight="600" fill="#101a30">HS</text>
                <text x="60" y="78" text-anchor="middle" font-family="IBM Plex Mono, monospace" font-size="7.5" letter-spacing="1.5" fill="#5a441e">HANDSEAL</text>
            </svg>
            <h2 class="mb-4 font-serif text-[clamp(28px,3.4vw,38px)] font-semibold leading-tight">
                Start issuing certificates your graduates can stand behind.
            </h2>
            <p class="mb-9 text-base leading-relaxed text-seal-muted-dark">
                Sign up free and test the whole flow, your first real certificate can go out the same day
                you activate.
            </p>
            <Link
                v-if="canRegister"
                :href="route('register')"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-seal-brass-light to-seal-brass px-8 py-4 text-base font-semibold text-seal-navy-3 shadow-[0_1px_0_rgba(255,255,255,.35)_inset,0_8px_20px_-8px_rgba(199,154,70,.55)]"
            >
                Create your free account
            </Link>
        </div>
    </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    canRegister: { type: Boolean, default: true },
});
</script>

<template>
    <header class="relative overflow-hidden bg-seal-navy px-6 pb-28 pt-20 text-seal-paper">
        <div
            class="pointer-events-none absolute inset-0"
            style="background: radial-gradient(ellipse 900px 500px at 15% -10%, rgba(231,197,119,.10), transparent 60%), radial-gradient(ellipse 700px 500px at 100% 10%, rgba(95,122,91,.10), transparent 55%);"
        ></div>

        <div class="relative mx-auto grid max-w-6xl items-center gap-14 lg:grid-cols-[1.05fr_.95fr]">
            <div>
                <span class="mb-5 inline-flex items-center gap-2 font-mono text-xs uppercase tracking-[.14em] text-seal-brass-light">
                    <span class="h-px w-4 bg-seal-brass-light"></span>
                    For trade &amp; vocational businesses
                </span>

                <h1 class="mb-5 font-serif text-[clamp(38px,5vw,58px)] font-semibold leading-[1.08] tracking-tight">
                    Certificates worth <em class="font-medium not-italic text-seal-brass-light">trusting.</em>
                </h1>

                <p class="mb-8 max-w-md text-lg leading-relaxed text-seal-muted-dark">
                    Issue certificates to every trainee you graduate, and let anyone check they're real in
                    seconds, no phone call to your office required.
                </p>

                <div class="mb-6 flex flex-wrap items-center gap-3.5">
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-seal-brass-light to-seal-brass px-7 py-3.5 text-base font-semibold text-seal-navy-3 shadow-[0_1px_0_rgba(255,255,255,.35)_inset,0_8px_20px_-8px_rgba(199,154,70,.55)] transition hover:shadow-[0_1px_0_rgba(255,255,255,.4)_inset,0_10px_26px_-8px_rgba(199,154,70,.75)]"
                    >
                        Start free
                    </Link>
                    <Link
                        :href="route('verify.lookup')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-seal-brass-light/35 px-7 py-3.5 text-base font-semibold text-seal-paper transition hover:border-seal-brass-light hover:bg-seal-brass-light/[.06]"
                    >
                        Verify a certificate →
                    </Link>
                </div>

                <div class="flex items-center gap-2 text-sm text-[#7d87a0]">
                    <svg viewBox="0 0 20 20" fill="none" class="h-3.5 w-3.5 shrink-0">
                        <path d="M4 10l4 4 8-8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    Free to sign up. Add trainees and issue test certificates before you activate.
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-[420px]" style="perspective: 1200px;">
                <div class="hidden rounded-xl border border-seal-line-dark bg-seal-navy-2 px-4 py-3.5 font-mono text-xs text-seal-sage shadow-[0_20px_40px_-15px_rgba(0,0,0,.5)] lg:absolute lg:-left-8 lg:-top-3.5 lg:flex lg:items-center lg:gap-2">
                    <svg viewBox="0 0 20 20" fill="none" class="h-3.5 w-3.5 shrink-0"><path d="M4 10l4 4 8-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" /></svg>
                    Verified — HS-JAO14-0231
                </div>

                <div ref="certCard" class="cert-card relative rounded-[10px] bg-seal-paper px-7 pb-7 pt-8 text-seal-ink shadow-[0_30px_70px_-20px_rgba(0,0,0,.55)] [transform:rotate(-1.2deg)]">
                    <div class="pointer-events-none absolute inset-2.5 rounded-md border border-seal-line"></div>
                    <div class="impact-ring absolute inset-0 rounded-full"></div>

                    <div class="mb-6 flex items-start justify-between">
                        <span class="font-mono text-[11px] uppercase tracking-[.1em] text-seal-muted">HandSeal · Certificate</span>
                        <span class="font-mono text-[11.5px] text-seal-muted">HS-JAO14-0231</span>
                    </div>
                    <div class="mb-2.5 font-mono text-[10.5px] uppercase tracking-[.16em] text-seal-brass-dim">Certificate of completion</div>
                    <h3 class="mb-1.5 font-serif text-[27px] font-semibold">Amaka Johnson-Obi</h3>
                    <p class="mb-5 max-w-[320px] text-sm leading-relaxed text-seal-muted">
                        Has completed the Professional Tailoring &amp; Fashion Design programme at Jane's Atelier of Fashion.
                    </p>
                    <div class="flex items-end justify-between border-t border-seal-line pt-4">
                        <div class="text-xs leading-[1.7] text-seal-muted">
                            Issued <b class="font-semibold text-seal-ink">12 Aug 2026</b><br />
                            Programme lead <b class="font-semibold text-seal-ink">J. Johnson-Obi</b>
                        </div>
                    </div>

                    <div class="seal-wrap absolute bottom-4 right-5 h-24 w-24">
                        <svg viewBox="0 0 120 120" fill="none" class="seal h-full w-full">
                            <circle cx="60" cy="60" r="52" fill="#c79a46" />
                            <circle cx="60" cy="60" r="52" stroke="#8a6a30" stroke-width="1.5" />
                            <circle cx="60" cy="60" r="44" fill="none" stroke="#8a6a30" stroke-width="1" stroke-dasharray="2 4" />
                            <text x="60" y="55" text-anchor="middle" font-family="Fraunces, serif" font-size="26" font-weight="600" fill="#101a30">HS</text>
                            <text x="60" y="78" text-anchor="middle" font-family="IBM Plex Mono, monospace" font-size="7.5" letter-spacing="1.5" fill="#5a441e">VERIFIED</text>
                        </svg>
                    </div>
                </div>

                <div class="mt-3 hidden justify-end lg:flex">
                    <div class="inline-flex items-center gap-2 rounded-xl border border-seal-line-dark bg-seal-navy-2 px-4 py-3.5 font-mono text-xs text-seal-brass-light shadow-[0_20px_40px_-15px_rgba(0,0,0,.5)]">
                        <svg viewBox="0 0 20 20" fill="none" class="h-3.5 w-3.5 shrink-0"><path d="M10 2v16M2 10h16" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" /></svg>
                        Issued in under 40 seconds
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<style scoped>
/* Signature moment: the seal stamps down onto the certificate once on load,
   and replays on hover. Kept to a single element — everything else on the
   page is static. Respects prefers-reduced-motion. */
.seal {
    opacity: 0;
    transform: translateY(-70px) scale(1.7) rotate(-16deg);
    filter: drop-shadow(0 14px 18px rgba(0, 0, 0, 0.35));
    animation: stampDown 900ms cubic-bezier(0.2, 0.9, 0.25, 1) 500ms forwards;
}
.impact-ring {
    background: radial-gradient(circle, rgba(199, 154, 70, 0.55), transparent 65%);
    opacity: 0;
    transform: scale(0.4);
    animation: impact 520ms ease-out 1320ms forwards;
}
@keyframes stampDown {
    0% { opacity: 0; transform: translateY(-70px) scale(1.7) rotate(-16deg); }
    55% { opacity: 1; transform: translateY(0) scale(0.9) rotate(-8deg); }
    72% { transform: translateY(0) scale(1.06) rotate(-8deg); }
    100% { opacity: 1; transform: translateY(0) scale(1) rotate(-8deg); }
}
@keyframes impact {
    0% { opacity: 0.9; transform: scale(0.4); }
    100% { opacity: 0; transform: scale(2.1); }
}
.seal-wrap:hover .seal {
    animation: reStamp 480ms cubic-bezier(0.3, 0.9, 0.3, 1);
}
@keyframes reStamp {
    0% { transform: translateY(0) scale(1) rotate(-8deg); }
    40% { transform: translateY(0) scale(0.88) rotate(-8deg); }
    100% { transform: translateY(0) scale(1) rotate(-8deg); }
}
@media (prefers-reduced-motion: reduce) {
    .seal, .impact-ring {
        animation: none !important;
        opacity: 1 !important;
        transform: none !important;
    }
}
</style>

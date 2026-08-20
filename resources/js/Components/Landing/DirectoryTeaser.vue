<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    // Optional: pass a handful of active/verified businesses from the
    // controller (e.g. Business::whereHas('payments', ...)->latest()->take(6))
    // to show real names here instead of the generic teaser below.
    featuredBusinesses: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <section class="bg-seal-navy px-6 py-24 text-seal-paper">
        <div class="mx-auto max-w-6xl">
            <div class="mb-10 flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
                <div class="max-w-xl">
                    <span class="mb-3.5 block font-mono text-xs uppercase tracking-[.14em] text-seal-brass-light">Public directory</span>
                    <h2 class="mb-3.5 font-serif text-[clamp(28px,3.4vw,38px)] font-semibold leading-tight">
                        Every business on HandSeal has a public profile.
                    </h2>
                    <p class="text-base leading-relaxed text-seal-muted-dark">
                        Employers and trainees can look a business up before they ever pick up the phone,
                        that visibility is part of what makes your certificates worth something.
                    </p>
                </div>
                <Link
                    :href="route('directory.index')"
                    class="inline-flex shrink-0 items-center gap-2 rounded-xl border border-seal-brass-light/35 px-6 py-3 text-sm font-semibold text-seal-paper transition hover:border-seal-brass-light hover:bg-seal-brass-light/[.06]"
                >
                    Browse the directory →
                </Link>
            </div>

            <div v-if="featuredBusinesses.length" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="business in featuredBusinesses"
                    :key="business.id"
                    :href="route('directory.index') + '#business-' + business.id"
                    class="rounded-xl border border-seal-line-dark bg-seal-navy-2 p-5 transition hover:border-seal-brass-light/50"
                >
                    <div class="mb-1 font-serif text-base font-semibold">{{ business.name }}</div>
                    <div class="text-xs text-seal-muted-dark">{{ business.trade || business.category }}</div>
                </Link>
            </div>

            <div v-else class="rounded-xl border border-seal-line-dark bg-seal-navy-2 p-6 text-sm text-seal-muted-dark">
                Once your business is active, your programmes and certificate count show up here for anyone
                to see,
                <Link :href="route('directory.index')" class="text-seal-brass-light hover:underline">take a look at the directory</Link>
                to see how it's presented.
            </div>
        </div>
    </section>
</template>

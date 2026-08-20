<script setup>
const trades = [
    'Tailoring & fashion',
    'Plumbing & pipefitting',
    'Cosmetology',
    'Catering & hospitality',
    'Electrical installation',
    'Auto mechanics',
    'ICT & digital skills',
];
</script>

<template>
    <section class="bg-seal-paper px-6 py-24">
        <div class="mx-auto max-w-6xl">
            <div class="mb-10 max-w-xl">
                <span class="mb-3.5 block font-mono text-xs uppercase tracking-[.14em] text-seal-brass-dim">Built for</span>
                <h2 class="font-serif text-[clamp(28px,3.4vw,38px)] font-semibold leading-tight tracking-tight text-seal-ink">
                    Whatever your trade, the process is the same.
                </h2>
            </div>
            <div class="flex flex-wrap gap-3">
                <span
                    v-for="trade in trades"
                    :key="trade"
                    class="rounded-full border border-seal-line bg-seal-paper-2 px-4.5 py-3 text-sm font-medium text-seal-ink"
                >
                    {{ trade }}
                </span>
            </div>
        </div>
    </section>
</template>

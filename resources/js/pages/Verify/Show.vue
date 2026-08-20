<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    result: { type: Object, required: true },
    certificate_number: { type: String, required: true },
});
</script>

<template>
    <Head title="Certificate verification" />

    <div class="min-h-screen bg-seal-paper flex items-center justify-center p-6">
        <div class="w-full max-w-sm">
            <p class="text-xs uppercase tracking-widest text-seal-brass font-semibold mb-2 text-center">HandSeal</p>

            <div
                v-if="result.valid"
                class="bg-white rounded-card border border-seal-sage p-5"
            >
                <p class="font-mono text-xs font-bold text-seal-sage mb-3">✓ VALID CERTIFICATE</p>
                <p class="text-sm text-seal-ink mb-1">
                    <span class="font-semibold text-seal-navy">{{ result.recipient_name }}</span> completed
                </p>
                <p class="text-sm text-seal-ink mb-1">{{ result.programme_name }}</p>
                <p class="text-sm text-seal-ink mb-1">
                    with <span class="font-semibold text-seal-navy">{{ result.business_name }}</span>
                </p>
                <p class="text-sm text-seal-ink">Issued {{ result.issued_at }}</p>
            </div>

            <div
                v-else
                class="bg-white rounded-card border border-seal-danger p-5"
            >
                <p class="font-mono text-xs font-bold text-seal-danger mb-3">✗ NOT FOUND</p>
                <p class="text-sm text-seal-ink">
                    No certificate matches <span class="font-mono">{{ certificate_number }}</span>.
                    Check the number and try again.
                </p>
            </div>

            <Link :href="route('verify.lookup')" class="block text-center text-xs text-seal-muted mt-4">
                Verify another certificate
            </Link>
        </div>
    </div>
</template>

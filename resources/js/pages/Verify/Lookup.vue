<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';

const certificateNumber = ref('');

function submit() {
    if (certificateNumber.value.trim()) {
        router.get(route('verify.show', certificateNumber.value.trim()));
    }
}
</script>

<template>
    <Head title="Verify a certificate" />

    <div class="min-h-screen bg-seal-paper flex items-center justify-center p-6">
        <div class="w-full max-w-sm">
            <p class="text-xs uppercase tracking-widest text-seal-brass font-semibold mb-2 text-center">HandSeal</p>
            <h1 class="font-serif text-xl font-semibold text-seal-navy mb-1 text-center">Verify a certificate</h1>
            <p class="text-sm text-seal-muted mb-6 text-center">Enter the certificate number found on the document</p>

            <form @submit.prevent="submit" class="space-y-3">
                <input
                    v-model="certificateNumber"
                    type="text"
                    placeholder="HS-PFT7-000042"
                    class="w-full rounded-lg border border-seal-line px-4 py-3 text-sm font-mono text-center focus:outline-none focus:ring-2 focus:ring-seal-navy"
                />
                <button
                    type="submit"
                    class="w-full bg-seal-navy text-white text-sm font-medium py-3 rounded-lg"
                >
                    Verify
                </button>
            </form>
        </div>
    </div>
</template>

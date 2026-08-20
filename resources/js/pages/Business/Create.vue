<script setup>
import { useForm, Head } from '@inertiajs/vue3';

const form = useForm({
    business_name: '',
    referral_code: new URLSearchParams(window.location.search).get('ref') ?? '',
});

function submit() {
    form.post(route('business.store'));
}
</script>

<template>
    <Head title="Set up your business" />

    <div class="min-h-screen bg-seal-paper flex items-center justify-center p-6">
        <div class="w-full max-w-sm">
            <p class="text-xs uppercase tracking-widest text-seal-brass font-semibold mb-2">HandSeal</p>
            <h1 class="font-serif text-2xl font-semibold text-seal-navy mb-1">What's your business called?</h1>
            <p class="text-sm text-seal-muted mb-6">This is the name that appears on every certificate you issue.</p>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <input
                        v-model="form.business_name"
                        type="text"
                        placeholder="e.g. Peter's Fashion House"
                        autofocus
                        class="w-full rounded-lg border border-seal-line px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                    />
                    <p v-if="form.errors.business_name" class="text-xs text-seal-danger mt-1">
                        {{ form.errors.business_name }}
                    </p>
                </div>

                <div>
                    <input
                        v-model="form.referral_code"
                        type="text"
                        placeholder="Referral code (optional)"
                        class="w-full rounded-lg border border-seal-line px-4 py-3 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-seal-navy"
                    />
                    <p v-if="form.errors.referral_code" class="text-xs text-seal-danger mt-1">
                        {{ form.errors.referral_code }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-seal-navy text-white text-sm font-medium py-3 rounded-lg disabled:opacity-50"
                >
                    Continue
                </button>
            </form>
        </div>
    </div>
</template>

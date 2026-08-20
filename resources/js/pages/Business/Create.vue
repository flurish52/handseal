<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { compressImage } from '@/Composables/useImageCompression';

const form = useForm({
    business_name: '',
    address: '',
    logo: null,
    referral_code: new URLSearchParams(window.location.search).get('ref') ?? '',
});

const logoPreview = ref(null);
const isCompressing = ref(false);
const logoError = ref('');

async function onLogoSelected(e) {
    const file = e.target.files?.[0];
    e.target.value = '';
    if (!file) return;

    logoError.value = '';
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
        logoError.value = 'Only JPG, PNG, or WEBP images are allowed.';
        return;
    }

    isCompressing.value = true;
    try {
        const compressed = await compressImage(file, { maxSizeMb: 1, maxDimension: 1000 });
        form.logo = compressed;
        if (logoPreview.value) URL.revokeObjectURL(logoPreview.value);
        logoPreview.value = URL.createObjectURL(compressed);
    } catch {
        logoError.value = 'Could not process that image. Try a different one.';
    } finally {
        isCompressing.value = false;
    }
}

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
                        v-model="form.address"
                        type="text"
                        placeholder="Business address (optional)"
                        class="w-full rounded-lg border border-seal-line px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                    />
                    <p v-if="form.errors.address" class="text-xs text-seal-danger mt-1">
                        {{ form.errors.address }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm text-seal-ink mb-1">Business logo <span class="text-seal-muted font-normal">(optional)</span></label>
                    <div class="flex items-center gap-3">
                        <div v-if="logoPreview" class="w-14 h-14 rounded-lg border border-seal-line overflow-hidden shrink-0">
                            <img :src="logoPreview" class="w-full h-full object-cover" />
                        </div>
                        <input
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            :disabled="isCompressing"
                            @change="onLogoSelected"
                            class="flex-1 text-sm text-seal-ink file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-seal-navy file:text-white file:text-sm file:font-medium disabled:opacity-50"
                        />
                    </div>
                    <p v-if="isCompressing" class="text-xs text-seal-muted mt-1">Optimizing image…</p>
                    <p v-if="logoError" class="text-xs text-seal-danger mt-1">{{ logoError }}</p>
                    <p v-if="form.errors.logo" class="text-xs text-seal-danger mt-1">{{ form.errors.logo }}</p>
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

<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { compressImage } from '@/Composables/useImageCompression';

const props = defineProps({
    business: { type: Object, required: true },
    referralCode: { type: String, default: null },
    referralLocked: { type: Boolean, required: true },
});

const form = useForm({
    business_name: props.business.business_name,
    address: props.business.address ?? '',
    logo: null,
    is_publicly_visible: props.business.is_publicly_visible,
    referral_code: props.referralCode ?? '',
});

const logoPreview = ref(props.business.logo_url ?? null);
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
        logoPreview.value = URL.createObjectURL(compressed);
    } catch {
        logoError.value = 'Could not process that image. Try a different one.';
    } finally {
        isCompressing.value = false;
    }
}

function submit() {
    form.put(route('business.update'));
}
</script>

<template>
    <Head title="Business settings" />

        <div class="p-4 space-y-6">
            <h1 class="font-serif text-xl font-semibold text-seal-navy">Business settings</h1>

            <form @submit.prevent="submit" class="bg-white rounded-card border border-seal-line p-4 space-y-4">
                <div>
                    <label class="text-xs text-seal-muted">Business name</label>
                    <input
                        v-model="form.business_name"
                        type="text"
                        class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                    />
                    <p v-if="form.errors.business_name" class="text-xs text-seal-danger mt-1">
                        {{ form.errors.business_name }}
                    </p>
                </div>

                <div>
                    <label class="text-xs text-seal-muted">Business address</label>
                    <input
                        v-model="form.address"
                        type="text"
                        placeholder="Street, city, state"
                        class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                    />
                    <p v-if="form.errors.address" class="text-xs text-seal-danger mt-1">
                        {{ form.errors.address }}
                    </p>
                </div>
                <div>
                    <label class="text-xs text-seal-muted">Business logo</label>
                    <div class="flex items-center gap-3 mt-1">
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

                <label class="flex items-start gap-3">
                    <input
                        v-model="form.is_publicly_visible"
                        type="checkbox"
                        class="mt-1"
                    />
                    <span>
                        <span class="block text-sm font-medium text-seal-ink">List me in the public directory</span>
                        <span class="block text-xs text-seal-muted mt-0.5">
                            Shows your business name and total certificates issued. No student names or financial data.
                        </span>
                    </span>
                </label>

                <div>
                    <label class="text-xs text-seal-muted">Referral code</label>
                    <input
                        v-model="form.referral_code"
                        type="text"
                        :disabled="referralLocked"
                        placeholder="Enter a referral code (optional)"
                        class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-seal-navy disabled:bg-seal-paper disabled:text-seal-muted"
                    />
                    <p v-if="referralLocked" class="text-xs text-seal-muted mt-1">
                        Locked — already applied and can't be changed.
                    </p>
                    <p v-if="form.errors.referral_code" class="text-xs text-seal-danger mt-1">
                        {{ form.errors.referral_code }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-seal-navy text-white text-sm font-medium px-4 py-2 rounded-lg disabled:opacity-50"
                >
                    Save changes
                </button>
            </form>
        </div>
</template>

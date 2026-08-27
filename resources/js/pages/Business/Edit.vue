<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
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
    cert_prefix: props.business.cert_prefix ?? '',
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

const certsAlreadyIssued = computed(() => props.business.certificates_count > 0);
const isChangingExistingPrefix = computed(
    () => !!props.business.cert_prefix && form.cert_prefix !== props.business.cert_prefix
);
const showPrefixChangeWarning = computed(
    () => certsAlreadyIssued.value && isChangingExistingPrefix.value
);

const defaultPrefixPreview = computed(() => `HS-${props.business.initials}`);

// Matches backend: {PREFIX}-{sequence_number}-{local_number}, both zero-padded to 6.
const padded = (n) => String(n ?? 0).padStart(6, '0');

const livePrefixPreview = computed(
    () => `${form.cert_prefix || defaultPrefixPreview.value}-${padded(props.business.sequence_number)}-${padded(props.business.certificates_count + 1)}`
);

function submit() {
    form.put(route('business.update'));
}
</script>

<template>
    <Head title="Business settings" />
    <div class="p-4 space-y-6">
        <h1 class="font-serif text-xl font-semibold text-seal-navy">Business settings</h1>

        <form @submit.prevent="submit" class="space-y-5">

            <!-- Identity -->
            <section class="bg-white rounded-card border border-seal-line p-4 space-y-4">
                <h2 class="text-sm font-semibold text-seal-ink">Business identity</h2>

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
            </section>

            <!-- Certificate branding -->
            <section class="bg-white rounded-card border border-seal-line p-4 space-y-3">
                <h2 class="text-sm font-semibold text-seal-ink">Certificate branding</h2>

                <div>
                    <label class="text-xs text-seal-muted">Certificate prefix</label>
                    <input
                        v-model="form.cert_prefix"
                        type="text"
                        maxlength="12"
                        placeholder="e.g. JBC (your initials)"
                        class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm font-mono uppercase focus:outline-none focus:ring-2 focus:ring-seal-navy"
                        @input="form.cert_prefix = form.cert_prefix.toUpperCase().replace(/[^A-Z0-9-]/g, '')"
                    />
                    <p class="text-xs text-seal-muted mt-1">
                        2–12 characters — letters, numbers, and hyphens only. Shown on every certificate you issue. Leave blank to use the HandSeal default
                        (<span class="font-mono">{{ defaultPrefixPreview }}</span>).
                    </p>
                    <p class="text-xs text-seal-muted mt-1">
                        Preview: <span class="font-mono">{{ livePrefixPreview }}</span>
                    </p>
                    <p v-if="form.errors.cert_prefix" class="text-xs text-seal-danger mt-1">
                        {{ form.errors.cert_prefix }}
                    </p>

                    <div
                        v-if="showPrefixChangeWarning"
                        class="mt-2 bg-seal-danger-light border border-seal-gold/30 text-seal-ink text-xs rounded-lg px-3 py-2.5"
                    >
                        Please note that certificates already issued with the number: {{ business.certificates_count }}
                        can not be changed.
                        Changing this won't affect previously issued certificates, only certificates issued from now on will use the new prefix.
                    </div>
                </div>
            </section>

            <!-- Visibility -->
            <section class="bg-white rounded-card border border-seal-line p-4">
                <h2 class="text-sm font-semibold text-seal-ink mb-3">Visibility</h2>
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
            </section>

            <!-- Referral -->
            <section class="bg-white rounded-card border border-seal-line p-4">
                <h2 class="text-sm font-semibold text-seal-ink mb-3">Referral</h2>
                <label class="text-xs text-seal-muted">Referred by</label>
                <input
                    v-model="form.referral_code"
                    type="text"
                    :disabled="referralLocked"
                    placeholder="Enter the code you were referred with (optional)"
                    class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-seal-navy disabled:bg-seal-paper disabled:text-seal-muted"
                />
                <p v-if="referralLocked" class="text-xs text-seal-muted mt-1">
                    Locked — already applied and can't be changed.
                </p>
                <p v-if="form.errors.referral_code" class="text-xs text-seal-danger mt-1">
                    {{ form.errors.referral_code }}
                </p>
            </section>

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

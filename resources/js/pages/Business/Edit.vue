<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    business: { type: Object, required: true },
    referralCode: { type: String, default: null },
    referralLocked: { type: Boolean, required: true },
});

const form = useForm({
    business_name: props.business.business_name,
    is_publicly_visible: props.business.is_publicly_visible,
    referral_code: props.referralCode ?? '',
});

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

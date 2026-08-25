<script setup>
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed, onBeforeUnmount } from 'vue';
import { useImageUpload } from '@/composables/useImageUpload';

const props = defineProps({
    mode: { type: String, required: true }, // 'ai' | 'team'
    quota: { type: Object, required: true },
});

const MAX_IMAGES = 3;

const form = useForm({
    name: '',
    description: '',
    images: [],
    sample_type: 'template',
});

const { previews, fileError, isCompressing, onFilesSelected, removeImage, resetImages } =
    useImageUpload(form, MAX_IMAGES);

const justSubmitted = ref(false);
const payingRequestFee = ref(false);

// Team mode has two states: they've already paid for a request (an unused
// voucher exists) and can fill out the form, or they haven't and need to
// pay first before the form appears at all.
const teamNeedsPayment = computed(() => props.mode === 'team' && ! props.quota.can_request_from_admins);

const copy = computed(() =>
    props.mode === 'ai'
        ? {
            heading: 'Generate it instantly with AI',
            blurb: "Describe the look you want, or attach a reference image. Our AI drafts a print-ready template in seconds — review it and approve if you like it, or reject and try again (up to 3 attempts).",
            badge: 'Instant, AI-drafted',
            submitIdle: 'Generate certificate',
            successBanner: '✓ Draft ready — scroll down to review and approve it.',
        }
        : {
            heading: 'Let our design team craft it for you',
            blurb: "Tell us the look you want, colors, style, mood, or attach a sample certificate as reference. One of our certificate designers will turn it into a polished, print-ready template.",
            badge: 'Made by our team',
            submitIdle: 'Request custom certificate',
            successBanner: "✓ Request received — one of our designers is already reviewing it.",
        }
);

const submitMessages = [
    props.mode === 'ai' ? 'Sending your description to the AI…' : 'Sending to our design team…',
    'Packaging your reference…',
    'Almost there…',
    'Just a little longer…',
];
const submitMessageIndex = ref(0);
let submitMessageTimer = null;

function startSubmitMessageCycle() {
    submitMessageIndex.value = 0;
    submitMessageTimer = setInterval(() => {
        submitMessageIndex.value = (submitMessageIndex.value + 1) % submitMessages.length;
    }, 1400);
}
function stopSubmitMessageCycle() {
    clearInterval(submitMessageTimer);
    submitMessageTimer = null;
}
onBeforeUnmount(stopSubmitMessageCycle);

const submitLabel = computed(() => {
    if (isCompressing.value) return 'Preparing your images…';
    if (form.processing) return submitMessages[submitMessageIndex.value];
    return copy.value.submitIdle;
});

function submit() {
    const routeName = props.mode === 'ai' ? 'certificate-templates.generate' : 'certificate-template-requests.store';

    startSubmitMessageCycle();
    form.post(route(routeName), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            resetImages();
            justSubmitted.value = true;
            setTimeout(() => (justSubmitted.value = false), 6000);
        },
        onFinish: () => stopSubmitMessageCycle(),
    });
}

function payForRequest() {
    payingRequestFee.value = true;
    router.post(route('payments.template-request-fee'), {}, {
        onFinish: () => (payingRequestFee.value = false),
    });
}
</script>

<template>
    <div class="bg-white rounded-card border border-seal-line p-4 space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-seal-ink">{{ copy.heading }}</p>
                <p class="text-xs text-seal-muted mt-1">{{ copy.blurb }}</p>
            </div>
            <span class="shrink-0 text-[10px] font-mono uppercase px-2 py-1 rounded bg-seal-navy/10 text-seal-navy whitespace-nowrap">
                {{ copy.badge }}
            </span>
        </div>

        <!-- Team mode, unpaid: pay first, form appears after -->
        <div v-if="teamNeedsPayment" class="space-y-2">
            <p class="text-xs text-seal-muted">
                A one-time fee of ₦{{ quota.fee_naira }} covers one custom design from our team.
            </p>
            <button
                type="button"
                @click="payForRequest"
                :disabled="payingRequestFee"
                class="bg-seal-navy text-white text-sm font-medium px-4 py-2 rounded-lg disabled:opacity-50 flex items-center gap-2"
            >
                <span v-if="payingRequestFee" class="inline-block w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                {{ payingRequestFee ? 'Redirecting…' : `Pay ₦${quota.fee_naira} to request from our team` }}
            </button>
        </div>

        <!-- AI mode, or team mode with an unused paid voucher -->
        <form v-else @submit.prevent="submit" class="space-y-3">
            <div v-if="justSubmitted" class="bg-seal-sage/10 border border-seal-sage/30 text-seal-sage text-sm rounded-lg px-3 py-2.5">
                {{ copy.successBanner }}
            </div>

            <div>
                <input
                    v-model="form.name"
                    type="text"
                    placeholder="Template name (e.g. Gold Foil Classic)"
                    class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                />
                <p v-if="form.errors.name" class="text-xs text-seal-danger mt-1">{{ form.errors.name }}</p>
            </div>

            <div>
                <textarea
                    v-model="form.description"
                    rows="3"
                    placeholder="e.g. Deep green and gold, elegant serif, floral corner border…"
                    class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                ></textarea>
                <p v-if="form.errors.description" class="text-xs text-seal-danger mt-1">{{ form.errors.description }}</p>
            </div>

            <div>
                <label class="block text-sm text-seal-ink mb-1">
                    Reference image(s) <span class="text-seal-muted font-normal">(optional, up to {{ MAX_IMAGES }})</span>
                </label>
                <input
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                    :disabled="form.images.length >= MAX_IMAGES || isCompressing"
                    @change="onFilesSelected"
                    class="w-full text-sm text-seal-ink file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-seal-navy file:text-white file:text-sm file:font-medium disabled:opacity-50"
                />
                <p v-if="isCompressing" class="text-xs text-seal-muted mt-1 flex items-center gap-1.5">
                    <span class="inline-block w-3 h-3 border-2 border-seal-navy/30 border-t-seal-navy rounded-full animate-spin"></span>
                    Optimizing image…
                </p>
                <p v-if="fileError" class="text-xs text-seal-danger mt-1">{{ fileError }}</p>
                <p v-if="form.errors.images" class="text-xs text-seal-danger mt-1">{{ form.errors.images }}</p>
                <p v-if="form.errors['images.0']" class="text-xs text-seal-danger mt-1">{{ form.errors['images.0'] }}</p>

                <div v-if="previews.length" class="flex flex-wrap gap-2 mt-2">
                    <div v-for="(p, i) in previews" :key="p.url" class="relative w-20 h-20 rounded-lg overflow-hidden border border-seal-line">
                        <img :src="p.url" :alt="p.name" class="w-full h-full object-cover" />
                        <button type="button" @click="removeImage(i)" class="absolute top-0.5 right-0.5 bg-black/60 text-white rounded-full w-5 h-5 text-xs leading-5">×</button>
                    </div>
                </div>
            </div>

            <div v-if="previews.length" class="pt-1">
                <p class="text-xs font-medium text-seal-ink mb-1">What kind of image is this?</p>
                <div class="flex gap-4 text-xs text-seal-ink">
                    <label class="flex items-center gap-1.5"><input type="radio" v-model="form.sample_type" value="template" /> Clean sample / mockup</label>
                    <label class="flex items-center gap-1.5"><input type="radio" v-model="form.sample_type" value="hardcopy" /> Photo of an issued certificate</label>
                </div>
            </div>

            <button
                type="submit"
                :disabled="form.processing || isCompressing"
                class="bg-seal-navy text-white text-sm font-medium px-4 py-2 rounded-lg disabled:opacity-50 flex items-center gap-2"
            >
                <span v-if="form.processing || isCompressing" class="inline-block w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"></span>
                {{ submitLabel }}
            </button>
        </form>
    </div>
</template>

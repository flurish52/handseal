<script setup>
import { useForm, Head, router } from '@inertiajs/vue3';
import { ref, computed, onBeforeUnmount } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    templates: { type: Array, default: () => [] },
    requests: { type: Array, default: () => [] },
    builtins: { type: Array, default: () => [] },
});

// Defensive fallbacks in case a prop ever arrives as null/undefined
// despite the defaults above (e.g. explicitly passed as null from the backend).
const templates = computed(() => props.templates ?? []);
const requests = computed(() => props.requests ?? []);
const builtins = computed(() => props.builtins ?? []);

const MAX_IMAGES = 3;
const MAX_UPLOAD_MB = 2;       // target size after compression
const MAX_RAW_INPUT_MB = 25;   // sanity ceiling before we even try to compress (huge phone panoramas etc.)
const MAX_DIMENSION = 1920;    // longest edge, px
const ACCEPTED_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

const form = useForm({
    name: '',
    description: '',
    images: [],
    sample_type: 'template',
});

const previews = ref([]);
const fileError = ref('');
const activePreview = ref(null); // { title, url }
const isCompressing = ref(false);
const justSubmitted = ref(false);

/**
 * Client-side image compression. Draws the image to a canvas capped at
 * MAX_DIMENSION on its longest edge, then re-encodes as JPEG, stepping
 * quality down until the result is under MAX_UPLOAD_MB (or quality
 * bottoms out). Falls back to the original file if it's already small
 * enough that compression isn't worth doing.
 */
function compressImage(file) {
    return new Promise((resolve, reject) => {
        if (file.size <= MAX_UPLOAD_MB * 1024 * 1024) {
            resolve(file);
            return;
        }

        const objectUrl = URL.createObjectURL(file);
        const img = new Image();

        img.onload = () => {
            let { width, height } = img;
            if (width > MAX_DIMENSION || height > MAX_DIMENSION) {
                const scale = MAX_DIMENSION / Math.max(width, height);
                width = Math.round(width * scale);
                height = Math.round(height * scale);
            }

            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0, width, height);

            const tryQuality = (quality) => {
                canvas.toBlob(
                    (blob) => {
                        if (!blob) {
                            URL.revokeObjectURL(objectUrl);
                            reject(new Error('Compression failed'));
                            return;
                        }
                        const underLimit = blob.size <= MAX_UPLOAD_MB * 1024 * 1024;
                        if (underLimit || quality <= 0.4) {
                            URL.revokeObjectURL(objectUrl);
                            const compressed = new File(
                                [blob],
                                file.name.replace(/\.\w+$/, '.jpg'),
                                { type: 'image/jpeg', lastModified: Date.now() }
                            );
                            resolve(compressed);
                        } else {
                            tryQuality(quality - 0.15);
                        }
                    },
                    'image/jpeg',
                    quality
                );
            };

            tryQuality(0.85);
        };

        img.onerror = () => {
            URL.revokeObjectURL(objectUrl);
            reject(new Error('Could not read image'));
        };

        img.src = objectUrl;
    });
}

async function onFilesSelected(e) {
    fileError.value = '';
    const incoming = Array.from(e.target.files ?? []);
    e.target.value = '';

    for (const file of incoming) {
        if (form.images.length >= MAX_IMAGES) {
            fileError.value = `You can attach up to ${MAX_IMAGES} images.`;
            break;
        }
        if (!ACCEPTED_TYPES.includes(file.type)) {
            fileError.value = 'Only JPG, PNG, or WEBP images are allowed.';
            continue;
        }
        if (file.size > MAX_RAW_INPUT_MB * 1024 * 1024) {
            fileError.value = `That image is too large to process. Try a smaller photo.`;
            continue;
        }

        isCompressing.value = true;
        try {
            const processed = await compressImage(file);
            form.images.push(processed);
            previews.value.push({ url: URL.createObjectURL(processed), name: processed.name });
        } catch (err) {
            fileError.value = 'One of the images could not be processed. Try a different photo.';
        } finally {
            isCompressing.value = false;
        }
    }
}

function removeImage(index) {
    URL.revokeObjectURL(previews.value[index].url);
    previews.value.splice(index, 1);
    form.images.splice(index, 1);
}

function resetImages() {
    previews.value.forEach((p) => URL.revokeObjectURL(p.url));
    previews.value = [];
    form.images = [];
}

function activate(template) {
    router.patch(route('certificate-templates.activate', template.id));
}

function destroy(template) {
    if (confirm(`Delete "${template.name}"? This can't be undone.`)) {
        router.delete(route('certificate-templates.destroy', template.id));
    }
}

// Rotating submit-button copy so a slow upload never looks stuck.
const submitMessages = [
    'Sending to our design team…',
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
    return 'Request custom certificate';
});

function requestCustom() {
    startSubmitMessageCycle();
    form.post(route('certificate-template-requests.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            resetImages();
            justSubmitted.value = true;
            setTimeout(() => (justSubmitted.value = false), 6000);
        },
        onFinish: () => {
            stopSubmitMessageCycle();
        },
    });
}

function retryRequest(req) {
    router.post(route('certificate-template-requests.retry', req.id));
}

function discardRequest(req) {
    if (confirm(`Discard the request "${req.name}"?`)) {
        router.delete(route('certificate-template-requests.destroy', req.id));
    }
}

function openPreview(title, url) {
    if (!url) return; // no preview_url yet (e.g. backend didn't compute one) — nothing to show
    activePreview.value = { title, url };
}

function closePreview() {
    activePreview.value = null;
}

function statusLabel(status) {
    return status === 'active' ? 'Active' : 'Draft, needs review';
}

function requestStatusLabel(status) {
    return {
        pending: 'With our design team',
        in_review: 'Being designed',
        declined: 'Needs changes',
    }[status] ?? status ?? 'With our design team';
}

function requestStatusHint(status) {
    return {
        pending: "Our design team has it, we'll notify you the moment your certificate is ready to approve.",
        in_review: "One of our designers is actively working on your certificate right now.",
        declined: 'See the note below, then feel free to send us an updated request.',
    }[status] ?? '';
}
</script>

<template>
    <Head title="Certificate templates" />

    <div class="p-4 space-y-6">
        <h1 class="font-serif text-xl font-semibold text-seal-navy">Certificate templates</h1>

        <!-- Built-in presets -->
        <div v-if="builtins.length">
            <p class="text-sm font-medium text-seal-ink mb-2">Built-in presets</p>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <div
                    v-for="b in builtins"
                    :key="b.key"
                    class="bg-white rounded-card border border-seal-line overflow-hidden cursor-pointer group"
                    @click="openPreview(b.label, b.preview_url)"
                >
                    <div class="relative h-28 bg-seal-line/20 overflow-hidden">
                        <iframe
                            v-if="b.preview_url"
                            :src="b.preview_url"
                            class="absolute top-0 left-0 w-[420px] h-[297px] origin-top-left scale-[0.27] pointer-events-none"
                            loading="lazy"
                            sandbox=""
                        ></iframe>
                        <div v-else class="w-full h-full flex items-center justify-center text-[10px] text-seal-muted">
                            No preview
                        </div>
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                            <span class="opacity-0 group-hover:opacity-100 text-[11px] font-medium text-white bg-black/60 px-2 py-1 rounded transition-opacity">
                                Preview
                            </span>
                        </div>
                    </div>
                    <p class="text-xs text-seal-ink px-2 py-2 truncate">{{ b.label }}</p>
                </div>
            </div>
        </div>

        <!-- Request a custom template -->
        <form @submit.prevent="requestCustom" class="bg-white rounded-card border border-seal-line p-4 space-y-3">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-medium text-seal-ink">Let our design team craft it for you</p>
                    <p class="text-xs text-seal-muted mt-1">
                        Tell us the look you want, colors, style, mood or attach a sample certificate as
                        reference. One of our certificate designers will turn it into a polished, print-ready
                        template, no design skills needed on your end. Once you approve it, it's yours to reuse
                        for free, forever.
                    </p>
                </div>
                <span class="shrink-0 text-[10px] font-mono uppercase px-2 py-1 rounded bg-seal-navy/10 text-seal-navy whitespace-nowrap">
                    Made by our team
                </span>
            </div>

            <!-- Success reassurance banner -->
            <div
                v-if="justSubmitted"
                class="bg-seal-sage/10 border border-seal-sage/30 text-seal-sage text-sm rounded-lg px-3 py-2.5"
            >
                ✓ Request received — one of our designers is already reviewing it. We'll let you know the moment
                it's ready for your approval.
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
                    <div
                        v-for="(p, i) in previews"
                        :key="p.url"
                        class="relative w-20 h-20 rounded-lg overflow-hidden border border-seal-line"
                    >
                        <img :src="p.url" :alt="p.name" class="w-full h-full object-cover" />
                        <button
                            type="button"
                            @click="removeImage(i)"
                            class="absolute top-0.5 right-0.5 bg-black/60 text-white rounded-full w-5 h-5 text-xs leading-5"
                        >
                            ×
                        </button>
                    </div>
                </div>
            </div>
            <div v-if="previews.length" class="pt-1">
                <p class="text-xs font-medium text-seal-ink mb-1">What kind of image is this?</p>
                <div class="flex gap-4 text-xs text-seal-ink">
                    <label class="flex items-center gap-1.5">
                        <input type="radio" v-model="form.sample_type" value="template" />
                        Clean sample / mockup
                    </label>
                    <label class="flex items-center gap-1.5">
                        <input type="radio" v-model="form.sample_type" value="hardcopy" />
                        Photo of an issued certificate
                    </label>
                </div>
            </div>
            <button
                type="submit"
                :disabled="form.processing || isCompressing"
                class="bg-seal-navy text-white text-sm font-medium px-4 py-2 rounded-lg disabled:opacity-50 flex items-center gap-2"
            >
                <span
                    v-if="form.processing || isCompressing"
                    class="inline-block w-3.5 h-3.5 border-2 border-white/40 border-t-white rounded-full animate-spin"
                ></span>
                {{ submitLabel }}
            </button>
        </form>

        <!-- In-flight / declined requests -->
        <div v-if="requests.length">
            <p class="text-sm font-medium text-seal-ink mb-2">Requests in progress</p>
            <div class="space-y-2">
                <div
                    v-for="req in requests"
                    :key="req.id"
                    class="bg-white rounded-card border border-seal-line p-4"
                >
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-seal-ink">{{ req.name }}</p>
                        <span
                            class="text-[10px] font-mono uppercase px-2 py-0.5 rounded"
                            :class="{
                    'bg-seal-brass/15 text-seal-brass': req.status !== 'declined',
                    'bg-seal-danger/15 text-seal-danger': req.status === 'declined',
                }"
                        >
                {{ requestStatusLabel(req.status) }}
            </span>
                    </div>
                    <p class="text-xs text-seal-muted mt-1">{{ req.description }}</p>
                    <p class="text-xs text-seal-muted mt-2">{{ requestStatusHint(req.status) }}</p>
                    <p v-if="req.admin_note" class="text-xs text-seal-danger mt-2">{{ req.admin_note }}</p>
                    <div v-if="req.status === 'pending'" class="flex gap-3 text-xs font-medium mt-3">
                        <button @click="discardRequest(req)" class="text-seal-danger">Cancel request</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom templates -->
        <div>
            <p class="text-sm font-medium text-seal-ink mb-2">Your custom templates</p>
            <div v-if="templates.length" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <div
                    v-for="template in templates"
                    :key="template.id"
                    class="bg-white rounded-card border border-seal-line overflow-hidden"
                >
                    <div
                        class="relative h-28 bg-seal-line/20 overflow-hidden cursor-pointer group"
                        @click="openPreview(template.name, template.preview_url)"
                    >
                        <iframe
                            v-if="template.preview_url"
                            :src="template.preview_url"
                            class="absolute top-0 left-0 w-[420px] h-[297px] origin-top-left scale-[0.27] pointer-events-none"
                            loading="lazy"
                            sandbox=""
                        ></iframe>
                        <div v-else class="w-full h-full flex items-center justify-center text-[10px] text-seal-muted">
                            No preview
                        </div>
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors flex items-center justify-center">
                            <span class="opacity-0 group-hover:opacity-100 text-[11px] font-medium text-white bg-black/60 px-2 py-1 rounded transition-opacity">
                                Preview
                            </span>
                        </div>
                    </div>
                    <div class="p-3">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-medium text-seal-ink truncate">{{ template.name }}</p>
                        </div>
                        <span
                            class="inline-block text-[10px] font-mono uppercase px-1.5 py-0.5 rounded mt-1"
                            :class="template.status === 'active' ? 'bg-seal-sage/15 text-seal-sage' : 'bg-seal-brass/15 text-seal-brass'"
                        >
                            {{ statusLabel(template.status) }}
                        </span>
                        <div class="flex gap-3 text-xs font-medium mt-2">
                            <button v-if="template.status !== 'active'" @click="activate(template)" class="text-seal-sage">
                                Approve
                            </button>
                            <button @click="destroy(template)" class="text-seal-danger">Delete</button>
                        </div>
                    </div>
                </div>
            </div>

            <p v-else class="text-sm text-seal-muted text-center py-8">
                No custom templates yet. Request one above.
            </p>
        </div>
    </div>

    <!-- Preview modal -->
    <div
        v-if="activePreview"
        class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
        @click.self="closePreview"
    >
        <div class="bg-white rounded-card w-full max-w-3xl overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-seal-line">
                <p class="text-sm font-medium text-seal-ink">{{ activePreview.title }}</p>
                <button @click="closePreview" class="text-seal-muted text-lg leading-none">×</button>
            </div>
            <iframe
                :src="activePreview.url"
                class="w-full h-[70vh] border-0"
                sandbox=""
            ></iframe>
        </div>
    </div>
</template>

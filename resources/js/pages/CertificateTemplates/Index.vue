<script setup>
import {Head, router} from '@inertiajs/vue3';
import {ref, computed} from 'vue';
import GenerateForm from './Partials/GenerateForm.vue';
import RequestsInProgress from './Partials/RequestsInProgress.vue';
import TemplateGrid from './Partials/TemplateGrid.vue';
import PreviewModal from './Partials/PreviewModal.vue';
import TemplatePaywall from "@/pages/CertificateTemplates/Partials/TemplatePaywall.vue";
import MarketplaceGrid from "@/pages/CertificateTemplates/Partials/MarketplaceGrid.vue";
import PurchaseModal from "@/pages/CertificateTemplates/Partials/PurchaseModal.vue";

const props = defineProps({
    templates: {type: Array, default: () => []},
    requests: {type: Array, default: () => []},
    marketplace: {type: Array, default: () => []},
    walletBalanceKobo: {type: Number, default: null},
    activeCertificateTemplateId: {type: Number, default: null},
    quota: {
        type: Object,
        default: () => ({
            attempts_remaining: 3,
            maxed_out: false,
            has_active_template: false,
            has_draft_template: false,
            can_generate_free: true,
            can_request_from_admins: false,
            fee_naira: 1000
        }),
    },
});

const templates = computed(() => props.templates ?? []);
const requests = computed(() => props.requests ?? []);
const marketplace = computed(() => props.marketplace ?? []);

// Two independent, non-wrapping galleries — custom templates and platform
// (marketplace/builtin) templates never mix while paging next/prev.
const customGalleryItems = computed(() =>
    templates.value.map((t) => ({
        id: t.id,
        title: t.name,
        url: t.preview_url,
        type: 'html',
        actionable: false,
    }))
);

const marketplaceGalleryItems = computed(() =>
    marketplace.value.map((t) => ({
        id: t.id,
        title: t.name,
        // Prefer the live render (cheap Blade render); the static thumbnail
        // is only a fallback if a preview URL somehow isn't available.
        url: t.preview_url ?? t.thumbnail_url,
        type: t.preview_url ? 'html' : 'image',
        actionable: true,
        isPremium: t.is_premium,
        owned: t.owned,
        priceNaira: t.price_naira,
    }))
);

const activeGallery = ref(null); // { items, index }

function openGallery(items, id) {
    const index = items.findIndex((i) => i.id === id);
    if (index === -1) return;
    activeGallery.value = { items, index };
}

function closePreview() {
    activeGallery.value = null;
}

function nextPreview() {
    if (!activeGallery.value) return;
    if (activeGallery.value.index < activeGallery.value.items.length - 1) {
        activeGallery.value.index++;
    }
}

function prevPreview() {
    if (!activeGallery.value) return;
    if (activeGallery.value.index > 0) {
        activeGallery.value.index--;
    }
}

const currentPreview = computed(() =>
    activeGallery.value ? activeGallery.value.items[activeGallery.value.index] : null
);
const isFirstPreview = computed(() => !activeGallery.value || activeGallery.value.index === 0);
const isLastPreview = computed(
    () => !activeGallery.value || activeGallery.value.index === activeGallery.value.items.length - 1
);

// Buy / Use, fired from inside the preview modal.
const purchaseTarget = ref(null);

function handleBuy() {
    if (!currentPreview.value) return;
    const template = marketplace.value.find((t) => t.id === currentPreview.value.id);
    if (!template) return;
    purchaseTarget.value = template;
    closePreview();
}

function handleUse() {
    if (!currentPreview.value) return;
    router.patch(route('certificate-templates.activate', currentPreview.value.id));
    closePreview();
}

// "What's active right now" — custom active template wins, else whichever
// marketplace item matches the business's active_certificate_template_id,
// else just the first (lowest sort_order) marketplace item.
const activeTemplate = computed(() => {
    const custom = templates.value.find((t) => t.status === 'active');
    if (custom) return { name: custom.name, preview_url: custom.preview_url, kind: 'Your custom design' };

    const active = marketplace.value.find((t) => t.id === props.activeCertificateTemplateId)
        ?? marketplace.value[0];
    return active ? { name: active.name, preview_url: active.preview_url, kind: 'Built-in preset' } : null;
});

const activeMode = ref(props.quota.can_generate_free ? 'ai' : 'team');
const aiBlocked = computed(() => !props.quota.can_generate_free);
const showPaywall = ref(false);

const paywallReason = computed(() => (props.quota.has_active_template ? 'has_active_template' : 'exhausted'));
const paywallMessage = computed(() =>
    props.quota.has_active_template
        ? "You've already got an active custom certificate. Pay to unlock a fresh set of AI attempts for a new design."
        : "You've used all 3 AI attempts. Pay to reset and get 3 more."
);

function onAiTabClick() {
    if (aiBlocked.value) {
        showPaywall.value = true;
        return;
    }
    activeMode.value = 'ai';
}
</script>

<template>
    <Head title="Certificate templates"/>

    <div class="p-4 space-y-6">
        <h1 class="font-serif text-xl font-semibold text-seal-navy">Certificate templates</h1>

        <!-- 1. Orientation: what's live right now -->
        <div v-if="activeTemplate" class="bg-white rounded-card border-2 border-seal-navy p-4 flex items-center gap-4">
            <div class="relative w-24 h-16 shrink-0 bg-seal-line/20 rounded overflow-hidden">
                <iframe v-if="activeTemplate.preview_url" :src="activeTemplate.preview_url"
                        class="absolute top-0 left-0 w-[420px] h-[297px] origin-top-left scale-[0.16] pointer-events-none"
                        loading="lazy" sandbox=""></iframe>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-mono uppercase text-seal-navy">Currently active</p>
                <p class="text-sm font-medium text-seal-ink truncate">{{ activeTemplate.name }}</p>
                <p class="text-[11px] text-seal-muted">{{ activeTemplate.kind }}, used on every certificate you
                    issue</p>
            </div>
        </div>

        <TemplateGrid :templates="templates" @preview="(id) => openGallery(customGalleryItems, id)" />

        <MarketplaceGrid
            :marketplace="marketplace"
            :wallet-balance-kobo="walletBalanceKobo"
            @preview="(id) => openGallery(marketplaceGalleryItems, id)"
        />

        <!-- 2. Status: anything they're already waiting on -->
        <RequestsInProgress :requests="requests"/>

        <!-- 3. Primary action: create/generate -->
        <div class="space-y-3">
            <div class="flex gap-2">
                <button
                    type="button"
                    @click="onAiTabClick"
                    class="text-xs font-medium px-3 py-1.5 rounded-full border transition-colors"
                    :class="activeMode === 'ai' && !aiBlocked ? 'bg-seal-navy text-white border-seal-navy' : 'text-seal-ink border-seal-line'"
                >
                    Generate with AI
                </button>
                <button
                    type="button"
                    @click="activeMode = 'team'"
                    class="text-xs font-medium px-3 py-1.5 rounded-full border transition-colors"
                    :class="activeMode === 'team' ? 'bg-seal-navy text-white border-seal-navy' : 'text-seal-ink border-seal-line'"
                >
                    Request from our team
                </button>
            </div>

            <GenerateForm :mode="activeMode" :key="activeMode" :quota="quota"/>
        </div>
    </div>

    <PreviewModal
        :preview="currentPreview"
        :is-first="isFirstPreview"
        :is-last="isLastPreview"
        @close="closePreview"
        @next="nextPreview"
        @prev="prevPreview"
        @buy="handleBuy"
        @use="handleUse"
    />

    <PurchaseModal
        v-if="purchaseTarget"
        :template="purchaseTarget"
        :wallet-balance-kobo="walletBalanceKobo"
        @close="purchaseTarget = null"
    />

    <TemplatePaywall
        v-if="showPaywall"
        :reason="paywallReason"
        :message="paywallMessage"
        :fee-naira="quota.fee_naira"
        @close="showPaywall = false"
    />
</template>

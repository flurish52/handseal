<script setup>
import {Head, router} from '@inertiajs/vue3';
import {ref, computed} from 'vue';
import BuiltinPresets from './Partials/BuiltinPresets.vue';
import QuotaBanner from './Partials/QuotaBanner.vue';
import GenerateForm from './Partials/GenerateForm.vue';
import RequestsInProgress from './Partials/RequestsInProgress.vue';
import TemplateGrid from './Partials/TemplateGrid.vue';
import PreviewModal from './Partials/PreviewModal.vue';
import TemplatePaywall from "@/pages/CertificateTemplates/Partials/TemplatePaywall.vue";

const props = defineProps({
    templates: {type: Array, default: () => []},
    requests: {type: Array, default: () => []},
    builtins: {type: Array, default: () => []},
    defaultBuiltinKey: {type: String, default: null},
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

function selectBuiltin(key) {
    router.post(route('certificate-templates.default-builtin'), {builtin_template_key: key}, {preserveScroll: true});
}

// Defensive fallbacks in case a prop ever arrives as null/undefined
// despite the defaults above.
const templates = computed(() => props.templates ?? []);
const requests = computed(() => props.requests ?? []);
const builtins = computed(() => props.builtins ?? []);

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

const activePreview = ref(null); // { title, url }

function openPreview(title, url) {
    if (!url) return;
    activePreview.value = {title, url};
}

function closePreview() {
    activePreview.value = null;
}

function goToTeamMode() {
    activeMode.value = 'team';
}

const activeTemplate = computed(() => {
    const custom = templates.value.find((t) => t.status === 'active');
    if (custom) return { name: custom.name, preview_url: custom.preview_url, kind: 'Your custom design' };

    const builtin = builtins.value.find((b) => b.key === props.defaultBuiltinKey) ?? builtins.value[0];
    return builtin ? { name: builtin.label, preview_url: builtin.preview_url, kind: 'Built-in preset' } : null;
});
</script>

<template>
    <Head title="Certificate templates"/>

    <div class="p-4 space-y-6">
        <h1 class="font-serif text-xl font-semibold text-seal-navy">Certificate templates</h1>
        <div v-if="activeTemplate" class="bg-white rounded-card border-2 border-seal-navy p-4 flex items-center gap-4">
            <div class="relative w-24 h-16 shrink-0 bg-seal-line/20 rounded overflow-hidden">
                <iframe v-if="activeTemplate.preview_url" :src="activeTemplate.preview_url"
                        class="absolute top-0 left-0 w-[420px] h-[297px] origin-top-left scale-[0.16] pointer-events-none"
                        loading="lazy" sandbox=""></iframe>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-mono uppercase text-seal-navy">Currently active</p>
                <p class="text-sm font-medium text-seal-ink truncate">{{ activeTemplate.name }}</p>
                <p class="text-[11px] text-seal-muted">{{ activeTemplate.kind }} — used on every certificate you issue</p>
            </div>
        </div>


        <BuiltinPresets
            :builtins="builtins"
            :active-key="defaultBuiltinKey"
            @preview="openPreview"
            @select="selectBuiltin"/>

        <div class="space-y-3">
            <QuotaBanner :quota="quota" @go-to-team="goToTeamMode"/>

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

        <RequestsInProgress :requests="requests"/>

        <TemplateGrid :templates="templates" @preview="openPreview"/>
    </div>

    <PreviewModal :preview="activePreview" @close="closePreview"/>

    <TemplatePaywall
        v-if="showPaywall"
        :reason="paywallReason"
        :message="paywallMessage"
        :fee-naira="quota.fee_naira"
        @close="showPaywall = false"
    />
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import PurchaseModal from './PurchaseModal.vue';
import Icon from "@/Components/Icons/Icon.vue";

const props = defineProps({
    marketplace: { type: Array, default: () => [] },
    walletBalanceKobo: { type: Number, default: 0 },
});
const emit = defineEmits(['preview']);

const purchaseTarget = ref(null);

function openPurchase(template) {
    purchaseTarget.value = template;
}

function useTemplate(template) {
    router.patch(route('certificate-templates.activate', template.id));
}
</script>

<template>
    <div v-if="marketplace.length">
        <p class="text-sm font-medium text-seal-ink mb-2">Template marketplace</p>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            <div v-for="t in marketplace" :key="t.id" class="bg-white rounded-card border border-seal-line overflow-hidden">
                <div class="relative h-28 bg-seal-line/20 overflow-hidden cursor-pointer"
                     @click="emit('preview', t.id)">
                    <img v-if="t.thumbnail_url"
                         :src="t.thumbnail_url"
                         class="w-full h-full object-cover"
                         loading="lazy" alt=""
                    />
                    <div v-else class="w-full h-full flex items-center justify-center text-[10px] text-seal-muted">No preview</div>
                    <Icon
                        v-if="t.is_premium"
                        name="crown"
                        class="absolute top-1.5 right-1.5 w-4 h-4 text-seal-brass" />
                </div>
                <div class="p-3 space-y-1.5">
                    <p class="text-xs font-medium text-seal-ink truncate">{{ t.name }}</p>
                    <p v-if="t.is_premium" class="text-[11px] text-seal-muted">₦{{ t.price_naira }}</p>
                    <button
                        v-if="t.owned"
                        type="button"
                        class="w-full text-[11px] font-medium text-seal-navy border border-seal-navy/30 rounded px-2 py-1 hover:bg-seal-navy/5"
                        @click="useTemplate(t)"
                    >
                        Use this template
                    </button>
                    <button
                        v-else
                        type="button"
                        class="w-full text-[11px] font-medium text-white bg-seal-navy rounded px-2 py-1 hover:bg-seal-navy/90"
                        @click="openPurchase(t)"
                    >
                        Buy
                    </button>
                </div>
            </div>
        </div>
    </div>

    <PurchaseModal
        v-if="purchaseTarget"
        :template="purchaseTarget"
        :wallet-balance-kobo="walletBalanceKobo"
        @close="purchaseTarget = null"
    />
</template>

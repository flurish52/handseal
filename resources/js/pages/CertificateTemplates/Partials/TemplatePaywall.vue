<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import PaywallModal from '@/Components/PaywallModal.vue';

const props = defineProps({
    message: { type: String, required: true },
    feeNaira: { type: [Number, String], required: true },
});
defineEmits(['close']);

const paying = ref(false);
function payToReset() {
    paying.value = true;
    router.post(route('payments.template_reset'), {}, { onFinish: () => (paying.value = false) });
}

const actions = computed(() => [{
    key: 'reset', label: `Pay ₦${props.feeNaira} for 3 more attempts`, style: 'primary',
    loading: paying.value, onClick: payToReset,
}]);
</script>

<template>
    <PaywallModal title="AI template limit reached" :message="message" :actions="actions" @close="$emit('close')" />
</template>

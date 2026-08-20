<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    prefix: { type: String, default: 'By continuing, you agree to our' },
});
</script>

<template>
    <p class="text-xs leading-relaxed text-seal-muted">
        {{ prefix }}
        <Link :href="route('terms.service')" class="font-medium text-seal-brass hover:text-seal-navy">
            Terms of Service
        </Link>
        and
        <Link :href="route('privacy.policy')" class="font-medium text-seal-brass hover:text-seal-navy">
            Privacy Policy
        </Link>.
    </p>
</template>

<script setup>
import {Head} from '@inertiajs/vue3';
import HeroSection from '@/Components/Landing/HeroSection.vue';
import HowItWorks from '@/Components/Landing/HowItWorks.vue';
import VerifyWidget from '@/Components/Landing/VerifyWidget.vue';
import TradesStrip from '@/Components/Landing/TradesStrip.vue';
import DirectoryTeaser from '@/Components/Landing/DirectoryTeaser.vue';
import PricingSection from '@/Components/Landing/PricingSection.vue';
import FinalCta from '@/Components/Landing/FinalCta.vue';
import GuestLayout from "@/Layouts/GuestLayout.vue";

const props = defineProps({
    canLogin: {
        type: Boolean,
        default: true,
    },
    canRegister: {
        type: Boolean,
        default: true,
    },

    featuredBusinesses: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="HandSeal — Certificates worth trusting"/>
        <HeroSection :can-register="$page.props.canRegister"/>
        <HowItWorks/>
        <VerifyWidget/>
        <TradesStrip/>
        <DirectoryTeaser :featured-businesses="featuredBusinesses"/>
        <PricingSection :can-register="canRegister"/>
        <FinalCta :can-register="canRegister"/>
</template>

<template>
    <div class="min-h-screen flex bg-seal-paper">
        <!-- Brand panel — desktop only -->
        <div
            class="hidden lg:flex lg:w-[45%] xl:w-2/5 relative flex-col justify-between bg-seal-navy px-12 py-14 overflow-hidden">
            <!-- decorative seal ring -->
            <div
                class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full border border-seal-brass/20"></div>
            <div
                class="pointer-events-none absolute -right-10 top-1/3 h-64 w-64 rounded-full border border-seal-brass/15"></div>

            <ApplicationLogo/>

            <div class="relative">
                <p class="font-serif text-2xl leading-snug text-white">
                    Every certificate,<br/>verifiably yours.
                </p>
                <p class="mt-3 max-w-xs text-sm text-white/60">
                    Issue, track, and verify credentials for your students and programmes — all in one place.
                </p>
            </div>

            <p class="relative text-xs text-white/40">&copy; {{ new Date().getFullYear() }} HandSeal</p>
        </div>

        <!-- Form panel -->
        <div class="flex flex-1 flex-col justify-center px-6 py-12 sm:px-10 lg:px-16 xl:px-20">
            <div class="mx-auto w-full max-w-sm">
                <!-- Mobile-only brand mark -->
                <ApplicationLogo />

            </div>
            <slot/>
            <div class="flex items-center justify-center mt-8 border-t border-seal-line pt-6">
                <LegalLinks/>
            </div>
        </div>
    </div>
</template>

<script setup>
import {Link} from '@inertiajs/vue3';
import Icon from '@/Components/Icons/Icon.vue';
import GuestLayout from "@/Layouts/GuestLayout.vue";
import LegalLinks from "@/Components/LegalLinks.vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
</script>

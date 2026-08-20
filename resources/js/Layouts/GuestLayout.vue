<script setup>
import SiteNav from "@/Components/Landing/SiteNav.vue";
import SiteFooter from "@/Components/Landing/SiteFooter.vue";
import InstallPWAButton from "@/Components/InstallPWAButton.vue";
</script>

<template>
    <div class="bg-seal-paper font-sans text-seal-ink antialiased">
        <SiteNav :is-authenticated="$page.props.auth?.user != null" :can-register="$page.props.canRegister" />
        <slot/>
        <SiteFooter />
    <InstallPWAButton />
    </div>

</template>

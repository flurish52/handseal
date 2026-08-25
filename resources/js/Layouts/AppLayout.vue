<template>
    <div class="min-h-screen bg-seal-paper pb-20">
        <!-- Top bar -->
        <header class="sticky top-0 z-10 bg-seal-navy text-white px-4 py-3 flex items-center justify-between">
            <span class="font-serif text-base font-semibold">HandSeal</span>
            <div class="flex items-center gap-2">
                <HeaderBillingStatus />
                <UserMenu />
            </div>
        </header>

        <!-- Flash message -->
        <div
            v-if="flashSuccess"
            class="mx-4 mt-3 rounded-lg bg-seal-sage/15 text-seal-sage text-sm px-3 py-2"
        >
            {{ flashSuccess }}
        </div>
        <div
            v-if="flashError"
            class="mx-4 mt-3 rounded-lg bg-seal-danger/10 text-seal-danger text-sm px-3 py-2"
        >
            {{ flashError }}
        </div>
        <InstallPWAButton />
        <!-- Page content -->
        <main>
            <slot />
        </main>

        <!-- Bottom nav -->
        <nav class="fixed bottom-0 inset-x-0 bg-white border-t border-seal-line px-2 pt-2 pb-[max(0.5rem,env(safe-area-inset-bottom))]">
            <div class="grid grid-cols-4">
                <Link
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    class="flex flex-col items-center gap-1 py-1"
                >
                    <Icon
                        :name="item.icon"
                        :size="20"
                        :class="item.active ? 'text-seal-navy' : 'text-seal-muted'"
                    />
                    <span
                        class="text-[10px] font-sans"
                        :class="item.active ? 'text-seal-navy font-semibold' : 'text-seal-muted'"
                    >
                        {{ item.label }}
                    </span>
                </Link>
            </div>
        </nav>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { watch } from 'vue';
import Icon from '@/Components/Icons/Icon.vue';
import UserMenu from "@/Components/UserMenu.vue";
import InstallPWAButton from "@/Components/InstallPWAButton.vue";
import HeaderBillingStatus from "@/Components/HeaderBillingStatus.vue";

const page = usePage();

const flashSuccess = computed(() => page.props.flash?.success ?? null);
const flashError = computed(() => page.props.flash?.error ?? null);

const navItems = computed(() => [
    { label: 'Home', icon: 'home', href: route('dashboard'), active: route().current('dashboard') },
    { label: 'Students', icon: 'users', href: route('students.index'), active: route().current('students.*') },
    { label: 'Certification', icon: 'award', href: route('certificates.index'), active: route().current('certificates.*') },
    { label: 'Programmes', icon: 'bookOpen', href: route('programmes.index'), active: route().current('programmes.*') },
]);



watch(
    () => page.props.flash?.download_url,
    (url) => {
        if (url) {
            window.location = url;
        }
    }
);
</script>

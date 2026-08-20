<template>
    <div ref="menuRef" class="relative">
        <button
            type="button"
            class="flex items-center gap-1.5 group"
            @click="open = !open"
        >
            <span
                class="flex h-8 w-8 items-center justify-center rounded-full text-white text-xs font-semibold
                       bg-gradient-to-br from-seal-navy to-seal-navy/80
                       ring-2 ring-white/50 group-hover:ring-white/40
                       transition-shadow"
            >
                {{ initials }}
            </span>
            <Icon
                name="chevronDown"
                :size="14"
                class="text-white/50 transition-transform"
                :class="{ 'rotate-180': open }"
            />
        </button>

        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-if="open"
                class="absolute right-0 mt-2 w-48 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black/5 py-1 z-20"
            >
                <div class="px-3 py-2 border-b border-seal-line">
                    <p class="text-sm font-medium text-seal-ink truncate">{{ user.name }}</p>
                    <p class="text-xs text-seal-muted truncate">{{ user.email }}</p>
                </div>

                <Link
                    v-for="item in links"
                    :key="item.label"
                    :href="item.href"
                    class="flex items-center gap-2 px-3 py-2 text-sm text-seal-ink hover:bg-seal-paper"
                    @click="open = false"
                >
                    <Icon :name="item.icon" :size="16" class="text-seal-navy" />
                    {{ item.label }}
                </Link>

                <button
                    type="button"
                    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-seal-paper text-left"
                    @click="logout"
                >
                    <Icon name="logOut" :size="16" />
                    Log out
                </button>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import Icon from '@/Components/Icons/Icon.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const open = ref(false);
const menuRef = ref(null);

const links = computed(() => [
    { label: 'Certificate templates', icon: 'edit', href: route('certificate-templates.index') },
    { label: 'Referrals', icon: 'referral', href: route('referrals.index') },
    { label: 'Settings', icon: 'settings', href: route('business.edit') },
]);

function logout() {
    open.value = false;
    router.post(route('logout'));
}

function handleClickOutside(e) {
    if (menuRef.value && !menuRef.value.contains(e.target)) {
        open.value = false;
    }
}

const initials = computed(() => {
    const name = user.value?.name?.trim();

    if (!name) return '?';

    const parts = name.split(/\s+/).filter(Boolean);

    if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase();
    }

    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
});

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

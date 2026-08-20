<script setup>
import {Link} from '@inertiajs/vue3';

defineProps({
    isAuthenticated: {type: Boolean, default: false},
    canRegister: {type: Boolean, default: true},
});
</script>

<template>
    <nav class="sticky top-0 z-50 border-b border-seal-line-dark bg-seal-navy/90 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-3.5">
            <Link href="/" class="flex items-center gap-2.5 font-serif text-lg font-semibold text-seal-paper">
                <svg viewBox="0 0 40 40" fill="none" class="h-7 w-7 shrink-0">
                    <circle cx="20" cy="20" r="19" fill="#c79a46"/>
                    <circle cx="20" cy="20" r="19" stroke="#e7c577" stroke-width="1"/>
                    <text x="20" y="26" text-anchor="middle" font-family="Fraunces, serif" font-size="16"
                          font-weight="600" fill="#101a30">HS
                    </text>
                </svg>
                HandSeal
            </Link>

            <div class="hidden items-center gap-7 md:flex">
                <a href="/#how" class="text-sm font-medium text-seal-muted-dark transition hover:text-seal-paper">How it
                    works</a>
                <Link :href="route('verify.lookup')"
                      class="text-sm font-medium text-seal-muted-dark transition hover:text-seal-paper">
                    Verify a certificate
                </Link>
                <Link :href="route('directory.index')"
                      class="text-sm font-medium text-seal-muted-dark transition hover:text-seal-paper">
                    Find a business
                </Link>
                <a href="/#pricing" class="text-sm font-medium text-seal-muted-dark transition hover:text-seal-paper">Pricing</a>
            </div>
            <div class="flex items-center gap-3">
                <Link
                    v-if="isAuthenticated"
                    :href="route('dashboard')"
                    class="rounded-[10px] bg-gradient-to-b from-seal-brass-light to-seal-brass px-5 py-2.5 text-sm font-semibold text-seal-navy-3 shadow-[0_1px_0_rgba(255,255,255,.35)_inset]"
                >
                    Go to dashboard
                </Link>
                <template v-else>
                    <Link :href="route('login')"
                          class="hidden text-sm font-medium text-seal-muted-dark transition hover:text-seal-paper sm:block">
                        Sign in
                    </Link>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="rounded-[10px] bg-gradient-to-b from-seal-brass-light to-seal-brass px-5 py-2.5 text-sm font-semibold text-seal-navy-3 shadow-[0_1px_0_rgba(255,255,255,.35)_inset] transition hover:shadow-[0_1px_0_rgba(255,255,255,.4)_inset,0_10px_26px_-8px_rgba(199,154,70,.75)]"
                    >
                        Start free
                    </Link>
                </template>
            </div>
        </div>
    </nav>
</template>

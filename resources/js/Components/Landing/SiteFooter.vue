<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <footer class="bg-seal-navy-3 px-6 py-11 text-[#7d87a0]">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-5">
            <div class="flex items-center gap-2.5 font-serif text-[15px] text-seal-muted-dark">
                <svg viewBox="0 0 40 40" fill="none" class="h-[18px] w-[18px]">
                    <circle cx="20" cy="20" r="19" fill="#c79a46" />
                    <text x="20" y="26" text-anchor="middle" font-family="Fraunces, serif" font-size="16" font-weight="600" fill="#101a30">HS</text>
                </svg>
                HandSeal
            </div>
            <div class="flex flex-wrap gap-6">
                <Link :href="route('verify.lookup')" class="text-[13.5px] text-[#9aa4bd] hover:text-seal-paper">Verify a certificate</Link>
                <Link :href="route('directory.index')" class="text-[13.5px] text-[#9aa4bd] hover:text-seal-paper">Find a business</Link>
                <Link :href="route('login')" class="text-[13.5px] text-[#9aa4bd] hover:text-seal-paper">Sign in</Link>
            </div>
        </div>
        <div class="mx-auto mt-5 max-w-6xl text-[12.5px] text-[#5c6788]">
            © {{ new Date().getFullYear() }} HandSeal. Certificate issuance and verification for trade &amp; vocational businesses.
        </div>
    </footer>
</template>

<template>
    <!-- Wordmark + seal mark -->
    <Link href="/" class="mb-8 flex flex-col items-center gap-3">
                <span
                    class="flex h-11 w-11 items-center justify-center rounded-full text-sm font-semibold text-white"
                    style="background: radial-gradient(circle at 35% 30%, #D3A75C, #B8863B 65%); box-shadow: inset 0 -2px 4px rgba(0,0,0,0.2), 0 1px 2px rgba(0,0,0,0.15);"
                >
                    HS
                </span>
        <span class="font-serif text-xl font-semibold text-seal-navy">HandSeal</span>
    </Link>

</template>
<script setup>
import {Link} from "@inertiajs/vue3";
</script>

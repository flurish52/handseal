<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    businesses: { type: Array, required: true },
    q: { type: String, default: '' },
});

const search = ref(props.q);

function submit() {
    router.get(route('directory.index'), { q: search.value }, { preserveState: true });
}
</script>

<template>
    <Head title="Business directory" />

    <div class="min-h-screen bg-seal-paper p-6">
        <div class="max-w-md mx-auto">
            <p class="text-xs uppercase tracking-widest text-seal-brass font-semibold mb-1">HandSeal</p>
            <h1 class="font-serif text-xl font-semibold text-seal-navy mb-4">Business directory</h1>

            <form @submit.prevent="submit" class="mb-4">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search business name…"
                    class="w-full rounded-lg border border-seal-line px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                />
            </form>

            <div class="space-y-2">
                <div
                    v-for="business in props.businesses"
                    :key="business.business_name"
                    class="bg-white rounded-card border border-seal-line p-4 flex items-center justify-between"
                >
                    <p class="text-sm font-medium text-seal-ink">{{ business.business_name }}</p>
                    <p class="text-xs text-seal-muted">{{ business.certificates_count }} issued</p>
                </div>

                <p v-if="props.businesses.length === 0" class="text-sm text-seal-muted text-center py-8">
                    No businesses found.
                </p>
            </div>

            <Link :href="route('verify.lookup')" class="block text-center text-xs text-seal-muted mt-6">
                Have a certificate number? Verify it here
            </Link>
        </div>
    </div>
</template>

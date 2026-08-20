<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    businessName: { type: String, required: true },
    activeStudentsCount: { type: Number, required: true },
    lifetimeCertificatesCount: { type: Number, required: true },
});
</script>

<template>
    <Head title="Dashboard" />
     <div class="p-4 space-y-6">
            <div>
                <p class="text-xs text-seal-muted">Welcome back</p>
                <h1 class="font-serif text-xl font-semibold text-seal-navy">{{ businessName }}</h1>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white rounded-card border border-seal-line p-5">
                    <p class="text-3xl font-serif font-semibold text-seal-navy">{{ activeStudentsCount }}</p>
                    <p class="text-xs text-seal-muted mt-1">Active students</p>
                </div>
                <div class="bg-white rounded-card border border-seal-line p-5">
                    <p class="text-3xl font-serif font-semibold text-seal-brass">{{ lifetimeCertificatesCount }}</p>
                    <p class="text-xs text-seal-muted mt-1">Certificates issued</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <Link
                    :href="route('certificates.guest.create')"
                    class="bg-seal-navy text-white text-sm font-medium text-center py-3 rounded-lg"
                >
                    Quick issue
                </Link>
                <Link
                    :href="route('students.index')"
                    class="bg-white border border-seal-line text-seal-navy text-sm font-medium text-center py-3 rounded-lg"
                >
                    Add student
                </Link>
            </div>
        </div>
</template>

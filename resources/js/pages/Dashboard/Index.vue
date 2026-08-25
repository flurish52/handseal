<!-- resources/js/Pages/Dashboard.vue -->
<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import Icon from '@/Components/Icons/Icon.vue';

const props = defineProps({
    businessName: { type: String, required: true },
    activeStudentsCount: { type: Number, required: true },
    lifetimeCertificatesCount: { type: Number, required: true },
    certificatesThisMonth: { type: Number, required: true },
    recentCertificates: { type: Array, required: true }, // last 5: { id, recipient_name, certificate_number, download_url, created_at }
});

const page = usePage();
const billing = computed(() => page.props.auth?.billing);
const sub = computed(() => billing.value?.subscription);

const naira = (kobo) => '₦' + (kobo / 100).toLocaleString();

const quotaLabel = computed(() =>
    sub.value?.is_unlimited ? 'Unlimited' : `${sub.value?.certs_used}/${sub.value?.included_certs}`
);

const lowQuota = computed(() =>
    sub.value && !sub.value.is_unlimited && sub.value.remaining <= 1
);
</script>

<template>
    <Head title="Dashboard" />
        <div class="p-4 space-y-6">
            <div>
                <p class="text-xs text-seal-muted">Welcome back</p>
                <h1 class="font-serif text-xl font-semibold text-seal-navy">{{ businessName }}</h1>
            </div>

            <!-- Billing summary -->
            <Link
                v-if="billing"
                :href="route('billing.index')"
                class="block bg-seal-navy rounded-card p-4 text-white space-y-2"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-white/60">{{ sub.plan_name }} plan</p>
                        <p class="text-lg font-serif font-semibold">{{ quotaLabel }}<span v-if="!sub.is_unlimited" class="text-xs font-sans font-normal text-white/60"> certs this period</span></p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-white/60">Wallet</p>
                        <p class="text-lg font-serif font-semibold">{{ naira(billing.wallet_balance_kobo) }}</p>
                    </div>
                </div>
                <p v-if="lowQuota" class="text-xs text-seal-brass bg-white/10 rounded-md px-2 py-1 inline-block">
                    {{ sub.remaining === 0 ? 'Quota used up' : `Only ${sub.remaining} certificate left` }} this period
                </p>
                <p class="text-xs text-white/50">Manage billing →</p>
            </Link>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-2">
                <div class="bg-white rounded-card border border-seal-line p-2.5">
                    <p class="text-lg font-serif font-semibold text-seal-navy">{{ activeStudentsCount }}</p>
                    <p class="text-[11px] text-seal-muted mt-0.5">Active students</p>
                </div>
                <div class="bg-white rounded-card border border-seal-line p-2.5">
                    <p class="text-lg font-serif font-semibold text-seal-brass">{{ certificatesThisMonth }}</p>
                    <p class="text-[11px] text-seal-muted mt-0.5">Certificates · This month</p>
                </div>
                <div class="bg-white rounded-card border border-seal-line p-2.5">
                    <p class="text-lg font-serif font-semibold text-seal-ink">{{ lifetimeCertificatesCount }}</p>
                    <p class="text-[11px] text-seal-muted mt-0.5">Certificates · Lifetime</p>
                </div>
            </div>

            <!-- Quick actions -->
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

            <!-- Recent certificates -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-seal-ink">Recently issued</p>
                    <Link :href="route('certificates.index')" class="text-xs text-seal-navy">View all</Link>
                </div>
                <div v-if="!recentCertificates" class="text-xs text-seal-muted bg-white rounded-card border border-seal-line p-4 text-center">
                    No certificates issued yet.
                </div>
                <div v-else class="bg-white rounded-card border border-seal-line divide-y divide-seal-line">

                  <a  v-for="c in recentCertificates"
                    :key="c.id"
                    :href="c.download_url"
                    class="flex items-center gap-3 px-4 py-3"
                    >
                    <div class="shrink-0 w-8 h-8 rounded-full bg-seal-paper flex items-center justify-center">
                        <Icon name="award" :size="14" class="text-seal-brass" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-seal-ink truncate">{{ c.recipient_name }}</p>
                        <p class="text-xs text-seal-muted font-mono">{{ c.certificate_number }}</p>
                    </div>
                    <Icon name="download" :size="14" class="text-seal-muted shrink-0" />
                    </a>
                </div>
            </div>
        </div>
</template>

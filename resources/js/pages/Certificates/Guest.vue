<script setup>
import {useForm, usePage, Head, Link, router} from '@inertiajs/vue3';
import {ref, watch, computed} from 'vue';
import ProgrammeSelect from '@/Components/Programme/ProgrammeSelect.vue';
import CertificatePaywall from "@/pages/Certificates/Partials/CertificatePaywall.vue";
import WalletDeductionBanner from "@/Components/WalletDeductionBanner.vue";
import SearchInput from '@/Components/SearchInput.vue';
import FilterPanel, { buildDefaultFilterValues } from '@/Components/FilterPanel.vue';
const props = defineProps({
    programmes: {type: Array, required: true},
    builtins: {type: Array, required: true},
    customTemplates: {type: Array, required: true},
    guestCertificates: { type: Array, default: () => [] },
});

const page = usePage();
const paywall = ref(null);

const form = useForm({
    recipient_name: '',
    programme_id: '',
    start_date: '',
    end_date: '',
    template_choice: '',
});

function formatLocalDate(d) {
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
}

function todayStr() {
    return formatLocalDate(new Date());
}

function addWeeks(dateStr, weeks) {
    const [y, m, d] = dateStr.split('-').map(Number);
    const date = new Date(y, m - 1, d);
    date.setDate(date.getDate() + weeks * 7);
    return formatLocalDate(date);
}

function recalcEndDate() {
    const programme = props.programmes.find((p) => String(p.id) === String(form.programme_id));
    if (programme?.typical_duration && form.start_date) {
        form.end_date = addWeeks(form.start_date, programme.typical_duration);
    }
}

watch(() => form.programme_id, (id) => {
    if (!id) return;
    form.start_date = todayStr();
    recalcEndDate();
});

watch(() => form.start_date, recalcEndDate);


function templateParts() {
    const [type, value] = form.template_choice.split(':');
    return {
        builtin_template_key: type === 'builtin' ? value : null,
        certificate_template_id: type === 'custom' ? value : null,
    };
}

function preview() {
    const params = new URLSearchParams({
        recipient_name: form.recipient_name,
        programme_id: form.programme_id,
        start_date: form.start_date,
        end_date: form.end_date,
    });

    window.open(route('certificates.guest.preview') + '?' + params.toString(), '_blank');
}

function submit() {
    form.transform((data) => ({
        recipient_name: data.recipient_name,
        programme_id: data.programme_id,
        start_date: data.start_date,
        end_date: data.end_date,
    })).post(route('certificates.guest.store'), {
        preserveScroll: true,
        onSuccess: () => {
            const reason = page.props.flash?.paywall;

            if (reason) {
                paywall.value = { reason, message: page.props.flash?.error ?? 'Payment required to continue.' };
                return;
            }

            form.reset();
        },
    });
}


const searchQuery = ref('');

function matchesSearch(cert, query) {
    if (!query) return true;
    const needle = query.trim().toLowerCase();
    if (!needle) return true;
    return [cert.recipient_name, cert.certificate_number].some(
        (field) => field && String(field).toLowerCase().includes(needle),
    );
}

const filtersConfig = computed(() => [
    {
        key: 'programme_id',
        label: 'Programme',
        type: 'select',
        options: props.programmes.map((p) => ({ value: p.id, label: p.name })),
    },
    { key: 'issued_at', label: 'Issued date', type: 'dateRange' },
]);

function inDateRange(dateValue, range) {
    if (!range || (!range.from && !range.to)) return true;
    if (!dateValue) return false;
    const date = new Date(dateValue);
    if (range.from && date < new Date(range.from)) return false;
    if (range.to && date > new Date(`${range.to}T23:59:59`)) return false;
    return true;
}

function matchesFilters(cert, filters) {
    if (filters.programme_id && String(cert.programme_id) !== String(filters.programme_id)) return false;
    if (!inDateRange(cert.issued_at, filters.issued_at)) return false;
    return true;
}

const filterValues = ref(buildDefaultFilterValues(filtersConfig.value));

const hasActiveSearchOrFilters = computed(() => {
    if (searchQuery.value.trim()) return true;
    return filtersConfig.value.some((f) => {
        const val = filterValues.value[f.key];
        return f.type === 'dateRange' ? !!(val?.from || val?.to) : !!val;
    });
});

function clearSearchAndFilters() {
    searchQuery.value = '';
    filterValues.value = buildDefaultFilterValues(filtersConfig.value);
}

const filteredGuestCertificates = computed(() =>
    props.guestCertificates.filter(
        (cert) => matchesSearch(cert, searchQuery.value) && matchesFilters(cert, filterValues.value),
    ),
);

watch(
    () => usePage().props.flash?.download_url,
    (url) => {
        if (url) {
            window.location.href = url;
            form.reset();
            router.reload({ only: ['guestCertificates'] });
        }
    },
    { immediate: true }
);

</script>

<template>
    <Head title="Issue guest certificate"/>
    <WalletDeductionBanner/>
    <div class="p-4 space-y-4">
        <div>
            <h1 class="font-serif text-xl font-semibold text-seal-navy">Issue guest certificate</h1>
            <p class="text-xs text-seal-muted">For students not tracked in-app</p>
        </div>

        <form @submit.prevent="submit"
              class="bg-white rounded-card border border-seal-line p-4 space-y-3">
            <div>
                <label class="text-xs text-seal-muted">Full name</label>
                <input
                    v-model="form.recipient_name"
                    type="text"
                    placeholder="Type name…"
                    class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                />
                <p v-if="form.errors.recipient_name" class="text-xs text-seal-danger mt-1">{{
                        form.errors.recipient_name
                    }}</p>
            </div>

            <div>
                <label class="text-xs text-seal-muted">Programme</label>
                <ProgrammeSelect
                    v-model="form.programme_id"
                    :programmes="props.programmes"
                    :error="form.errors.programme_id"
                />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs text-seal-muted">Start date</label>
                    <input
                        v-model="form.start_date"
                        type="date"
                        class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                    />
                    <p v-if="form.errors.start_date" class="text-xs text-seal-danger mt-1">{{
                            form.errors.start_date
                        }}</p>
                </div>
                <div>
                    <label class="text-xs text-seal-muted">Completion date</label>
                    <input
                        v-model="form.end_date"
                        type="date"
                        class="w-full rounded-lg border border-seal-line px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-seal-navy"
                    />
                    <p v-if="form.errors.end_date" class="text-xs text-seal-danger mt-1">{{ form.errors.end_date }}</p>
                </div>
            </div>



            <div class="flex gap-2">
                <button
                    type="button"
                    @click="preview"
                    class="flex-1 border border-seal-line text-seal-ink text-sm font-medium py-3 rounded-lg"
                >
                    Preview
                </button>
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="flex-1 bg-seal-navy text-white text-sm font-medium py-3 rounded-lg disabled:opacity-50"
                >
                    Generate certificate now
                </button>
            </div>
            <p class="text-[11px] text-seal-muted text-center">
                No tracking history needed, issued instantly
            </p>
        </form>


        <div class="pt-2 space-y-3">
            <h2 class="text-sm font-medium text-seal-ink">Guest certificates issued</h2>

            <div v-if="props.guestCertificates.length > 0" class="flex flex-col sm:flex-row gap-2.5">
                <div class="flex-1">
                    <SearchInput v-model="searchQuery" placeholder="Search by recipient or certificate number" />
                </div>
                <FilterPanel :filters="filtersConfig" v-model="filterValues" />
            </div>

            <p v-if="hasActiveSearchOrFilters" class="text-xs text-seal-muted">
                Showing {{ filteredGuestCertificates.length }} of {{ props.guestCertificates.length }}
            </p>

            <div v-if="props.guestCertificates.length === 0" class="text-center py-8 text-xs text-seal-muted">
                No guest certificates issued yet.
            </div>

            <div v-else-if="filteredGuestCertificates.length === 0" class="text-center py-8">
                <p class="text-xs text-seal-muted">No certificates match.</p>
                <button @click="clearSearchAndFilters" class="mt-2 text-xs font-medium text-seal-navy hover:underline">
                    Clear search & filters
                </button>
            </div>

            <div v-else class="space-y-2">
                <div
                    v-for="cert in filteredGuestCertificates"
                    :key="cert.id"
                    class="bg-white rounded-card border border-seal-line p-4 flex items-center justify-between"
                >
                    <div>
                        <p class="text-sm font-medium text-seal-ink">{{ cert.recipient_name }}</p>
                        <p class="text-xs text-seal-muted mt-0.5">{{ cert.programme?.name }}</p>
                        <p class="text-[10px] font-mono text-seal-muted mt-1">{{ cert.certificate_number }}</p>
                    </div>
                    <a :href="route('certificates.download', cert.id)" class="text-xs font-medium text-seal-navy">
                        Download
                    </a>
                </div>
            </div>
        </div>

        <Link :href="route('certificates.index')" class="block text-center text-xs text-seal-muted">
            ← Back to certificates
        </Link>
    </div>


    <CertificatePaywall
        v-if="paywall"
        :reason="paywall?.reason"
        :message="paywall?.message"
        :recipient-name="form.recipient_name"
        :programme-id="form.programme_id"
        :start-date="form.start_date"
        :end-date="form.end_date"
        @close="paywall = null"
    />
</template>

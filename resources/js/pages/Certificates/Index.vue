<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Icon from '@/Components/Icons/Icon.vue';
import SearchInput from '@/Components/SearchInput.vue';
import FilterPanel, { buildDefaultFilterValues } from '@/Components/FilterPanel.vue';

const props = defineProps({
    certificates: { type: Array, required: true },
});

const showIssueMenu = ref(false);

/* ---------------------------------------------------------------------- */
/* Search                                                                  */
/* ---------------------------------------------------------------------- */

const searchQuery = ref('');

function matchesSearch(cert, query) {
    if (!query) return true;
    const needle = query.trim().toLowerCase();
    if (!needle) return true;

    return [cert.recipient_name, cert.certificate_number].some(
        (field) => field && String(field).toLowerCase().includes(needle),
    );
}

/* ---------------------------------------------------------------------- */
/* Filters                                                                 */
/* ---------------------------------------------------------------------- */

// NOTE: assumes each certificate carries `programme` (with `id`/`name`) and
// `created_at` for the issue date. Rename below if your payload differs.
const filtersConfig = computed(() => [
    {
        key: 'programme_id',
        label: 'Programme',
        type: 'select',
        options: uniqueProgrammes.value.map((p) => ({ value: p.id, label: p.name })),
    },
    { key: 'created_at', label: 'Issued date', type: 'dateRange' },
]);

const uniqueProgrammes = computed(() => {
    const seen = new Map();
    for (const cert of props.certificates) {
        if (cert.programme?.id != null && !seen.has(cert.programme.id)) {
            seen.set(cert.programme.id, cert.programme);
        }
    }
    return [...seen.values()];
});

const filterValues = ref(buildDefaultFilterValues(filtersConfig.value));

function inDateRange(dateValue, range) {
    if (!range || (!range.from && !range.to)) return true;
    if (!dateValue) return false;

    const date = new Date(dateValue);
    if (range.from && date < new Date(range.from)) return false;
    if (range.to && date > new Date(`${range.to}T23:59:59`)) return false;

    return true;
}

function matchesFilters(cert, filters) {
    if (filters.programme_id && String(cert.programme?.id) !== String(filters.programme_id)) return false;
    if (!inDateRange(cert.created_at, filters.created_at)) return false;
    return true;
}

const hasActiveSearchOrFilters = computed(() => {
    if (searchQuery.value.trim()) return true;
    return filtersConfig.value.some((filter) => {
        const val = filterValues.value[filter.key];
        return filter.type === 'dateRange' ? !!(val?.from || val?.to) : !!val;
    });
});

function clearSearchAndFilters() {
    searchQuery.value = '';
    filterValues.value = buildDefaultFilterValues(filtersConfig.value);
}

const filteredCertificates = computed(() =>
    props.certificates.filter(
        (cert) => matchesSearch(cert, searchQuery.value) && matchesFilters(cert, filterValues.value),
    ),
);

const showIssueMenuEmpty = ref(false); // separate from the header's — they shouldn't share state
</script>

<template>
    <Head title="Certificates" />
     <div class="p-4 space-y-6">
         <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
             <div>
                 <h1 class="font-serif text-xl font-semibold text-seal-navy">Certificates Issued</h1>
                 <p v-if="hasActiveSearchOrFilters" class="text-xs text-seal-muted mt-1">
                     Showing {{ filteredCertificates.length }} of {{ props.certificates.length }}
                 </p>
             </div>

             <div class="flex items-center gap-2">
                 <Link
                     :href="route('certificate-templates.index')"
                     class="flex-1 sm:flex-none text-sm font-medium text-white bg-seal-brass px-3 py-2 rounded-lg flex items-center justify-center gap-1.5 hover:bg-seal-brass/90"
                 >
                     <Icon name="palette" :size="16" />
                     Templates
                 </Link>

                 <div class="relative flex-1 sm:flex-none">
                     <button
                         @click="showIssueMenu = !showIssueMenu"
                         class="w-full bg-seal-navy text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center justify-center gap-1.5"
                     >
                         <Icon name="plus" :size="16" />
                         Issue Certificate
                     </button>

                     <div
                         v-if="showIssueMenu"
                         class="absolute right-0 mt-2 w-44 rounded-lg bg-white shadow-lg ring-1 ring-black/5 py-1 z-20"
                     >
                         <Link
                             :href="route('students.index')"
                             class="block px-3 py-2 text-sm text-seal-ink hover:bg-seal-paper"
                         >
                             For a student
                         </Link>
                         <Link
                             :href="route('certificates.guest.create')"
                             class="block px-3 py-2 text-sm text-seal-ink hover:bg-seal-paper"
                         >
                             For a guest
                         </Link>
                     </div>
                 </div>
             </div>
         </div>

            <div v-if="props.certificates.length > 0" class="flex flex-col sm:flex-row gap-2.5">
                <div class="flex-1">
                    <SearchInput v-model="searchQuery" placeholder="Search by recipient or certificate number" />
                </div>
                <FilterPanel :filters="filtersConfig" v-model="filterValues" />
            </div>

            <div class="space-y-2">
                <div
                    v-for="cert in filteredCertificates"
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
                <div v-if="props.certificates.length === 0" class="text-center flex flex-col items-center justify-center py-12">
                    <p class="text-sm text-seal-ink font-medium">No certificates issued yet</p>
                    <p class="text-xs text-seal-muted mt-1">Tap "Issue" above to create your first one.</p>

                    <div class="relative w-full md:w-1/5 mt-4">
                        <button
                            @click="showIssueMenuEmpty = !showIssueMenuEmpty"
                            class="w-full bg-seal-navy text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center justify-center gap-1.5"
                        >
                            <Icon name="plus" :size="16" />
                            Issue Certificate
                        </button>

                        <div
                            v-if="showIssueMenuEmpty"
                            class="absolute left-0 right-0 mt-2 rounded-lg bg-white shadow-lg ring-1 ring-black/5 py-1 z-20"
                        >
                            <Link :href="route('students.index')" class="block px-3 py-2 text-sm text-seal-ink hover:bg-seal-paper">
                                For a student
                            </Link>
                            <Link :href="route('certificates.guest.create')" class="block px-3 py-2 text-sm text-seal-ink hover:bg-seal-paper">
                                For a guest
                            </Link>
                        </div>
                    </div>
                </div>

                <div v-else-if="filteredCertificates.length === 0" class="text-center py-12">
                    <p class="text-sm text-seal-ink font-medium">No certificates match</p>
                    <p class="text-xs text-seal-muted mt-1">Try a different search term or clear your filters.</p>
                    <button
                        @click="clearSearchAndFilters"
                        class="mt-3 text-sm font-medium text-seal-navy hover:underline"
                    >
                        Clear search & filters
                    </button>
                </div>
            </div>
        </div>
</template>

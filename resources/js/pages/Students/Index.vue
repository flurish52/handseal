<script setup>
import {ref, computed, watch} from 'vue';
import {router, Head, usePage} from '@inertiajs/vue3';
import StudentFormModal from '@/Components/StudentFormModal/StudentFormModal.vue';
import IssueCertificateModal from "@/Components/StudentFormModal/IssueCertificateModal.vue";
import SearchInput from '@/Components/SearchInput.vue';
import FilterPanel, { buildDefaultFilterValues } from '@/Components/FilterPanel.vue';

const props = defineProps({
    students: { type: Array, required: true },
    programmes: { type: Array, required: true },
    builtins: { type: Array, required: true },
    customTemplates: { type: Array, required: true },
});

const issuingFor = ref(null);

function openIssueModal(student) {
    issuingFor.value = student;
}

function onIssued() {
    issuingFor.value = null;
}

const modalOpen = ref(false);
const editingStudent = ref(null);

function openAddModal() {
    editingStudent.value = null;
    modalOpen.value = true;
}

function openEditModal(student) {
    editingStudent.value = student;
    modalOpen.value = true;
}

function destroy(student) {
    if (confirm(`Remove "${student.name}"? This can't be undone.`)) {
        router.delete(route('students.destroy', student.id), { preserveScroll: true });
    }
}

function toggleStatus(student) {
    const nextStatus = student.status === 'active' ? 'completed' : 'active';

    router.put(
        route('students.update', student.id),
        {
            programme_id: student.programme_id,
            name: student.name,
            phone: student.phone ?? '',
            start_at: student.start_at?.slice(0, 10) ?? '',
            end_at: student.end_at?.slice(0, 10) ?? '',
            status: nextStatus,
        },
        { preserveScroll: true },
    );
}

// Eligible once marked completed, OR once the end date has actually passed —
// covers both paths you asked for without waiting on a manual click.
function isCertificateEligible(student) {
    if (student.status === 'completed') return true;
    if (!student.end_at) return false;
    return new Date(student.end_at) <= new Date();
}

// Backend route/controller action for this still needs wiring — see chat notes.
function printCertificate(student) {
    router.visit(route('certificates.index', student.id));
}

function initials(name) {
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0].toUpperCase())
        .join('');
}

function formatDate(value) {
    if (!value) return '—';
    return new Date(value).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

const activeCount = computed(() => props.students.filter((s) => s.status === 'active').length);
const completedCount = computed(() => props.students.filter((s) => s.status === 'completed').length);

/* ---------------------------------------------------------------------- */
/* Search                                                                  */
/* ---------------------------------------------------------------------- */

// This page searches by enrollment number, name, and phone. Mount
// <SearchInput> on another page and match whatever fields make sense
// there — the component itself doesn't care.
const searchQuery = ref('');

function matchesSearch(student, query) {
    if (!query) return true;
    const needle = query.trim().toLowerCase();
    if (!needle) return true;

    return [student.name, student.enrollment_number, student.phone].some(
        (field) => field && String(field).toLowerCase().includes(needle),
    );
}

/* ---------------------------------------------------------------------- */
/* Filters                                                                 */
/* ---------------------------------------------------------------------- */

// NOTE: assumes a `completed_at` field on the student payload. If your
// backend uses a different name (or doesn't send this field yet), rename
// the key below and in `filtersConfig` — everything else keeps working.
const filtersConfig = [
    {
        key: 'status',
        label: 'Status',
        type: 'select',
        options: [
            { value: 'active', label: 'Active' },
            { value: 'completed', label: 'Completed' },
        ],
    },
    { key: 'start_at', label: 'Start date', type: 'dateRange' },
    { key: 'end_at', label: 'End date', type: 'dateRange' },
    { key: 'completed_at', label: 'Completed date', type: 'dateRange' },
];

const filterValues = ref(buildDefaultFilterValues(filtersConfig));

function inDateRange(dateValue, range) {
    if (!range || (!range.from && !range.to)) return true;
    if (!dateValue) return false;

    const date = new Date(dateValue);
    if (range.from && date < new Date(range.from)) return false;
    if (range.to && date > new Date(`${range.to}T23:59:59`)) return false;

    return true;
}

function matchesFilters(student, filters) {
    if (filters.status && student.status !== filters.status) return false;
    if (!inDateRange(student.start_at, filters.start_at)) return false;
    if (!inDateRange(student.end_at, filters.end_at)) return false;
    if (!inDateRange(student.completed_at, filters.completed_at)) return false;
    return true;
}

const hasActiveSearchOrFilters = computed(() => {
    if (searchQuery.value.trim()) return true;
    return filtersConfig.some((filter) => {
        const val = filterValues.value[filter.key];
        return filter.type === 'dateRange' ? !!(val?.from || val?.to) : !!val;
    });
});

function clearSearchAndFilters() {
    searchQuery.value = '';
    filterValues.value = buildDefaultFilterValues(filtersConfig);
}

const filteredStudents = computed(() =>
    props.students.filter(
        (student) => matchesSearch(student, searchQuery.value) && matchesFilters(student, filterValues.value),
    ),
);

watch(
    () => usePage().props.flash?.download_url,
    (url) => {
        if (url) {
            window.location.href = url;
            issuingFor.value = null; // close the modal now that the cert is issued
        }
    },
    { immediate: true }
);
</script>

<template>
    <Head title="Students" />
     <div class="p-4 sm:p-6 max-w-4xl mx-auto space-y-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="font-serif text-2xl font-semibold text-seal-navy">Students</h1>
                    <p class="text-xs text-seal-muted mt-1">
                        {{ props.students.length }} total
                        <span v-if="props.students.length"> · {{ activeCount }} active · {{ completedCount }} completed</span>
                        <span v-if="hasActiveSearchOrFilters"> · showing {{ filteredStudents.length }}</span>
                    </p>
                </div>

                <button
                    @click="openAddModal"
                    class="flex items-center gap-1.5 bg-seal-navy text-white text-sm font-medium px-4 py-2.5 rounded-lg hover:bg-seal-navy/90 transition-colors shrink-0"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                    Add student
                </button>
            </div>

            <div v-if="props.students.length > 0" class="flex flex-col sm:flex-row gap-2.5">
                <div class="flex-1">
                    <SearchInput v-model="searchQuery" placeholder="Search by name, enrollment number, or phone" />
                </div>
                <FilterPanel :filters="filtersConfig" v-model="filterValues" />
            </div>

            <div v-if="props.students.length === 0" class="border-2 border-dashed border-seal-line rounded-card py-16 text-center">
                <p class="text-sm font-medium text-seal-ink">No students yet</p>
                <p class="text-xs text-seal-muted mt-1">Add your first student to start tracking their programme.</p>
                <button
                    @click="openAddModal"
                    class="mt-4 text-sm font-medium text-seal-navy hover:underline"
                >
                    Add a student →
                </button>
            </div>

            <div v-else-if="filteredStudents.length === 0" class="border-2 border-dashed border-seal-line rounded-card py-16 text-center">
                <p class="text-sm font-medium text-seal-ink">No students match</p>
                <p class="text-xs text-seal-muted mt-1">Try a different search term or clear your filters.</p>
                <button
                    @click="clearSearchAndFilters"
                    class="mt-4 text-sm font-medium text-seal-navy hover:underline"
                >
                    Clear search & filters
                </button>
            </div>

            <div v-else class="space-y-2.5">
                <div
                    v-for="student in filteredStudents"
                    :key="student.id"
                    class="bg-white rounded-card border border-seal-line p-4 hover:border-seal-navy/30 transition-colors"
                >
                    <div class="flex sm:items-center gap-4">
                        <!-- Seal badge: signature element, echoes the wax-seal / HandSeal motif -->
                        <div class="shrink-0 p-0.5 rounded-full border-2 border-dashed border-seal-brass">
                            <div class="w-10 h-10 rounded-full bg-seal-navy text-white flex items-center justify-center font-serif text-sm font-semibold">
                                {{ initials(student.name) }}
                            </div>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="text-sm font-medium text-seal-ink break-words">{{ student.name }}</p>
                                <span
                                    class="inline-flex items-center gap-1 text-[10px] font-mono uppercase px-2 py-0.5 rounded-full shrink-0"
                                    :class="student.status === 'active' ? 'bg-seal-sage/15 text-seal-sage' : 'bg-seal-brass/15 text-seal-brass'"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full" :class="student.status === 'active' ? 'bg-seal-sage' : 'bg-seal-brass'"></span>
                                    {{ student.status }}
                                </span>
                            </div>
                            <p class="text-xs text-seal-muted mt-0.5 truncate">
                                {{ student.programme?.name ?? 'No programme' }}
                                <span v-if="student.enrollment_number"> · {{ student.enrollment_number }}</span>
                            </p>
                            <p class="text-[11px] text-seal-muted mt-1">
                                {{ formatDate(student.start_at) }} → {{ formatDate(student.end_at) }}
                            </p>
                        </div>

                        <!-- Desktop actions: inline, right-aligned -->
                        <div class="hidden sm:flex items-center gap-1.5 shrink-0">
                            <div v-if="isCertificateEligible(student)" class="flex items-center gap-1.5">
                                <a v-if="student.latest_certificate"
                                   :href="route('certificates.download', student.latest_certificate.id)"
                                   title="Download certificate"
                                   class="flex items-center gap-1.5 bg-seal-sage/10 text-seal-sage text-xs font-medium px-3 py-2 rounded-lg hover:bg-seal-sage/20 transition-colors"
                                >
                                    Download
                                </a>
                                <button
                                    @click="openIssueModal(student)"
                                    :title="student.latest_certificate ? 'Reissue certificate' : 'Issue certificate'"
                                    class="text-xs font-medium text-seal-muted px-3 py-2 rounded-lg hover:bg-seal-line/50 hover:text-seal-ink transition-colors"
                                >
                                    {{ student.latest_certificate ? 'Reissue' : 'Issue certificate' }}
                                </button>
                            </div>
                            <button
                                v-else
                                @click="toggleStatus(student)"
                                title="Mark completed"
                                class="text-xs font-medium text-seal-muted px-3 py-2 rounded-lg hover:bg-seal-line/50 hover:text-seal-ink transition-colors"
                            >
                                Mark completed
                            </button>

                            <button
                                @click="openEditModal(student)"
                                aria-label="Edit student"
                                class="text-seal-muted hover:text-seal-navy p-2 rounded-lg hover:bg-seal-line/50 transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                            </button>

                            <button
                                @click="destroy(student)"
                                aria-label="Remove student"
                                class="text-seal-muted hover:text-seal-danger p-2 rounded-lg hover:bg-seal-danger/10 transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14z" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Mobile actions: own row below a divider, primary action stretches, icons pinned right -->
                    <div class="flex sm:hidden items-center gap-1.5 mt-3 pt-3 border-t border-seal-line">
                        <div v-if="isCertificateEligible(student)" class="flex items-center gap-1.5 flex-1 min-w-0">
                            <a v-if="student.latest_certificate"
                               :href="route('certificates.download', student.latest_certificate.id)"
                               title="Download certificate"
                               class="flex-1 flex items-center justify-center gap-1.5 bg-seal-sage/10 text-seal-sage text-xs font-medium px-3 py-2.5 rounded-lg hover:bg-seal-sage/20 transition-colors"
                            >
                                Download
                            </a>
                            <button
                                @click="openIssueModal(student)"
                                :title="student.latest_certificate ? 'Reissue certificate' : 'Issue certificate'"
                                class="flex-1 text-xs font-medium text-seal-muted px-3 py-2.5 rounded-lg hover:bg-seal-line/50 hover:text-seal-ink transition-colors"
                            >
                                {{ student.latest_certificate ? 'Reissue' : 'Issue certificate' }}
                            </button>
                        </div>
                        <button
                            v-else
                            @click="toggleStatus(student)"
                            title="Mark completed"
                            class="flex-1 text-xs font-medium text-seal-muted px-3 py-2.5 rounded-lg hover:bg-seal-line/50 hover:text-seal-ink transition-colors"
                        >
                            Mark completed
                        </button>

                        <div class="flex items-center gap-1 shrink-0">
                            <button
                                @click="openEditModal(student)"
                                aria-label="Edit student"
                                class="text-seal-muted hover:text-seal-navy p-2 rounded-lg hover:bg-seal-line/50 transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                </svg>
                            </button>

                            <button
                                @click="destroy(student)"
                                aria-label="Remove student"
                                class="text-seal-muted hover:text-seal-danger p-2 rounded-lg hover:bg-seal-danger/10 transition-colors"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    <IssueCertificateModal
        v-if="issuingFor"
        :student="issuingFor"
        @close="issuingFor = null"
        @issued="onIssued"
    />
        <StudentFormModal
            :open="modalOpen"
            :student="editingStudent"
            :programmes="props.programmes"
            @close="modalOpen = false"
            :students="students"/>
</template>

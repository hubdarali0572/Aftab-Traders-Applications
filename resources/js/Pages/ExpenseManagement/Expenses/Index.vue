<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    expenses: Object,
    summary: Object,
    filters: Object,
});

const page = usePage();
const showFlash = ref(false);
const searchQuery = ref(props.filters?.search ?? '');
const statusFilter = ref(props.filters?.status ?? '');
const paymentMethod = ref(props.filters?.payment_method ?? '');
let timer = null;

const startTimer = () => {
    showFlash.value = true;
    if (timer) clearTimeout(timer);
    timer = setTimeout(() => { showFlash.value = false; }, 5000);
};

watch(
    () => [page.props.flash.success, page.props.flash.danger],
    ([newSuccess, newDanger]) => {
        if (newSuccess || newDanger) startTimer();
    },
    { immediate: true }
);

const isModalOpen = ref(false);
const selectedExpense = ref(null);

const openDeleteModal = (expense) => {
    selectedExpense.value = expense;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => { selectedExpense.value = null; }, 300);
};

const confirmDelete = () => {
    if (selectedExpense.value) {
        router.delete(route('expenses.destroy', selectedExpense.value.id), {
            onSuccess: () => closeModal(),
            onFinish: () => closeModal(),
        });
    }
};

const applySearch = () => {
    router.get(route('expenses.index'), {
        search: searchQuery.value || undefined,
        status: statusFilter.value || undefined,
        payment_method: paymentMethod.value || undefined,
    }, { preserveState: true, replace: true });
};

const clearSearch = () => {
    searchQuery.value = '';
    statusFilter.value = '';
    paymentMethod.value = '';
    applySearch();
};

const money = (v) => Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const formatDate = (value) => {
    if (!value) return '—';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return value;
    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const statusClass = (status) => ({
    draft: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
    approved: 'bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400',
    paid: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
    cancelled: 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400',
}[status] || 'bg-slate-100 text-slate-500');

const hasFilters = () =>
    props.filters?.search || props.filters?.status || props.filters?.payment_method;

const overviewStats = computed(() => [
    { title: 'Total Records', value: props.summary?.total_count ?? 0, tone: 'text-indigo-700', bg: 'bg-indigo-50 dark:bg-indigo-500/10', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { title: 'Financial Amount', value: `$${money(props.summary?.financial_amount)}`, tone: 'text-indigo-700', bg: 'bg-indigo-50 dark:bg-indigo-500/10', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', highlight: true },
    { title: 'Today', value: `$${money(props.summary?.today_amount)}`, tone: 'text-violet-700', bg: 'bg-violet-50 dark:bg-violet-500/10', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
    { title: 'This Month', value: `$${money(props.summary?.month_amount)}`, tone: 'text-sky-700', bg: 'bg-sky-50 dark:bg-sky-500/10', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
]);
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Expenses')" />

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">{{ $t('Expenses') }}</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">{{ $t('Track and manage all business expenses with full details.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Link :href="route('expense-history.index')" class="theme-form-back-link px-4 py-2 text-sm font-bold">{{ $t('Expense History') }}</Link>
                <Link :href="route('expenses.create')" class="theme-btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 5v14m7-7H5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ $t('Add Expense') }}
            </Link>
            </div>
        </div>

        <!-- Summary cards -->
        <div class="theme-table-card mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-indigo-50 to-white dark:from-indigo-950/30 dark:to-slate-800">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">{{ $t('Expense Overview') }}</h3>
                <p class="text-xs text-slate-400 mt-0.5">{{ $t('Financial amount includes approved and paid expenses only.') }}</p>
            </div>
            <div class="p-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    v-for="stat in overviewStats"
                    :key="stat.title"
                    class="flex items-start gap-3 p-4 rounded-xl border transition-shadow hover:shadow-md"
                    :class="stat.highlight ? 'border-indigo-200 dark:border-indigo-800 bg-indigo-50/40 dark:bg-indigo-950/20' : 'border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800/50'"
                >
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" :class="stat.bg">
                        <svg class="w-5 h-5" :class="stat.tone" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="stat.icon" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-[0.1em] text-slate-400 leading-tight">{{ $t(stat.title) }}</p>
                        <p class="text-base font-black mt-1 truncate" :class="stat.tone">{{ stat.value }}</p>
                    </div>
                </div>
            </div>
        </div>

        <transition name="fade">
            <div v-if="showFlash && ($page.props.flash.success || $page.props.flash.danger)"
                :class="[$page.props.flash.success ? 'bg-indigo-50 border-indigo-500 text-indigo-800 dark:bg-indigo-500/10 dark:text-indigo-200' : 'bg-slate-100 border-slate-400 text-slate-700 dark:bg-slate-700/80 dark:text-slate-200']"
                class="mb-6 flex items-center p-4 border-l-4 rounded-r-xl shadow-sm"
            >
                <div class="flex-shrink-0">
                    <svg v-if="$page.props.flash.success" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                    <svg v-else class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"/></svg>
                </div>
                <p class="ml-3 text-sm font-bold">{{ $page.props.flash.success || $page.props.flash.danger }}</p>
                <button @click="showFlash = false" class="ml-auto opacity-50 hover:opacity-100 transition-opacity">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
        </transition>

        <div class="theme-table-card">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50">
                <form @submit.prevent="applySearch" class="flex flex-col xl:flex-row gap-3">
                    <input v-model="searchQuery" type="text" class="theme-form-input flex-1" placeholder="Search expense #, name, payee, employee, invoice..." />
                    <select v-model="statusFilter" class="theme-form-input w-full xl:w-40">
                        <option value="">{{ $t('All Status') }}</option>
                        <option value="draft">{{ $t('Draft') }}</option>
                        <option value="approved">{{ $t('Approved') }}</option>
                        <option value="paid">{{ $t('Paid') }}</option>
                        <option value="cancelled">{{ $t('Cancelled') }}</option>
                    </select>
                    <select v-model="paymentMethod" class="theme-form-input w-full xl:w-40">
                        <option value="">{{ $t('All Payments') }}</option>
                        <option value="cash">{{ $t('Cash') }}</option>
                        <option value="bank">{{ $t('Bank') }}</option>
                        <option value="cheque">{{ $t('Cheque') }}</option>
                        <option value="online">{{ $t('Online') }}</option>
                    </select>
                    <div class="flex gap-2">
                        <button type="submit" class="theme-btn-primary px-6 py-2.5">{{ $t('Filter') }}</button>
                        <button v-if="hasFilters()" type="button" @click="clearSearch" class="theme-form-back-link px-4 py-2.5">{{ $t('Clear') }}</button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[1200px]">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell">{{ $t('Expense #') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Date') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Expense Name') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Warehouse') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Employee') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Payee') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Amount') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Payment') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Reference') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Invoice') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Status') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="e in expenses.data" :key="e.id" class="theme-table-row group">
                            <td class="px-6 py-3">
                                <div class="text-sm font-bold text-indigo-600">{{ e.expense_no }}</div>
                                <div v-if="e.description" class="text-[11px] text-slate-400 mt-0.5 max-w-[160px] truncate" :title="e.description">{{ e.description }}</div>
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-slate-700 dark:text-slate-300 whitespace-nowrap">
                                {{ formatDate(e.expense_date) }}
                            </td>
                            <td class="px-6 py-3">
                                <div class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ e.expense_name || '—' }}</div>
                            </td>
                            <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-300">{{ e.warehouse?.name || '—' }}</td>
                            <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-300">{{ e.employee_name || '—' }}</td>
                            <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-300">{{ e.payee_name || '—' }}</td>
                            <td class="px-6 py-3 text-right font-black text-slate-800 dark:text-slate-100 whitespace-nowrap">${{ money(e.amount) }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                    {{ e.payment_method }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-300">{{ e.reference_no || '—' }}</td>
                            <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-300">{{ e.invoice_no || '—' }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold capitalize" :class="statusClass(e.status)">
                                    {{ e.status }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right whitespace-nowrap">
                                <div class="theme-table-actions">
                                    <Link :href="route('expenses.show', e.id)" class="theme-table-action-btn" :title="$t('View')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </Link>
                                    <Link :href="route('expenses.edit', e.id)" class="theme-table-action-btn theme-table-action-edit" :title="$t('Edit')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </Link>
                                    <button @click="openDeleteModal(e)" class="theme-table-action-btn theme-table-action-delete" :title="$t('Delete')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="expenses.data.length === 0">
                            <td colspan="13" class="px-6 py-12 text-center text-slate-400 font-medium">{{ $t('No expenses found.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="theme-table-footer flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">Showing {{ expenses.from || 0 }} to {{ expenses.to || 0 }} of {{ expenses.total }} entries</div>
                <div class="flex gap-1.5">
                    <template v-for="(link, k) in expenses.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border transition-all" :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']" />
                    </template>
                </div>
            </div>
        </div>

        <ConfirmModal
            :show="isModalOpen"
            title="Delete Expense"
            message="Are you sure you want to delete this expense record?"
            confirm-label="Yes, Delete"
            cancel-label="No, Keep"
            :badge="selectedExpense?.expense_no"
            :badge-initial="selectedExpense?.expense_no?.slice(0, 1)"
            @close="closeModal"
            @confirm="confirmDelete"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

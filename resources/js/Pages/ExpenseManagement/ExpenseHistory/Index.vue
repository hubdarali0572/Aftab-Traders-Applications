<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    summary: Object,
    expenses: Object,
    filters: Object,
    options: Object,
    printMode: Boolean,
});

const dateFrom = ref(props.filters?.date_from ?? '');
const dateTo = ref(props.filters?.date_to ?? '');
const warehouseId = ref(props.filters?.warehouse_id ?? '');
const statusFilter = ref(props.filters?.status ?? '');
const paymentMethod = ref(props.filters?.payment_method ?? '');
const searchQuery = ref(props.filters?.search ?? '');
const tableSearch = ref('');

const money = (v) => `$${Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const filterParams = (extra = {}) => ({
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
    warehouse_id: warehouseId.value || undefined,
    status: statusFilter.value || undefined,
    payment_method: paymentMethod.value || undefined,
    search: searchQuery.value || undefined,
    sort: props.filters?.sort,
    direction: props.filters?.direction,
    ...extra,
});

const applyFilters = () => {
    router.get(route('expense-history.index'), filterParams(), { preserveState: true, replace: true });
};

const clearFilters = () => {
    dateFrom.value = '';
    dateTo.value = '';
    warehouseId.value = '';
    statusFilter.value = '';
    paymentMethod.value = '';
    searchQuery.value = '';
    applyFilters();
};

const hasFilters = computed(() =>
    Boolean(dateFrom.value || dateTo.value || warehouseId.value || statusFilter.value || paymentMethod.value || searchQuery.value)
);

let filterTimer = null;
watch([dateFrom, dateTo, warehouseId, statusFilter, paymentMethod], () => {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(applyFilters, 350);
});

const statusClass = (status) => ({
    draft: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
    approved: 'bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400',
    paid: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
    cancelled: 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400',
}[status] || 'bg-slate-100 text-slate-500');

const paymentClass = (method) => ({
    cash: 'bg-indigo-50 text-indigo-700 ring-indigo-200',
    bank: 'bg-indigo-50 text-indigo-700 ring-indigo-200',
    cheque: 'bg-amber-50 text-amber-700 ring-amber-200',
    online: 'bg-violet-50 text-violet-700 ring-violet-200',
}[method] || 'bg-slate-50 text-slate-600 ring-slate-200');

const overviewStats = computed(() => [
    { title: 'Total Records', value: props.summary?.total_expenses ?? 0, tone: 'text-indigo-700 dark:text-indigo-300', bg: 'bg-indigo-50 dark:bg-indigo-500/10', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { title: 'Financial Expenses', value: props.summary?.financial_count ?? 0, tone: 'text-sky-700 dark:text-sky-300', bg: 'bg-sky-50 dark:bg-sky-500/10', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
    { title: 'Total Expense Amount', value: money(props.summary?.total_expense_amount), tone: 'text-indigo-700 dark:text-indigo-300', bg: 'bg-indigo-50 dark:bg-indigo-500/10', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', highlight: true },
    { title: 'Expenses Today', value: money(props.summary?.expenses_today), tone: 'text-violet-700 dark:text-violet-300', bg: 'bg-violet-50 dark:bg-violet-500/10', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
    { title: 'This Month', value: money(props.summary?.expenses_this_month), tone: 'text-sky-700 dark:text-sky-300', bg: 'bg-sky-50 dark:bg-sky-500/10', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
    { title: 'Paid Amount', value: money(props.summary?.paid_amount), tone: 'text-emerald-800 dark:text-emerald-200', bg: 'bg-emerald-100 dark:bg-emerald-500/15', icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' },
    { title: 'Draft Amount', value: money(props.summary?.draft_amount), tone: 'text-amber-700 dark:text-amber-300', bg: 'bg-amber-50 dark:bg-amber-500/10', icon: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' },
    { title: 'Cancelled Records', value: props.summary?.cancelled_count ?? 0, tone: 'text-rose-700 dark:text-rose-300', bg: 'bg-rose-50 dark:bg-rose-500/10', icon: 'M6 18L18 6M6 6l12 12' },
]);

const formatDate = (value) => {
    if (!value) return '—';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return value;
    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const filteredRows = computed(() => {
    const q = tableSearch.value.trim().toLowerCase();
    const rows = props.expenses?.data ?? [];
    if (!q) return rows;
    return rows.filter((row) =>
        [row.expense_no, row.expense_name, row.warehouse_name, row.payee_name, row.employee_name, row.recorded_by, row.reference_no]
            .some((v) => String(v || '').toLowerCase().includes(q))
    );
});

const sortExpenses = (column) => {
    const current = props.filters?.sort;
    const dir = props.filters?.direction === 'asc' ? 'desc' : 'asc';
    router.get(route('expense-history.index'), filterParams({
        sort: column,
        direction: current === column ? dir : 'desc',
    }), { preserveState: true, replace: true });
};

const exportUrl = () => {
    const params = new URLSearchParams(Object.entries(filterParams({ export: 'expenses' })).filter(([, v]) => v != null && v !== ''));
    return `${route('expense-history.index')}?${params.toString()}`;
};

const printPage = () => window.print();

onMounted(() => {
    if (props.printMode) setTimeout(() => window.print(), 400);
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Expense History')" />

        <div class="mb-6 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 print:hidden">
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-indigo-500 mb-1">{{ $t('Expense Management') }}</p>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight dark:text-slate-100">{{ $t('Expense History') }}</h2>
                <p class="text-sm text-slate-500 mt-1 dark:text-slate-400">{{ $t('Complete audit trail of every expense — category, warehouse, payee, and recorded by.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                <Link :href="route('expenses.create')" class="theme-btn-primary px-4 py-2 text-sm">{{ $t('Add Expense') }}</Link>
                <a :href="exportUrl()" class="theme-form-back-link px-4 py-2 text-sm font-bold">{{ $t('Export CSV') }}</a>
                <button type="button" @click="printPage" class="theme-form-back-link px-4 py-2 text-sm font-bold">{{ $t('Print') }}</button>
            </div>
        </div>

        <!-- Dashboard -->
        <div class="theme-table-card mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-indigo-50 to-white dark:from-indigo-950/30 dark:to-slate-800">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">{{ $t('Expense History Dashboard') }}</h3>
                <p class="text-xs text-slate-400 mt-0.5">{{ hasFilters ? $t('Metrics update instantly with your filters.') : $t('Financial totals include approved and paid expenses only.') }}</p>
            </div>
            <div class="p-6 grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
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

        <!-- Payment breakdown -->
        <div class="theme-table-card mb-6 overflow-hidden print:hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ $t('By Payment Method') }}</h3>
            </div>
            <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div v-if="!(summary?.by_payment_method?.length)" class="col-span-full text-sm text-slate-400 text-center py-4">{{ $t('No data for current filters.') }}</div>
                <div
                    v-for="pm in summary?.by_payment_method ?? []"
                    :key="pm.payment_method"
                    class="p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/30"
                >
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase ring-1 ring-inset capitalize mb-2" :class="paymentClass(pm.payment_method)">{{ pm.payment_method }}</span>
                    <p class="text-lg font-black text-slate-800 dark:text-slate-100">{{ money(pm.amount) }}</p>
                    <p class="text-[10px] text-slate-400 mt-1">{{ pm.count }} {{ $t('transactions') }}</p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="theme-table-card mb-6 sticky top-0 z-20 shadow-md print:hidden">
            <div class="px-6 py-3 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between gap-3">
                <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ $t('Essential Filters') }}</h3>
                <button v-if="hasFilters" type="button" @click="clearFilters" class="text-xs font-bold text-rose-600 hover:text-rose-700">{{ $t('Clear All') }}</button>
            </div>
            <form @submit.prevent="applyFilters" class="p-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
                <div class="xl:col-span-2">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ $t('Date Range') }}</label>
                    <div class="flex gap-2">
                        <input v-model="dateFrom" type="date" class="theme-form-input flex-1" />
                        <input v-model="dateTo" type="date" class="theme-form-input flex-1" />
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ $t('Warehouse') }}</label>
                    <select v-model="warehouseId" class="theme-form-input w-full">
                        <option value="">{{ $t('All Warehouses') }}</option>
                        <option v-for="w in options?.warehouses ?? []" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ $t('Status') }}</label>
                    <select v-model="statusFilter" class="theme-form-input w-full">
                        <option value="">{{ $t('All Status') }}</option>
                        <option value="draft">{{ $t('Draft') }}</option>
                        <option value="approved">{{ $t('Approved') }}</option>
                        <option value="paid">{{ $t('Paid') }}</option>
                        <option value="cancelled">{{ $t('Cancelled') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ $t('Payment') }}</label>
                    <select v-model="paymentMethod" class="theme-form-input w-full">
                        <option value="">{{ $t('All Payments') }}</option>
                        <option value="cash">{{ $t('Cash') }}</option>
                        <option value="bank">{{ $t('Bank') }}</option>
                        <option value="cheque">{{ $t('Cheque') }}</option>
                        <option value="online">{{ $t('Online') }}</option>
                    </select>
                </div>
                <div class="xl:col-span-5">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ $t('Search') }}</label>
                    <input v-model="searchQuery" type="text" class="theme-form-input w-full" placeholder="Expense #, payee, employee, reference..." @keyup.enter="applyFilters" />
                </div>
            </form>
        </div>

        <!-- History table -->
        <div class="theme-table-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 print:hidden">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">{{ $t('Expense Records') }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ expenses.total ?? 0 }} {{ $t('records found') }}</p>
                </div>
                <input v-model="tableSearch" type="text" class="theme-form-input w-full sm:w-72" :placeholder="$t('Quick filter table...')" />
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[960px]">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell cursor-pointer print:cursor-default" @click="sortExpenses('expense_date')">{{ $t('Date') }}</th>
                            <th class="theme-table-header-cell cursor-pointer print:cursor-default" @click="sortExpenses('expense_no')">{{ $t('Expense #') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Expense Name') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Warehouse') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Payee') }}</th>
                            <th class="theme-table-header-cell text-right cursor-pointer print:cursor-default" @click="sortExpenses('amount')">{{ $t('Amount') }}</th>
                            <th class="theme-table-header-cell cursor-pointer print:cursor-default" @click="sortExpenses('payment_method')">{{ $t('Payment') }}</th>
                            <th class="theme-table-header-cell cursor-pointer print:cursor-default" @click="sortExpenses('status')">{{ $t('Status') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Recorded By') }}</th>
                            <th class="theme-table-header-cell text-right print:hidden">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="e in filteredRows" :key="e.id" class="theme-table-row group">
                            <td class="px-6 py-3 text-sm font-medium text-slate-700 dark:text-slate-300 whitespace-nowrap">{{ formatDate(e.expense_date) }}</td>
                            <td class="px-6 py-3">
                                <div class="text-sm font-bold text-indigo-600">{{ e.expense_no }}</div>
                                <div v-if="e.reference_no" class="text-[10px] text-slate-400">Ref: {{ e.reference_no }}</div>
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-slate-700 dark:text-slate-300">{{ e.expense_name || '—' }}</td>
                            <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-300">{{ e.warehouse_name || $t('Company-wide') }}</td>
                            <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-300">{{ e.payee_name || e.employee_name || '—' }}</td>
                            <td class="px-6 py-3 text-right font-black text-slate-800 dark:text-slate-100 whitespace-nowrap">{{ money(e.amount) }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase ring-1 ring-inset capitalize" :class="paymentClass(e.payment_method)">{{ e.payment_method }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold capitalize" :class="statusClass(e.status)">{{ e.status }}</span>
                            </td>
                            <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-300">{{ e.recorded_by || '—' }}</td>
                            <td class="px-6 py-3 text-right whitespace-nowrap print:hidden">
                                <Link :href="route('expenses.show', e.id)" class="theme-table-action-btn" :title="$t('View')">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="filteredRows.length === 0">
                            <td colspan="10" class="px-6 py-12 text-center text-slate-400 font-medium">{{ $t('No expense records found.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="theme-table-footer flex flex-col sm:flex-row sm:items-center sm:justify-between print:hidden">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">Showing {{ expenses.from || 0 }} to {{ expenses.to || 0 }} of {{ expenses.total }} entries</div>
                <div class="flex gap-1.5">
                    <template v-for="(link, k) in expenses.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border transition-all" :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

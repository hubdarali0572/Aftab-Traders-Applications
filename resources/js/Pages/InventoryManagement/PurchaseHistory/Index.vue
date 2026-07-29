<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    purchases: Object,
    recentTransactions: Array,
    summary: Object,
    filters: Object,
});

const searchQuery = ref(props.filters?.search ?? '');
const paymentStatus = ref(props.filters?.payment_status ?? '');
const purchaseStatus = ref(props.filters?.purchase_status ?? '');
const dateFrom = ref(props.filters?.date_from ?? '');
const dateTo = ref(props.filters?.date_to ?? '');

const filterParams = () => ({
    search: searchQuery.value || undefined,
    payment_status: paymentStatus.value || undefined,
    purchase_status: purchaseStatus.value || undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
});

const applyFilters = () => {
    router.get(route('purchase-history.index'), filterParams(), { preserveState: true, replace: true });
};

const clearFilters = () => {
    searchQuery.value = '';
    paymentStatus.value = '';
    purchaseStatus.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};

const hasFilters = () => Object.values(props.filters ?? {}).some((v) => v);
const money = (v) => `$${Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const paymentClass = (status) => ({
    unpaid: 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
    partial: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
    paid: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
}[status] || '');

const purchaseStatusClass = (status) => ({
    draft: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
    received: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
    cancelled: 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
}[status] || '');

const txnBadgeClass = (type) => ({
    purchase: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300',
    payment: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
    purchase_return: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
    adjustment: 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
    cancellation: 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
}[type] || 'bg-slate-100 text-slate-700');

const formatTxn = (type) => type?.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) ?? '—';

const overviewStats = computed(() => [
    { title: 'Total Purchases', value: money(props.summary?.total_purchases), tone: 'text-indigo-700 dark:text-indigo-300', bg: 'bg-indigo-50 dark:bg-indigo-500/10', icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z' },
    { title: 'Total Paid', value: money(props.summary?.total_paid), tone: 'text-emerald-700 dark:text-emerald-300', bg: 'bg-emerald-50 dark:bg-emerald-500/10', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { title: 'Total Returns', value: money(props.summary?.total_returns), tone: 'text-amber-700 dark:text-amber-300', bg: 'bg-amber-50 dark:bg-amber-500/10', icon: 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6' },
    { title: 'Total Remaining', value: money(props.summary?.total_remaining), tone: 'text-rose-700 dark:text-rose-300', bg: 'bg-rose-50 dark:bg-rose-500/10', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
]);
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Purchase History')" />

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">{{ $t('Purchase History') }}</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">{{ $t('Complete purchase and return financial history with payments and balances.') }}</p>
            </div>
            <Link :href="route('purchases.index')" class="theme-btn-primary shrink-0 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                {{ $t('Purchases') }}
            </Link>
        </div>

        <!-- Financial overview -->
        <div class="theme-table-card mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-indigo-50 to-white dark:from-indigo-950/30 dark:to-slate-800">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">{{ $t('Financial Overview') }}</h3>
                            <p class="text-xs text-slate-400 mt-0.5">{{ hasFilters() ? $t('Totals for filtered purchase records.') : $t('All active purchase financial summary.') }}</p>
                        </div>
                    </div>
                    <span v-if="summary?.total_count != null" class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-3 py-1 rounded-full">
                        {{ summary.total_count }} {{ $t('records') }}
                    </span>
                </div>
            </div>
            <div class="p-6 grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="stat in overviewStats" :key="stat.title" class="flex items-start gap-3 p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800/50 shadow-sm">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" :class="stat.bg">
                        <svg class="w-5 h-5" :class="stat.tone" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="stat.icon" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400">{{ $t(stat.title) }}</p>
                        <p class="text-lg font-black mt-0.5 truncate" :class="stat.tone">{{ stat.value }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="theme-table-card mb-6">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Filters') }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $t('Search and narrow purchase history records.') }}</p>
                    </div>
                </div>
            </div>
            <form @submit.prevent="applyFilters" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    <div class="xl:col-span-1">
                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ $t('Search') }}</label>
                        <input v-model="searchQuery" type="text" class="theme-form-input w-full" :placeholder="$t('PO #, supplier, invoice...')" />
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ $t('Date Range') }}</label>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <input v-model="dateFrom" type="date" class="theme-form-input flex-1" />
                            <span class="hidden sm:flex items-center text-slate-300 dark:text-slate-600 font-bold px-1">—</span>
                            <input v-model="dateTo" type="date" class="theme-form-input flex-1" />
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 xl:col-span-1">
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ $t('Purchase Status') }}</label>
                            <select v-model="purchaseStatus" class="theme-form-input w-full">
                                <option value="">{{ $t('All Purchase Status') }}</option>
                                <option value="draft">{{ $t('Draft') }}</option>
                                <option value="received">{{ $t('Received') }}</option>
                                <option value="cancelled">{{ $t('Cancelled') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ $t('Payment Status') }}</label>
                            <select v-model="paymentStatus" class="theme-form-input w-full">
                                <option value="">{{ $t('All Payment Status') }}</option>
                                <option value="unpaid">{{ $t('Unpaid') }}</option>
                                <option value="partial">{{ $t('Partial') }}</option>
                                <option value="paid">{{ $t('Paid') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-5 pt-5 border-t border-slate-100 dark:border-slate-700">
                    <button type="submit" class="theme-btn-primary px-6 py-2.5 inline-flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                        {{ $t('Apply Filters') }}
                    </button>
                    <button v-if="hasFilters()" type="button" @click="clearFilters" class="theme-form-back-link px-4 py-2.5">{{ $t('Clear') }}</button>
                </div>
            </form>
        </div>

        <!-- Purchase records -->
        <div class="theme-table-card mb-8">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Purchase Records') }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $t('All purchase orders with payment and return totals.') }}</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell">{{ $t('PO #') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Date') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Supplier') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Status') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Grand Total') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Returns') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Net Payable') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Paid') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Remaining') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Payment') }}</th>
                            <th class="theme-table-header-cell text-right whitespace-nowrap">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="p in purchases.data" :key="p.id" class="theme-table-row">
                            <td class="px-4 py-2.5 font-bold text-indigo-600 dark:text-indigo-400">{{ p.purchase_no }}</td>
                            <td class="px-4 py-2.5 whitespace-nowrap text-slate-600 dark:text-slate-300">{{ p.purchase_date }}</td>
                            <td class="px-4 py-2.5">{{ p.supplier_name || '—' }}</td>
                            <td class="px-4 py-2.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase" :class="purchaseStatusClass(p.purchase_status)">{{ p.purchase_status }}</span>
                            </td>
                            <td class="px-4 py-2.5 text-right font-bold">{{ money(p.grand_total) }}</td>
                            <td class="px-4 py-2.5 text-right text-amber-600 dark:text-amber-400">{{ money(p.returns_total) }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold">{{ money(p.net_payable) }}</td>
                            <td class="px-4 py-2.5 text-right text-emerald-600 dark:text-emerald-400">{{ money(p.paid_amount) }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-rose-600 dark:text-rose-400">{{ money(p.remaining_amount) }}</td>
                            <td class="px-4 py-2.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase" :class="paymentClass(p.payment_status)">{{ p.payment_status }}</span>
                            </td>
                            <td class="px-4 py-2.5 text-right">
                                <Link :href="route('purchase-history.show', p.id)" class="theme-table-action-btn" :title="$t('View')">
                                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!purchases.data?.length">
                            <td colspan="11" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <svg class="w-10 h-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    <span class="font-medium">{{ $t('No purchase history found.') }}</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="theme-table-footer flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 gap-4">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest dark:text-slate-300">
                    {{ $t('Showing') }} {{ purchases.from || 0 }} {{ $t('to') }} {{ purchases.to || 0 }} {{ $t('of') }} {{ purchases.total }} {{ $t('entries') }}
                </div>
                <div class="flex flex-wrap gap-1.5">
                    <template v-for="(link, k) in purchases.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border" :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']" />
                    </template>
                </div>
            </div>
        </div>

        <!-- Recent transactions -->
        <div class="theme-table-card">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Recent Transactions') }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $t('Latest financial activity matching your filters.') }}</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell">{{ $t('Date') }}</th>
                            <th class="theme-table-header-cell">{{ $t('PO #') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Type') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Reference') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Debit') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Credit') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Paid') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Remaining') }}</th>
                            <th class="theme-table-header-cell text-right whitespace-nowrap">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="t in recentTransactions" :key="t.id" class="theme-table-row">
                            <td class="px-4 py-2.5 whitespace-nowrap text-slate-600 dark:text-slate-300">{{ t.transaction_date }}</td>
                            <td class="px-4 py-2.5 font-bold text-indigo-600 dark:text-indigo-400">{{ t.purchase?.purchase_no }}</td>
                            <td class="px-4 py-2.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase" :class="txnBadgeClass(t.transaction_type)">{{ formatTxn(t.transaction_type) }}</span>
                            </td>
                            <td class="px-4 py-2.5">{{ t.reference_no || '—' }}</td>
                            <td class="px-4 py-2.5 text-right text-rose-600 dark:text-rose-400">{{ t.debit > 0 ? money(t.debit) : '—' }}</td>
                            <td class="px-4 py-2.5 text-right text-emerald-600 dark:text-emerald-400">{{ t.credit > 0 ? money(t.credit) : '—' }}</td>
                            <td class="px-4 py-2.5 text-right text-emerald-600">{{ money(t.paid_total) }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-rose-600">{{ money(t.due_total) }}</td>
                            <td class="px-4 py-2.5 text-right">
                                <Link :href="route('purchase-history.show', t.purchase_id)" class="theme-table-action-btn" :title="$t('View')">
                                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!recentTransactions?.length">
                            <td colspan="9" class="px-6 py-12 text-center text-slate-400">{{ $t('No transactions recorded yet.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

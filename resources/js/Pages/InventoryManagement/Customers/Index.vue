<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    customers: Object,
    summary: Object,
    recentTransactions: { type: Array, default: () => [] },
    filters: Object,
    customerTypes: Array,
    pageTitle: { type: String, default: 'Customers' },
    pageSubtitle: { type: String, default: 'Manage customer accounts, balances, and transaction history.' },
    indexRoute: { type: String, default: 'customers.index' },
    lockedType: { type: String, default: null },
});

const searchQuery = ref(props.filters?.search ?? '');
const customerType = ref(props.lockedType || props.filters?.customer_type || '');
const statusFilter = ref(props.filters?.status ?? '');
const dateFrom = ref(props.filters?.date_from ?? '');
const dateTo = ref(props.filters?.date_to ?? '');
const isModalOpen = ref(false);
const selectedId = ref(null);
const selectedDeleteBlockedReason = ref(null);

const page = usePage();
const showFlash = ref(false);
let flashTimer = null;

const startFlashTimer = () => {
    showFlash.value = true;
    if (flashTimer) clearTimeout(flashTimer);
    flashTimer = setTimeout(() => {
        showFlash.value = false;
    }, 5000);
};

watch(
    () => [page.props.flash.success, page.props.flash.danger, page.props.flash.error],
    ([success, danger, error]) => {
        if (success || danger || error) {
            startFlashTimer();
        }
    },
    { immediate: true }
);

const flashMessage = computed(() => page.props.flash.success || page.props.flash.danger || page.props.flash.error);
const flashIsSuccess = computed(() => Boolean(page.props.flash.success));

const money = (v) => `$${Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
const formatType = (type) => type?.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) ?? '—';
const formatTxn = (type) => type?.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) ?? '—';

const filterParams = () => ({
    search: searchQuery.value || undefined,
    customer_type: props.lockedType || customerType.value || undefined,
    status: statusFilter.value !== '' ? statusFilter.value : undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
});

const applyFilters = () => {
    router.get(route(props.indexRoute), filterParams(), { preserveState: true, replace: true });
};

const clearFilters = () => {
    searchQuery.value = '';
    if (!props.lockedType) customerType.value = '';
    statusFilter.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};

const hasFilters = () => Object.values(props.filters ?? {}).some((v) => v !== '' && v != null);

const openDeleteModal = (customer) => {
    if (!customer.can_delete) {
        return;
    }
    selectedId.value = customer.id;
    selectedDeleteBlockedReason.value = null;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => {
        selectedId.value = null;
        selectedDeleteBlockedReason.value = null;
    }, 300);
};

const confirmDelete = () => {
    if (selectedId.value) {
        router.delete(route('customers.destroy', selectedId.value), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
            onFinish: () => closeModal(),
        });
    }
};

const createHref = () => {
    if (props.lockedType) {
        return route('customers.create', { customer_type: props.lockedType });
    }
    return route('customers.create');
};

const overviewStats = computed(() => [
    { title: 'Total Customers', value: props.summary?.total_customers ?? 0, tone: 'text-indigo-700 dark:text-indigo-300', bg: 'bg-indigo-50 dark:bg-indigo-500/10', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' },
    { title: 'Active Customers', value: props.summary?.active_customers ?? 0, tone: 'text-emerald-700 dark:text-emerald-300', bg: 'bg-emerald-50 dark:bg-emerald-500/10', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { title: 'Retail Customers', value: props.summary?.retail_customers ?? 0, tone: 'text-sky-700 dark:text-sky-300', bg: 'bg-sky-50 dark:bg-sky-500/10', icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z' },
    { title: 'Wholesale Customers', value: props.summary?.wholesale_customers ?? 0, tone: 'text-violet-700 dark:text-violet-300', bg: 'bg-violet-50 dark:bg-violet-500/10', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
    { title: 'Total Outstanding', value: money(props.summary?.total_outstanding), tone: 'text-rose-700 dark:text-rose-300', bg: 'bg-rose-50 dark:bg-rose-500/10', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { title: 'Total Sales', value: money(props.summary?.total_sales), tone: 'text-amber-700 dark:text-amber-300', bg: 'bg-amber-50 dark:bg-amber-500/10', icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6' },
    { title: 'Total Payments Received', value: money(props.summary?.total_payments_received), tone: 'text-teal-700 dark:text-teal-300', bg: 'bg-teal-50 dark:bg-teal-500/10', icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' },
]);
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="pageTitle" />

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">{{ $t(pageTitle) }}</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">{{ $t(pageSubtitle) }}</p>
            </div>
            <Link :href="createHref()" class="theme-btn-primary shrink-0 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5" /></svg>
                {{ $t('New Customer') }}
            </Link>
        </div>

        <div
            v-if="showFlash && flashMessage"
            :class="[flashIsSuccess ? 'bg-indigo-50 border-indigo-500 text-indigo-800 dark:bg-indigo-500/10 dark:text-indigo-200' : 'bg-rose-50 border-rose-500 text-rose-800 dark:bg-rose-500/10 dark:text-rose-200']"
            class="mb-6 flex items-center p-4 border-l-4 rounded-r-xl shadow-sm"
        >
            <p class="ml-3 text-sm font-bold">{{ flashMessage }}</p>
        </div>

        <!-- Dashboard overview -->
        <div class="theme-table-card mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-indigo-50 to-white dark:from-indigo-950/30 dark:to-slate-800">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">{{ $t('Customer Dashboard') }}</h3>
                            <p class="text-xs text-slate-400 mt-0.5">{{ hasFilters() ? $t('Summary for filtered customers.') : $t('Overview of all customer accounts.') }}</p>
                        </div>
                    </div>
                    <span v-if="summary?.total_customers != null" class="text-xs font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-500/10 px-3 py-1 rounded-full">
                        {{ summary.total_customers }} {{ $t('customers') }}
                    </span>
                </div>
            </div>
            <div class="p-6 grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-4">
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
                <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $t('Search & Filters') }}</h3>
            </div>
            <form @submit.prevent="applyFilters" class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <input v-model="searchQuery" type="text" class="theme-form-input lg:col-span-2" :placeholder="$t('Name, code, or mobile...')" />
                <select v-if="!lockedType" v-model="customerType" class="theme-form-input">
                    <option value="">{{ $t('All Types') }}</option>
                    <option v-for="t in customerTypes" :key="t" :value="t">{{ formatType(t) }}</option>
                </select>
                <select v-model="statusFilter" class="theme-form-input">
                    <option value="">{{ $t('All Status') }}</option>
                    <option value="1">{{ $t('Active') }}</option>
                    <option value="0">{{ $t('Inactive') }}</option>
                </select>
                <input v-model="dateFrom" type="date" class="theme-form-input" :title="$t('From Date')" />
                <input v-model="dateTo" type="date" class="theme-form-input" :title="$t('To Date')" />
                <div class="flex gap-2 lg:col-span-6">
                    <button type="submit" class="theme-btn-primary px-6 py-2.5">{{ $t('Apply Filters') }}</button>
                    <button v-if="hasFilters()" type="button" @click="clearFilters" class="theme-form-back-link px-4 py-2.5">{{ $t('Clear') }}</button>
                </div>
            </form>
        </div>

        <!-- Recent ledger activity -->
        <div v-if="recentTransactions?.length" class="theme-table-card mb-6">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex justify-between items-center">
                <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $t('Recent Ledger Activity') }}</h3>
                <Link :href="route('customer-ledgers.index')" class="text-xs font-bold text-indigo-600 hover:underline">{{ $t('View All') }}</Link>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell">{{ $t('Date') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Customer') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Type') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Debit') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Credit') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Balance') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="txn in recentTransactions" :key="txn.id" class="theme-table-row">
                            <td class="px-6 py-3 text-sm">{{ txn.transaction_date }}</td>
                            <td class="px-6 py-3 text-sm font-bold">{{ txn.customer?.customer_name }}</td>
                            <td class="px-6 py-3"><span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest bg-indigo-100 text-indigo-700">{{ formatTxn(txn.transaction_type) }}</span></td>
                            <td class="px-6 py-3 text-sm text-right text-rose-600">{{ Number(txn.debit) > 0 ? money(txn.debit) : '—' }}</td>
                            <td class="px-6 py-3 text-sm text-right text-emerald-600">{{ Number(txn.credit) > 0 ? money(txn.credit) : '—' }}</td>
                            <td class="px-6 py-3 text-sm text-right font-bold">{{ money(txn.balance) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Customer list -->
        <div class="theme-table-card">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell">{{ $t('Code') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Name') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Type') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Mobile') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Opening') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Outstanding') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Status') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="c in customers.data" :key="c.id" class="theme-table-row group">
                            <td class="px-6 py-3 font-bold text-indigo-600 dark:text-indigo-400">{{ c.customer_code }}</td>
                            <td class="px-6 py-3">
                                <div class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ c.customer_name }}</div>
                                <div v-if="c.company_name" class="text-[10px] text-slate-400 font-medium">{{ c.company_name }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400">
                                    {{ formatType(c.customer_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400">{{ c.phone }}</td>
                            <td class="px-6 py-3 text-sm text-right text-slate-600 dark:text-slate-400">{{ money(c.opening_balance) }}</td>
                            <td class="px-6 py-3 text-sm text-right font-bold" :class="Number(c.outstanding) > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-slate-600 dark:text-slate-400'">
                                {{ money(c.outstanding) }}
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                    :class="c.status ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400'">
                                    {{ c.status ? $t('Active') : $t('Inactive') }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right whitespace-nowrap">
                                <div class="theme-table-actions">
                                    <Link :href="route('customers.customer-ledger', c.id)" class="theme-table-action-btn" :title="$t('Ledger')">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                    </Link>
                                    <Link :href="route('customers.show', c.id)" class="theme-table-action-btn" :title="$t('Profile')">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </Link>
                                    <Link :href="route('customers.edit', c.id)" class="theme-table-action-btn theme-table-action-edit" :title="$t('Edit')">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    </Link>
                                    <button
                                        @click="openDeleteModal(c)"
                                        class="theme-table-action-btn theme-table-action-delete"
                                        :class="{ 'opacity-40 cursor-not-allowed': !c.can_delete }"
                                        :title="c.can_delete ? $t('Delete') : (c.delete_blocked_reason || $t('Cannot delete this customer'))"
                                        :disabled="!c.can_delete"
                                    >
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="customers.data.length === 0">
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400 font-medium dark:text-slate-500">{{ $t('No customers found.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="theme-table-footer flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest text-center sm:text-left">
                    {{ $t('Showing') }} {{ customers.from || 0 }} {{ $t('to') }} {{ customers.to || 0 }} {{ $t('of') }} {{ customers.total }} {{ $t('entries') }}
                </div>
                <div class="flex flex-wrap justify-center items-center gap-1.5 mt-4 sm:mt-0">
                    <template v-for="(link, k) in customers.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label"
                            class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border transition-all duration-200"
                            :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']" />
                        <span v-else v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold text-slate-300 bg-white border border-slate-100 rounded-lg cursor-not-allowed dark:text-slate-600 dark:bg-slate-800" />
                    </template>
                </div>
            </div>
        </div>

        <ConfirmModal
            :show="isModalOpen"
            :title="$t('Delete Customer')"
            :message="$t('Are you sure you want to permanently remove this customer? This action cannot be undone.')"
            @close="closeModal"
            @confirm="confirmDelete"
        />
    </AuthenticatedLayout>
</template>

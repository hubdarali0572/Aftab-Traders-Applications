<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    transferHistory: Object,
    damagedHistory: Object,
    summary: Object,
    warehouses: Array,
    products: Array,
    filters: Object,
});

const dateFrom = ref(props.filters?.date_from ?? '');
const dateTo = ref(props.filters?.date_to ?? '');
const productId = ref(props.filters?.product_id ?? '');
const warehouseId = ref(props.filters?.warehouse_id ?? '');

const filterParams = () => ({
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
    product_id: productId.value || undefined,
    warehouse_id: warehouseId.value || undefined,
});

const applyFilters = () => {
    router.get(route('stock-history.index'), filterParams(), { preserveState: true, replace: true });
};

const clearFilters = () => {
    dateFrom.value = '';
    dateTo.value = '';
    productId.value = '';
    warehouseId.value = '';
    applyFilters();
};

const hasFilters = () => Object.values(props.filters ?? {}).some((v) => v);
const money = (v) => (v == null ? '—' : `$${parseFloat(v).toFixed(2)}`);
const num = (v) => Number(v || 0).toLocaleString(undefined, { maximumFractionDigits: 2 });

const warehouseAccents = [
    { ring: 'ring-sky-200 dark:ring-sky-500/30', bg: 'bg-sky-50 dark:bg-sky-500/10', icon: 'text-sky-600 dark:text-sky-400', value: 'text-sky-700 dark:text-sky-300' },
    { ring: 'ring-indigo-200 dark:ring-indigo-500/30', bg: 'bg-indigo-50 dark:bg-indigo-500/10', icon: 'text-indigo-600 dark:text-indigo-400', value: 'text-indigo-700 dark:text-indigo-300' },
    { ring: 'ring-emerald-200 dark:ring-emerald-500/30', bg: 'bg-emerald-50 dark:bg-emerald-500/10', icon: 'text-emerald-600 dark:text-emerald-400', value: 'text-emerald-700 dark:text-emerald-300' },
    { ring: 'ring-violet-200 dark:ring-violet-500/30', bg: 'bg-violet-50 dark:bg-violet-500/10', icon: 'text-violet-600 dark:text-violet-400', value: 'text-violet-700 dark:text-violet-300' },
];

const warehouseCards = computed(() =>
    (props.summary?.warehouse_totals ?? []).map((wh, index) => ({
        ...wh,
        accent: warehouseAccents[index % warehouseAccents.length],
    }))
);

const overviewStats = computed(() => [
    {
        title: 'Total Damaged Qty',
        value: num(props.summary?.total_damaged_quantity ?? 0),
        iconPath: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        tone: 'text-amber-600 dark:text-amber-400',
        bg: 'bg-amber-50 dark:bg-amber-500/10',
    },
    {
        title: 'Total Damaged Loss',
        value: money(props.summary?.total_damaged_amount ?? 0),
        iconPath: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        tone: 'text-rose-600 dark:text-rose-400',
        bg: 'bg-rose-50 dark:bg-rose-500/10',
    },
    {
        title: 'Current Stock Qty',
        value: num(props.summary?.current_stock_quantity ?? 0),
        iconPath: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        tone: 'text-slate-600 dark:text-slate-300',
        bg: 'bg-slate-50 dark:bg-slate-700/40',
    },
    {
        title: 'Current Stock Value',
        value: money(props.summary?.current_stock_value ?? 0),
        iconPath: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
        tone: 'text-indigo-600 dark:text-indigo-400',
        bg: 'bg-indigo-50 dark:bg-indigo-500/10',
    },
]);
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Stock History')" />

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">{{ $t('Stock History') }}</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">{{ $t('Transfer and damaged stock audit trail with balances.') }}</p>
            </div>
            <Link :href="route('stocks.index')" class="theme-btn-primary shrink-0 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                {{ $t('Total Stock') }}
            </Link>
        </div>

        <!-- Overview -->
        <div class="theme-table-card mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800/80 dark:to-slate-800">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">{{ $t('Stock Overview') }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $t('Warehouse availability and inventory summary.') }}</p>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <div v-if="warehouseCards.length">
                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400 mb-3">{{ $t('Warehouse Availability') }}</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                        <div
                            v-for="wh in warehouseCards"
                            :key="wh.warehouse_id"
                            class="group relative flex items-center gap-4 p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800/50 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md ring-1 ring-inset"
                            :class="wh.accent.ring"
                        >
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0" :class="wh.accent.bg">
                                <svg class="w-6 h-6" :class="wh.accent.icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 truncate" :title="wh.warehouse_name">{{ wh.warehouse_name }}</p>
                                <p class="text-2xl font-black mt-0.5 leading-none" :class="wh.accent.value">{{ num(wh.quantity) }}</p>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mt-1">{{ $t('Available Qty') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-slate-100 dark:border-slate-700 pt-6">
                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400 mb-3">{{ $t('Inventory Summary') }}</p>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div
                            v-for="stat in overviewStats"
                            :key="stat.title"
                            class="group flex items-start gap-3 p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800/50 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md hover:border-indigo-200 dark:hover:border-indigo-500/30"
                        >
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center shrink-0" :class="stat.bg">
                                <svg class="w-5 h-5" :class="stat.tone" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="stat.iconPath" /></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-slate-400 dark:text-slate-500 leading-tight">{{ $t(stat.title) }}</p>
                                <p class="text-xl font-black mt-1 truncate text-slate-800 dark:text-slate-100" :class="stat.tone">{{ stat.value }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="theme-table-card mb-8">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-700 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                    </div>
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Filters') }}</h3>
                </div>
            </div>
            <form @submit.prevent="applyFilters" class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ $t('Date Range') }}</label>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <input v-model="dateFrom" type="date" class="theme-form-input flex-1" />
                            <span class="hidden sm:flex items-center text-slate-300 dark:text-slate-600 font-bold px-1">—</span>
                            <input v-model="dateTo" type="date" class="theme-form-input flex-1" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ $t('Product') }}</label>
                        <select v-model="productId" class="theme-form-input w-full">
                            <option value="">{{ $t('All Products') }}</option>
                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ $t('Warehouse') }}</label>
                        <select v-model="warehouseId" class="theme-form-input w-full">
                            <option value="">{{ $t('All Warehouses') }}</option>
                            <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                        </select>
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

        <!-- Transfer History -->
        <div class="theme-table-card mb-8">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Stock Transfer History') }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $t('Complete warehouse-to-warehouse transfer audit trail.') }}</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell whitespace-nowrap">{{ $t('Transfer Date & Time') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Reference') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Product') }}</th>
                            <th class="theme-table-header-cell">{{ $t('SKU') }}</th>
                            <th class="theme-table-header-cell">{{ $t('From Warehouse') }}</th>
                            <th class="theme-table-header-cell">{{ $t('To Warehouse') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Qty') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Unit Cost') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Total Value') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Bal. Before') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Bal. After') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Created By') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Remarks') }}</th>
                            <th class="theme-table-header-cell text-right whitespace-nowrap">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="row in transferHistory?.data ?? []" :key="row.id" class="theme-table-row">
                            <td class="px-4 py-2.5 whitespace-nowrap text-slate-600 dark:text-slate-300">{{ row.transfer_datetime }}</td>
                            <td class="px-4 py-2.5 font-bold text-indigo-600 dark:text-indigo-400">{{ row.reference_no }}</td>
                            <td class="px-4 py-2.5">{{ row.product_name }}</td>
                            <td class="px-4 py-2.5 text-slate-500">{{ row.sku || '—' }}</td>
                            <td class="px-4 py-2.5">{{ row.from_warehouse }}</td>
                            <td class="px-4 py-2.5">{{ row.to_warehouse }}</td>
                            <td class="px-4 py-2.5 text-right font-semibold">{{ row.quantity }}</td>
                            <td class="px-4 py-2.5 text-right">{{ money(row.unit_cost) }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-indigo-700 dark:text-indigo-300">{{ money(row.total_value) }}</td>
                            <td class="px-4 py-2.5 text-right text-slate-500">{{ row.from_balance_before ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-right font-bold">{{ row.from_balance_after ?? '—' }}</td>
                            <td class="px-4 py-2.5">{{ row.created_by || '—' }}</td>
                            <td class="px-4 py-2.5 max-w-[120px] truncate text-slate-500" :title="row.remarks">{{ row.remarks || '—' }}</td>
                            <td class="px-4 py-2.5 whitespace-nowrap text-right">
                                <Link :href="route('stock-history.transfers.show', row.id)" class="theme-table-action-btn" :title="$t('View')">
                                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!(transferHistory?.data?.length)">
                            <td colspan="14" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <svg class="w-10 h-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                                    <span class="font-medium">{{ $t('No transfer history found.') }}</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="transferHistory?.links" class="theme-table-footer flex flex-wrap gap-1.5 justify-center sm:justify-end p-4">
                <template v-for="(link, k) in transferHistory.links" :key="'t' + k">
                    <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border" :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']" />
                </template>
            </div>
        </div>

        <!-- Damaged History -->
        <div class="theme-table-card mb-8">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Damaged Stock History') }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $t('Complete damaged stock and loss audit trail.') }}</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell whitespace-nowrap">{{ $t('Damage Date & Time') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Reference') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Product') }}</th>
                            <th class="theme-table-header-cell">{{ $t('SKU') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Warehouse') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Damaged Qty') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Unit Cost') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Total Loss') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Damage Reason') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Bal. Before') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Bal. After') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Recorded By') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Remarks') }}</th>
                            <th class="theme-table-header-cell text-right whitespace-nowrap">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="row in damagedHistory?.data ?? []" :key="row.id" class="theme-table-row">
                            <td class="px-4 py-2.5 whitespace-nowrap text-slate-600 dark:text-slate-300">{{ row.damage_datetime }}</td>
                            <td class="px-4 py-2.5 font-bold text-rose-600 dark:text-rose-400">{{ row.reference_no }}</td>
                            <td class="px-4 py-2.5">{{ row.product_name }}</td>
                            <td class="px-4 py-2.5 text-slate-500">{{ row.sku || '—' }}</td>
                            <td class="px-4 py-2.5">{{ row.warehouse }}</td>
                            <td class="px-4 py-2.5 text-right text-rose-600 dark:text-rose-400 font-bold">{{ row.quantity }}</td>
                            <td class="px-4 py-2.5 text-right">{{ money(row.unit_cost) }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-rose-600 dark:text-rose-400">{{ money(row.total_loss) }}</td>
                            <td class="px-4 py-2.5">{{ row.damage_reason || '—' }}</td>
                            <td class="px-4 py-2.5 text-right text-slate-500">{{ row.balance_before ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-right font-bold">{{ row.balance_after ?? '—' }}</td>
                            <td class="px-4 py-2.5">{{ row.recorded_by || '—' }}</td>
                            <td class="px-4 py-2.5 max-w-[120px] truncate text-slate-500" :title="row.remarks">{{ row.remarks || '—' }}</td>
                            <td class="px-4 py-2.5 whitespace-nowrap text-right">
                                <Link :href="route('stock-history.damaged.show', row.id)" class="theme-table-action-btn" :title="$t('View')">
                                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!(damagedHistory?.data?.length)">
                            <td colspan="14" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-400">
                                    <svg class="w-10 h-10 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                    <span class="font-medium">{{ $t('No damaged stock history found.') }}</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="damagedHistory?.links" class="theme-table-footer flex flex-wrap gap-1.5 justify-center sm:justify-end p-4">
                <template v-for="(link, k) in damagedHistory.links" :key="'d' + k">
                    <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border" :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']" />
                </template>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

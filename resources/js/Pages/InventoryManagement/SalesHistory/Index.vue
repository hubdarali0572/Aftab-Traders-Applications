<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    summary: Object,
    sales: Object,
    returns: Object,
    filters: Object,
    options: Object,
    printMode: Boolean,
});

const dateFrom = ref(props.filters?.date_from ?? '');
const dateTo = ref(props.filters?.date_to ?? '');
const customerId = ref(props.filters?.customer_id ?? '');
const productId = ref(props.filters?.product_id ?? '');
const warehouseId = ref(props.filters?.warehouse_id ?? '');
const salesSearch = ref('');
const returnsSearch = ref('');

const money = (v) => `$${Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const filterParams = (extra = {}) => ({
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
    customer_id: customerId.value || undefined,
    product_id: productId.value || undefined,
    warehouse_id: warehouseId.value || undefined,
    sales_sort: props.filters?.sales_sort,
    sales_direction: props.filters?.sales_direction,
    returns_sort: props.filters?.returns_sort,
    returns_direction: props.filters?.returns_direction,
    ...extra,
});

const applyFilters = () => {
    router.get(route('sales-history.index'), filterParams(), { preserveState: true, replace: true });
};

const clearFilters = () => {
    dateFrom.value = '';
    dateTo.value = '';
    customerId.value = '';
    productId.value = '';
    warehouseId.value = '';
    applyFilters();
};

const hasFilters = computed(() =>
    Boolean(dateFrom.value || dateTo.value || customerId.value || productId.value || warehouseId.value)
);

let filterTimer = null;
watch([dateFrom, dateTo, customerId, productId, warehouseId], () => {
    clearTimeout(filterTimer);
    filterTimer = setTimeout(applyFilters, 350);
});

const paymentClass = (status) => ({
    unpaid: 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
    partial: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
    paid: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
}[status] || 'bg-slate-100 text-slate-600');

const saleStatusClass = (status) => ({
    draft: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
    completed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
    cancelled: 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
}[status] || '');

const overviewStats = computed(() => [
    { title: 'Total Sales', value: props.summary?.total_sales ?? 0, tone: 'text-indigo-700 dark:text-indigo-300', bg: 'bg-indigo-50 dark:bg-indigo-500/10', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { title: 'Total Sales Amount', value: money(props.summary?.total_sales_amount), tone: 'text-emerald-700 dark:text-emerald-300', bg: 'bg-emerald-50 dark:bg-emerald-500/10', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    { title: 'Total Sales Returns', value: props.summary?.total_sales_returns ?? 0, tone: 'text-violet-700 dark:text-violet-300', bg: 'bg-violet-50 dark:bg-violet-500/10', icon: 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6' },
    { title: 'Total Return Amount', value: money(props.summary?.total_return_amount), tone: 'text-amber-700 dark:text-amber-300', bg: 'bg-amber-50 dark:bg-amber-500/10', icon: 'M16 15v-1a4 4 0 00-4-4H8m0 0l3 3m-3-3l3-3m9 14V5a2 2 0 00-2-2H6a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z' },
    { title: 'Net Sales Amount', value: money(props.summary?.net_sales_amount), tone: 'text-indigo-800 dark:text-indigo-200', bg: 'bg-indigo-100 dark:bg-indigo-500/15', icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', highlight: true },
    { title: 'Total Products Sold', value: props.summary?.products_sold ?? 0, tone: 'text-teal-700 dark:text-teal-300', bg: 'bg-teal-50 dark:bg-teal-500/10', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' },
    { title: 'Total Products Returned', value: props.summary?.products_returned ?? 0, tone: 'text-rose-700 dark:text-rose-300', bg: 'bg-rose-50 dark:bg-rose-500/10', icon: 'M20 12H4' },
]);

const filterSalesRows = (rows) => {
    const q = salesSearch.value.trim().toLowerCase();
    if (!q) return rows;
    return rows.filter((row) =>
        [row.invoice_no, row.customer_name, row.warehouse_name, row.products_label, row.sold_by]
            .some((v) => String(v || '').toLowerCase().includes(q))
    );
};

const filterReturnRows = (rows) => {
    const q = returnsSearch.value.trim().toLowerCase();
    if (!q) return rows;
    return rows.filter((row) =>
        [row.reference_no, row.invoice_no, row.customer_name, row.warehouse_name, row.products_label, row.returned_by, row.return_reason]
            .some((v) => String(v || '').toLowerCase().includes(q))
    );
};

const filteredSales = computed(() => filterSalesRows(props.sales?.data ?? []));
const filteredReturns = computed(() => filterReturnRows(props.returns?.data ?? []));

const sortSales = (column) => {
    const current = props.filters?.sales_sort;
    const dir = props.filters?.sales_direction === 'asc' ? 'desc' : 'asc';
    router.get(route('sales-history.index'), filterParams({
        sales_sort: column,
        sales_direction: current === column ? dir : 'desc',
    }), { preserveState: true, replace: true });
};

const sortReturns = (column) => {
    const current = props.filters?.returns_sort;
    const dir = props.filters?.returns_direction === 'asc' ? 'desc' : 'asc';
    router.get(route('sales-history.index'), filterParams({
        returns_sort: column,
        returns_direction: current === column ? dir : 'desc',
    }), { preserveState: true, replace: true });
};

const exportUrl = (type) => {
    const params = new URLSearchParams(Object.entries(filterParams({ export: type })).filter(([, v]) => v != null && v !== ''));
    return `${route('sales-history.index')}?${params.toString()}`;
};

const printPage = () => window.print();

onMounted(() => {
    if (props.printMode) setTimeout(() => window.print(), 400);
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Sales History')" />

        <div class="mb-6 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 print:hidden">
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-indigo-500 mb-1">{{ $t('Sales Management') }}</p>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight dark:text-slate-100">{{ $t('Sales History') }}</h2>
                <p class="text-sm text-slate-500 mt-1 dark:text-slate-400">{{ $t('Complete visibility into every sale and return — customer, warehouse, products, and user.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                <a :href="exportUrl('sales')" class="theme-form-back-link px-4 py-2 text-sm font-bold">{{ $t('Export Sales') }}</a>
                <a :href="exportUrl('returns')" class="theme-form-back-link px-4 py-2 text-sm font-bold">{{ $t('Export Returns') }}</a>
                <button type="button" @click="printPage" class="theme-form-back-link px-4 py-2 text-sm font-bold">{{ $t('Print') }}</button>
            </div>
        </div>

        <!-- Dashboard -->
        <div class="theme-table-card mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-indigo-50 to-white dark:from-indigo-950/30 dark:to-slate-800">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">{{ $t('Sales History Dashboard') }}</h3>
                <p class="text-xs text-slate-400 mt-0.5">{{ hasFilters ? $t('Metrics update instantly with your filters.') : $t('Overview of all sales and return activity.') }}</p>
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

        <!-- Essential filters -->
        <div class="theme-table-card mb-6 sticky top-0 z-20 shadow-md print:hidden">
            <div class="px-6 py-3 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between gap-3">
                <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ $t('Essential Filters') }}</h3>
                <button v-if="hasFilters" type="button" @click="clearFilters" class="text-xs font-bold text-rose-600 hover:text-rose-700">{{ $t('Clear All') }}</button>
            </div>
            <div class="p-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
                <div class="xl:col-span-2">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ $t('Date Range') }}</label>
                    <div class="flex gap-2">
                        <input v-model="dateFrom" type="date" class="theme-form-input flex-1" />
                        <input v-model="dateTo" type="date" class="theme-form-input flex-1" />
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ $t('Customer') }}</label>
                    <select v-model="customerId" class="theme-form-input w-full">
                        <option value="">{{ $t('All Customers') }}</option>
                        <option v-for="c in options?.customers ?? []" :key="c.id" :value="c.id">{{ c.customer_name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ $t('Warehouse') }}</label>
                    <select v-model="warehouseId" class="theme-form-input w-full">
                        <option value="">{{ $t('All Warehouses') }}</option>
                        <option v-for="w in options?.warehouses ?? []" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ $t('Product') }}</label>
                    <select v-model="productId" class="theme-form-input w-full">
                        <option value="">{{ $t('All Products') }}</option>
                        <option v-for="p in options?.products ?? []" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Sales History -->
        <div class="theme-table-card mb-8">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Sales History') }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $t('Warehouse, products, quantities, customer, and user for every sale.') }}</p>
                </div>
                <div class="flex items-center gap-3 print:hidden">
                    <input v-model="salesSearch" type="text" class="theme-form-input text-sm py-2 w-full sm:w-56" :placeholder="$t('Search table...')" />
                    <span class="text-xs font-bold text-indigo-600 whitespace-nowrap">{{ sales?.total ?? 0 }} {{ $t('records') }}</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[1150px]">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell cursor-pointer" @click="sortSales('sale_date')">{{ $t('Sale Date & Time') }}</th>
                            <th class="theme-table-header-cell cursor-pointer" @click="sortSales('invoice_no')">{{ $t('Invoice No.') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Customer') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Warehouse') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Product(s)') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Total Quantity') }}</th>
                            <th class="theme-table-header-cell text-right cursor-pointer" @click="sortSales('grand_total')">{{ $t('Grand Total') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Paid Amount') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Due Amount') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Sold By') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Status') }}</th>
                            <th class="theme-table-header-cell text-right print:hidden">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="row in filteredSales" :key="row.id" class="theme-table-row hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ row.datetime }}</td>
                            <td class="px-4 py-3 font-bold text-indigo-600">{{ row.invoice_no }}</td>
                            <td class="px-4 py-3 font-medium">{{ row.customer_name }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 text-slate-700 dark:text-slate-300">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    {{ row.warehouse_name }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 max-w-[200px] truncate" :title="row.products_label">{{ row.products_label }}</td>
                            <td class="px-4 py-3 text-right font-bold text-sky-700 dark:text-sky-300">{{ row.total_quantity }}</td>
                            <td class="px-4 py-3 text-right font-black">{{ money(row.grand_total) }}</td>
                            <td class="px-4 py-3 text-right text-emerald-600 font-medium">{{ money(row.paid_amount) }}</td>
                            <td class="px-4 py-3 text-right font-medium" :class="row.due_amount > 0 ? 'text-rose-600' : 'text-slate-400'">{{ money(row.due_amount) }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ row.sold_by }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1 items-start">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-black uppercase" :class="saleStatusClass(row.sale_status)">{{ row.sale_status }}</span>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[10px] font-black uppercase" :class="paymentClass(row.payment_status)">{{ row.payment_status }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap print:hidden">
                                <Link :href="route('sales.show', row.id)" class="text-xs font-bold text-indigo-600 hover:underline mr-3">{{ $t('View') }}</Link>
                                <Link :href="route('sales.show', row.id) + '?print=1'" class="text-xs font-bold text-slate-500 hover:text-slate-700">{{ $t('Print') }}</Link>
                            </td>
                        </tr>
                        <tr v-if="!filteredSales.length"><td colspan="12" class="px-6 py-12 text-center text-slate-400">{{ $t('No sales records found.') }}</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="theme-table-footer flex flex-col sm:flex-row sm:items-center sm:justify-between print:hidden">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest dark:text-slate-200">
                    {{ $t('Showing') }} {{ sales.from || 0 }} {{ $t('to') }} {{ sales.to || 0 }} {{ $t('of') }} {{ sales.total }} {{ $t('entries') }}
                </div>
                <div class="flex flex-wrap justify-center items-center gap-1.5 mt-4 sm:mt-0">
                    <template v-for="(link, k) in sales.links" :key="k">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border transition-all duration-200"
                            :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']"
                        />
                        <span
                            v-else
                            v-html="link.label"
                            class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold text-slate-300 bg-white border border-slate-100 rounded-lg cursor-not-allowed dark:text-slate-600 dark:bg-slate-800"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Sales Return History -->
        <div class="theme-table-card mb-8">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Sales Return History') }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $t('Returned stock, warehouse, customer, and processing user for every return.') }}</p>
                </div>
                <div class="flex items-center gap-3 print:hidden">
                    <input v-model="returnsSearch" type="text" class="theme-form-input text-sm py-2 w-full sm:w-56" :placeholder="$t('Search table...')" />
                    <span class="text-xs font-bold text-violet-600 whitespace-nowrap">{{ returns?.total ?? 0 }} {{ $t('records') }}</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-[1100px]">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell cursor-pointer" @click="sortReturns('return_date')">{{ $t('Return Date & Time') }}</th>
                            <th class="theme-table-header-cell cursor-pointer" @click="sortReturns('reference_no')">{{ $t('Return Reference No.') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Original Invoice') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Customer') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Warehouse') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Returned Product(s)') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Returned Quantity') }}</th>
                            <th class="theme-table-header-cell text-right cursor-pointer" @click="sortReturns('total_amount')">{{ $t('Return Amount') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Returned By') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Return Reason') }}</th>
                            <th class="theme-table-header-cell text-right print:hidden">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="row in filteredReturns" :key="row.id" class="theme-table-row hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ row.datetime }}</td>
                            <td class="px-4 py-3 font-bold text-violet-600">{{ row.reference_no }}</td>
                            <td class="px-4 py-3">
                                <Link v-if="row.sale_id" :href="route('sales.show', row.sale_id)" class="text-indigo-600 font-bold hover:underline">{{ row.invoice_no }}</Link>
                                <span v-else>{{ row.invoice_no }}</span>
                            </td>
                            <td class="px-4 py-3 font-medium">{{ row.customer_name }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 text-slate-700 dark:text-slate-300">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                    {{ row.warehouse_name }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-600 max-w-[200px] truncate" :title="row.products_label">{{ row.products_label }}</td>
                            <td class="px-4 py-3 text-right font-bold text-sky-700 dark:text-sky-300">{{ row.total_quantity }}</td>
                            <td class="px-4 py-3 text-right font-black text-amber-600">{{ money(row.total_amount) }}</td>
                            <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ row.returned_by }}</td>
                            <td class="px-4 py-3 text-slate-600 max-w-[140px] truncate" :title="row.return_reason">{{ row.return_reason }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap print:hidden">
                                <Link :href="route('sale-returns.show', row.id)" class="text-xs font-bold text-violet-600 hover:underline mr-3">{{ $t('View') }}</Link>
                                <Link :href="route('sale-returns.show', row.id) + '?print=1'" class="text-xs font-bold text-slate-500 hover:text-slate-700">{{ $t('Print') }}</Link>
                            </td>
                        </tr>
                        <tr v-if="!filteredReturns.length"><td colspan="11" class="px-6 py-12 text-center text-slate-400">{{ $t('No return records found.') }}</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="theme-table-footer flex flex-col sm:flex-row sm:items-center sm:justify-between print:hidden">
                <div class="text-[11px] font-bold text-violet-700 uppercase tracking-widest dark:text-slate-200">
                    {{ $t('Showing') }} {{ returns.from || 0 }} {{ $t('to') }} {{ returns.to || 0 }} {{ $t('of') }} {{ returns.total }} {{ $t('entries') }}
                </div>
                <div class="flex flex-wrap justify-center items-center gap-1.5 mt-4 sm:mt-0">
                    <template v-for="(link, k) in returns.links" :key="k">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border transition-all duration-200"
                            :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']"
                        />
                        <span
                            v-else
                            v-html="link.label"
                            class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold text-slate-300 bg-white border border-slate-100 rounded-lg cursor-not-allowed dark:text-slate-600 dark:bg-slate-800"
                        />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@media print {
    :deep(.print\:hidden) { display: none !important; }
}
</style>

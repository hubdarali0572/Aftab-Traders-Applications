<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    sales: Object,
    customers: Array,
    filters: Object,
    saleStatuses: Array,
});

const searchQuery = ref(props.filters?.search ?? '');
const customerId = ref(props.filters?.customer_id ?? '');
const saleStatus = ref(props.filters?.sale_status ?? '');

const money = (v) => `$${Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
const formatStatus = (s) => s?.charAt(0).toUpperCase() + s?.slice(1) ?? '—';

const applyFilters = () => {
    router.get(route('customers.sales-history'), {
        search: searchQuery.value || undefined,
        customer_id: customerId.value || undefined,
        sale_status: saleStatus.value || undefined,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    searchQuery.value = '';
    customerId.value = '';
    saleStatus.value = '';
    applyFilters();
};

const statusClass = (status) => ({
    draft: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
    completed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
    cancelled: 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
}[status] || '');
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Customer Sales History')" />

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">{{ $t('Customer Sales History') }}</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">{{ $t('All sales invoices linked to customer accounts.') }}</p>
            </div>
            <Link :href="route('customers.index')" class="theme-form-back-link">{{ $t('Back to Customers') }}</Link>
        </div>

        <div class="theme-table-card mb-6">
            <form @submit.prevent="applyFilters" class="p-6 flex flex-col md:flex-row gap-4">
                <input v-model="searchQuery" type="text" class="theme-form-input flex-1" :placeholder="$t('Invoice no. or customer...')" />
                <select v-model="customerId" class="theme-form-input md:w-56">
                    <option value="">{{ $t('All Customers') }}</option>
                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.customer_code }} — {{ c.customer_name }}</option>
                </select>
                <select v-model="saleStatus" class="theme-form-input md:w-40">
                    <option value="">{{ $t('All Status') }}</option>
                    <option v-for="s in saleStatuses" :key="s" :value="s">{{ formatStatus(s) }}</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="theme-btn-primary px-6 py-2.5">{{ $t('Search') }}</button>
                    <button v-if="filters?.search || filters?.customer_id || filters?.sale_status" type="button" @click="clearFilters" class="theme-form-back-link px-4 py-2.5">{{ $t('Clear') }}</button>
                </div>
            </form>
        </div>

        <div class="theme-table-card">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell">{{ $t('Date') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Invoice No.') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Customer') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Warehouse') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Grand Total') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Paid') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Due') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Status') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="sale in sales.data" :key="sale.id" class="theme-table-row">
                            <td class="px-6 py-3 text-sm">{{ sale.sale_date }}</td>
                            <td class="px-6 py-3 font-bold text-indigo-600">{{ sale.invoice_no }}</td>
                            <td class="px-6 py-3 text-sm">
                                <div class="font-bold">{{ sale.customer?.customer_name }}</div>
                                <div class="text-[10px] text-slate-400">{{ sale.customer?.customer_code }}</div>
                            </td>
                            <td class="px-6 py-3 text-sm">{{ sale.warehouse?.name || '—' }}</td>
                            <td class="px-6 py-3 text-sm text-right font-bold">{{ money(sale.grand_total) }}</td>
                            <td class="px-6 py-3 text-sm text-right text-emerald-600">{{ money(sale.paid_amount) }}</td>
                            <td class="px-6 py-3 text-sm text-right text-rose-600">{{ money(sale.due_amount) }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest" :class="statusClass(sale.sale_status)">
                                    {{ formatStatus(sale.sale_status) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <Link :href="route('sales.show', sale.id)" class="theme-table-action-btn inline-flex" :title="$t('View')">
                                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="sales.data.length === 0">
                            <td colspan="9" class="px-6 py-12 text-center text-slate-400 font-medium">{{ $t('No sales found.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="theme-table-footer flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">
                    {{ $t('Showing') }} {{ sales.from || 0 }} {{ $t('to') }} {{ sales.to || 0 }} {{ $t('of') }} {{ sales.total }} {{ $t('entries') }}
                </div>
                <div class="flex flex-wrap justify-center items-center gap-1.5 mt-4 sm:mt-0">
                    <template v-for="(link, k) in sales.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label"
                            class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border transition-all duration-200"
                            :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']" />
                        <span v-else v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold text-slate-300 bg-white border border-slate-100 rounded-lg cursor-not-allowed dark:text-slate-600 dark:bg-slate-800" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

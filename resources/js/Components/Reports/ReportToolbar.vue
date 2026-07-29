<script setup>
import { router } from '@inertiajs/vue3';
import { computed, reactive, watch } from 'vue';
import { useLocale } from '@/i18n';

const { t } = useLocale();

const props = defineProps({
    title: String,
    subtitle: { type: String, default: '' },
    category: { type: String, default: 'Report Management' },
    filters: { type: Object, default: () => ({}) },
    options: { type: Object, default: () => ({}) },
    routeName: { type: String, required: true },
    showWarehouse: { type: Boolean, default: true },
    showCustomer: { type: Boolean, default: false },
    showProduct: { type: Boolean, default: false },
    showCategory: { type: Boolean, default: false },
    showBrand: { type: Boolean, default: false },
    showUser: { type: Boolean, default: false },
    showPaymentStatus: { type: Boolean, default: false },
    showPaymentMethod: { type: Boolean, default: false },
    showExpenseHead: { type: Boolean, default: false },
    showAging: { type: Boolean, default: false },
    showTransactionType: { type: Boolean, default: false },
    transactionTypes: { type: Object, default: () => ({}) },
    showDate: { type: Boolean, default: true },
    showSearch: { type: Boolean, default: true },
});

const form = reactive({
    date_from: props.filters?.date_from ?? '',
    date_to: props.filters?.date_to ?? '',
    warehouse_id: props.filters?.warehouse_id ?? '',
    customer_id: props.filters?.customer_id ?? '',
    product_id: props.filters?.product_id ?? '',
    category_id: props.filters?.category_id ?? '',
    brand_id: props.filters?.brand_id ?? '',
    user_id: props.filters?.user_id ?? '',
    payment_status: props.filters?.payment_status ?? '',
    payment_method: props.filters?.payment_method ?? '',
    expense_head_id: props.filters?.expense_head_id ?? '',
    aging: props.filters?.aging ?? '',
    transaction_type: props.filters?.transaction_type ?? '',
    search: props.filters?.search ?? '',
});

watch(
    () => props.filters,
    (v) => {
        Object.keys(form).forEach((k) => {
            form[k] = v?.[k] ?? '';
        });
    },
    { deep: true },
);

const hasFilters = computed(() =>
    Object.values(form).some((v) => v !== '' && v != null),
);

const cleanParams = (extra = {}) => {
    const params = { ...form, ...extra };
    Object.keys(params).forEach((k) => {
        if (params[k] === '' || params[k] === null || params[k] === undefined) delete params[k];
    });
    return params;
};

const apply = () => {
    router.get(route(props.routeName), cleanParams(), { preserveState: true, replace: true });
};

const clear = () => {
    Object.keys(form).forEach((k) => {
        form[k] = '';
    });
    apply();
};

const exportUrl = () => {
    const params = new URLSearchParams(Object.entries(cleanParams({ export: 'csv' })).filter(([, v]) => v != null && v !== ''));
    return `${route(props.routeName)}?${params.toString()}`;
};

const printPage = () => window.print();
</script>

<template>
    <div class="mb-6 print:hidden space-y-6">
        <!-- Page header -->
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-indigo-500 mb-1">{{ t(category) }}</p>
                <h2 class="text-2xl font-black text-slate-800 tracking-tight dark:text-slate-100">{{ t(title) }}</h2>
                <p v-if="subtitle" class="text-sm text-slate-500 mt-1 dark:text-slate-400">{{ t(subtitle) }}</p>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                <a :href="exportUrl()" class="theme-form-back-link px-4 py-2 text-sm font-bold">{{ t('Export CSV') }}</a>
                <button type="button" class="theme-form-back-link px-4 py-2 text-sm font-bold" @click="printPage">{{ t('Print') }}</button>
            </div>
        </div>

        <!-- Filters -->
        <div class="theme-table-card sticky top-0 z-20 shadow-md overflow-hidden">
            <div class="px-6 py-3 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between gap-3 bg-gradient-to-r from-indigo-50/80 to-white dark:from-indigo-950/30 dark:to-slate-800">
                <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ t('Filters') }}</h3>
                <button
                    v-if="hasFilters"
                    type="button"
                    class="text-xs font-bold text-rose-600 hover:text-rose-700 dark:text-rose-400"
                    @click="clear"
                >
                    {{ t('Clear All') }}
                </button>
            </div>
            <form @submit.prevent="apply" class="p-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div v-if="showDate" class="sm:col-span-2">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ t('Date Range') }}</label>
                    <div class="flex gap-2">
                        <input v-model="form.date_from" type="date" class="theme-form-input flex-1" />
                        <input v-model="form.date_to" type="date" class="theme-form-input flex-1" />
                    </div>
                </div>
                <div v-if="showWarehouse">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ t('Warehouse') }}</label>
                    <select v-model="form.warehouse_id" class="theme-form-input w-full">
                        <option value="">{{ t('All Warehouses') }}</option>
                        <option v-for="w in options.warehouses || []" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                </div>
                <div v-if="showCustomer">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ t('Customer') }}</label>
                    <select v-model="form.customer_id" class="theme-form-input w-full">
                        <option value="">{{ t('All Customers') }}</option>
                        <option v-for="c in options.customers || []" :key="c.id" :value="c.id">{{ c.customer_name || c.company_name }}</option>
                    </select>
                </div>
                <div v-if="showProduct">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ t('Product') }}</label>
                    <select v-model="form.product_id" class="theme-form-input w-full">
                        <option value="">{{ t('All Products') }}</option>
                        <option v-for="p in options.products || []" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>
                <div v-if="showCategory">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ t('Category') }}</label>
                    <select v-model="form.category_id" class="theme-form-input w-full">
                        <option value="">{{ t('All Categories') }}</option>
                        <option v-for="c in options.categories || []" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>
                <div v-if="showBrand">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ t('Brand') }}</label>
                    <select v-model="form.brand_id" class="theme-form-input w-full">
                        <option value="">{{ t('All Brands') }}</option>
                        <option v-for="b in options.brands || []" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                </div>
                <div v-if="showUser">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ t('Sales Person') }}</label>
                    <select v-model="form.user_id" class="theme-form-input w-full">
                        <option value="">{{ t('All Sales Persons') }}</option>
                        <option v-for="u in options.users || []" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                </div>
                <div v-if="showPaymentStatus">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ t('Payment Status') }}</label>
                    <select v-model="form.payment_status" class="theme-form-input w-full">
                        <option value="">{{ t('All Payment Status') }}</option>
                        <option value="paid">{{ t('Paid') }}</option>
                        <option value="partial">{{ t('Partial') }}</option>
                        <option value="unpaid">{{ t('Unpaid') }}</option>
                    </select>
                </div>
                <div v-if="showPaymentMethod">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ t('Payment Method') }}</label>
                    <select v-model="form.payment_method" class="theme-form-input w-full">
                        <option value="">{{ t('All Payment Methods') }}</option>
                        <option value="cash">{{ t('Cash') }}</option>
                        <option value="bank">{{ t('Bank') }}</option>
                        <option value="cheque">{{ t('Cheque') }}</option>
                        <option value="online">{{ t('Online') }}</option>
                        <option value="credit">{{ t('Credit') }}</option>
                    </select>
                </div>
                <div v-if="showExpenseHead">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ t('Expense Head') }}</label>
                    <select v-model="form.expense_head_id" class="theme-form-input w-full">
                        <option value="">{{ t('All Expense Heads') }}</option>
                        <option v-for="h in options.expenseHeads || []" :key="h.id" :value="h.id">{{ h.name }}</option>
                    </select>
                </div>
                <div v-if="showAging">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ t('Aging') }}</label>
                    <select v-model="form.aging" class="theme-form-input w-full">
                        <option value="">{{ t('All Aging') }}</option>
                        <option value="30">{{ t('0–30 Days') }}</option>
                        <option value="60">{{ t('31–60 Days') }}</option>
                        <option value="90">{{ t('61–90 Days') }}</option>
                        <option value="90_plus">{{ t('Above 90 Days') }}</option>
                    </select>
                </div>
                <div v-if="showTransactionType">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ t('Transaction Type') }}</label>
                    <select v-model="form.transaction_type" class="theme-form-input w-full">
                        <option value="">{{ t('All Types') }}</option>
                        <option v-for="(label, key) in transactionTypes" :key="key" :value="key">{{ t(label) }}</option>
                    </select>
                </div>
                <div v-if="showSearch" class="sm:col-span-2">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-400 mb-1.5">{{ t('Search') }}</label>
                    <input v-model="form.search" type="text" class="theme-form-input w-full" :placeholder="t('Search...')" />
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="theme-btn-primary px-6 py-2.5 w-full sm:w-auto">{{ t('Apply Filters') }}</button>
                </div>
            </form>
        </div>
    </div>
</template>

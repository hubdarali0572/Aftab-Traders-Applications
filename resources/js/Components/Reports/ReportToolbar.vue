<script setup>
import { router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({
    title: String,
    subtitle: { type: String, default: '' },
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
    { deep: true }
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
    Object.keys(form).forEach((k) => { form[k] = ''; });
    apply();
};
</script>

<template>
    <div class="mb-6 print:hidden">
        <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">{{ title }}</h2>
        <p v-if="subtitle" class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">{{ subtitle }}</p>
    </div>

    <div class="theme-table-card mb-6 print:hidden">
        <form @submit.prevent="apply" class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-3">
                <input v-if="showDate" v-model="form.date_from" type="date" class="theme-form-input" title="From date" />
                <input v-if="showDate" v-model="form.date_to" type="date" class="theme-form-input" title="To date" />
                <select v-if="showWarehouse" v-model="form.warehouse_id" class="theme-form-input">
                    <option value="">All Warehouses</option>
                    <option v-for="w in options.warehouses || []" :key="w.id" :value="w.id">{{ w.name }}</option>
                </select>
                <select v-if="showCustomer" v-model="form.customer_id" class="theme-form-input">
                    <option value="">All Customers</option>
                    <option v-for="c in options.customers || []" :key="c.id" :value="c.id">{{ c.customer_name || c.company_name }}</option>
                </select>
                <select v-if="showProduct" v-model="form.product_id" class="theme-form-input">
                    <option value="">All Products</option>
                    <option v-for="p in options.products || []" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
                <select v-if="showCategory" v-model="form.category_id" class="theme-form-input">
                    <option value="">All Categories</option>
                    <option v-for="c in options.categories || []" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <select v-if="showBrand" v-model="form.brand_id" class="theme-form-input">
                    <option value="">All Brands</option>
                    <option v-for="b in options.brands || []" :key="b.id" :value="b.id">{{ b.name }}</option>
                </select>
                <select v-if="showUser" v-model="form.user_id" class="theme-form-input">
                    <option value="">All Sales Persons</option>
                    <option v-for="u in options.users || []" :key="u.id" :value="u.id">{{ u.name }}</option>
                </select>
                <select v-if="showPaymentStatus" v-model="form.payment_status" class="theme-form-input">
                    <option value="">All Payment Status</option>
                    <option value="paid">Paid</option>
                    <option value="partial">Partial</option>
                    <option value="unpaid">Unpaid</option>
                </select>
                <select v-if="showPaymentMethod" v-model="form.payment_method" class="theme-form-input">
                    <option value="">All Payment Methods</option>
                    <option value="cash">Cash</option>
                    <option value="bank">Bank</option>
                    <option value="cheque">Cheque</option>
                    <option value="online">Online</option>
                    <option value="credit">Credit</option>
                </select>
                <select v-if="showExpenseHead" v-model="form.expense_head_id" class="theme-form-input">
                    <option value="">All Expense Heads</option>
                    <option v-for="h in options.expenseHeads || []" :key="h.id" :value="h.id">{{ h.name }}</option>
                </select>
                <select v-if="showAging" v-model="form.aging" class="theme-form-input">
                    <option value="">All Aging</option>
                    <option value="30">0–30 Days</option>
                    <option value="60">31–60 Days</option>
                    <option value="90">61–90 Days</option>
                    <option value="90_plus">Above 90 Days</option>
                </select>
                <select v-if="showTransactionType" v-model="form.transaction_type" class="theme-form-input">
                    <option value="">All Types</option>
                    <option v-for="(label, key) in transactionTypes" :key="key" :value="key">{{ label }}</option>
                </select>
                <input v-if="showSearch" v-model="form.search" type="text" class="theme-form-input sm:col-span-2" placeholder="Search..." />
                <div class="flex gap-2 sm:col-span-2 lg:col-span-2">
                    <button type="submit" class="theme-btn-primary px-6 py-2.5">Filter</button>
                    <button type="button" class="theme-form-back-link px-4 py-2.5" @click="clear">Clear</button>
                </div>
            </div>
        </form>
    </div>
</template>

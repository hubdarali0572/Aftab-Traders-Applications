<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ReportBarChart from "@/Components/Reports/ReportBarChart.vue";
import ReportHBarChart from "@/Components/Reports/ReportHBarChart.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    kpis: { type: Object, default: () => ({}) },
    charts: { type: Object, default: () => ({}) },
    tables: { type: Object, default: () => ({}) },
});

const money = (v) =>
    Number(v || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

const maxOf = (arr, key) =>
    Math.max(...(arr || []).map((i) => Number(i[key] || 0)), 1);

const num = (v) => Number(v || 0).toLocaleString();

const monthly = computed(() => props.charts?.monthly || []);
const warehouseDist = computed(() => props.charts?.warehouse_distribution || []);
const categoryInv = computed(() => props.charts?.category_inventory || []);
const topSelling = computed(() => props.charts?.top_selling || []);
const topCustomers = computed(() => props.charts?.top_customers || []);
const recentPurchases = computed(() => props.tables?.recent_purchases || []);
const recentSales = computed(() => props.tables?.recent_sales || []);
const recentTransfers = computed(() => props.tables?.recent_transfers || []);
const lowStock = computed(() => props.tables?.low_stock || []);
const outOfStock = computed(() => props.tables?.out_of_stock || []);
const outstandingCustomers = computed(() => props.tables?.outstanding_customers || []);

const categoryMax = computed(() => maxOf(categoryInv.value, "quantity"));

const orderStatusCards = computed(() => {
    const byStatus = props.kpis?.orders_by_status || {};
    const meta = {
        pending: {
            title: "Pending",
            iconPath: "M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z",
            tone: "text-amber-600 dark:text-amber-400",
        },
        confirmed: {
            title: "Confirmed",
            iconPath: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
            tone: "text-sky-600 dark:text-sky-400",
        },
        completed: {
            title: "Completed",
            iconPath: "M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z",
            tone: "text-emerald-600 dark:text-emerald-400",
        },
        cancelled: {
            title: "Cancelled",
            iconPath: "M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z",
            tone: "text-rose-600 dark:text-rose-400",
        },
    };

    return Object.keys(meta).map((status) => ({
        status,
        title: meta[status].title,
        iconPath: meta[status].iconPath,
        tone: meta[status].tone,
        amount: byStatus[status]?.amount || 0,
        count: byStatus[status]?.count || 0,
    }));
});

const expenseStatusCards = computed(() => {
    const byStatus = props.kpis?.expenses_by_status || {};
    const meta = {
        draft: {
            title: "Draft",
            iconPath: "M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z",
            tone: "text-slate-600 dark:text-slate-300",
        },
        approved: {
            title: "Approved",
            iconPath: "M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z",
            tone: "text-sky-600 dark:text-sky-400",
        },
        paid: {
            title: "Paid",
            iconPath: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
            tone: "text-emerald-600 dark:text-emerald-400",
        },
        cancelled: {
            title: "Cancelled",
            iconPath: "M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z",
            tone: "text-rose-600 dark:text-rose-400",
        },
    };

    return Object.keys(meta).map((status) => ({
        status,
        title: meta[status].title,
        iconPath: meta[status].iconPath,
        tone: meta[status].tone,
        amount: byStatus[status]?.amount || 0,
        count: byStatus[status]?.count || 0,
    }));
});

const kpiCards = computed(() => [
    {
        title: "Products",
        value: num(props.kpis.total_products),
        iconPath:
            "M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4",
    },
    {
        title: "Warehouses",
        value: num(props.kpis.total_warehouses),
        iconPath:
            "M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6",
    },
    {
        title: "Customers",
        value: num(props.kpis.total_customers),
        iconPath:
            "M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z",
    },
    {
        title: "Stock Value",
        value: `$${money(props.kpis.inventory_value)}`,
        iconPath:
            "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
    },
    {
        title: "Sales",
        value: `$${money(props.kpis.sales_amount)}`,
        sub: `${num(props.kpis.total_sales)} invoices`,
        iconPath:
            "M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z",
    },
    {
        title: "Purchases",
        value: `$${money(props.kpis.purchase_amount)}`,
        sub: `${num(props.kpis.total_purchases)} orders`,
        iconPath: "M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z",
    },
    {
        title: "Gross Profit",
        value: `$${money(props.kpis.gross_profit)}`,
        iconPath: "M13 7h8m0 0v8m0-8l-8 8-4-4-6 6",
        positive: Number(props.kpis.gross_profit || 0) >= 0,
    },
    {
        title: "Outstanding",
        value: `$${money(props.kpis.outstanding_balance)}`,
        iconPath:
            "M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z",
        alert: Number(props.kpis.outstanding_balance || 0) > 0,
    },
    {
        title: "Low Stock",
        value: num(props.kpis.low_stock_count),
        iconPath:
            "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z",
        alert: Number(props.kpis.low_stock_count || 0) > 0,
    },
    {
        title: "Out of Stock",
        value: num(props.kpis.out_of_stock_count),
        iconPath: "M6 18L18 6M6 6l12 12",
        alert: Number(props.kpis.out_of_stock_count || 0) > 0,
    },
]);

const quickActions = [
    {
        title: "New Purchase",
        description: "Record a supplier purchase order.",
        route: "purchases.create",
        iconPath: "M12 5v14m7-7H5",
    },
    {
        title: "New Sale",
        description: "Create a customer sales invoice.",
        route: "sales.create",
        iconPath:
            "M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17",
    },
    {
        title: "Stock Transfer",
        description: "Move inventory between warehouses.",
        route: "stock-transfers.create",
        iconPath: "M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4",
    },
    {
        title: "Add Customer",
        description: "Register a new customer account.",
        route: "customers.create",
        iconPath:
            "M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z",
    },
    {
        title: "Products",
        description: "Browse and manage product catalog.",
        route: "products.index",
        iconPath:
            "M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4",
    },
];

const moneyShort = (v) => {
    const n = Number(v || 0);
    if (n >= 1000000) return `$${(n / 1000000).toFixed(1)}M`;
    if (n >= 1000) return `$${(n / 1000).toFixed(1)}K`;
    return `$${n.toFixed(0)}`;
};

const monthlyLabels = computed(() =>
    monthly.value.map((m) => m.label?.split(" ")[0] || m.month),
);
const monthlyPurchases = computed(() =>
    monthly.value.map((m) => Number(m.purchases || 0)),
);
const monthlySalesVals = computed(() =>
    monthly.value.map((m) => Number(m.sales || 0)),
);
const warehouseItems = computed(() =>
    warehouseDist.value.map((w) => ({ name: w.name, amount: Number(w.quantity || 0) })),
);

const barHeight = (value, max) =>
    `${Math.max((Number(value || 0) / max) * 100, value > 0 ? 4 : 0)}%`;
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <div class="max-w-[1700px] mx-auto space-y-6 lg:space-y-8">
            <!-- Welcome Banner -->
            <div
                class="bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-900 rounded-xl p-6 lg:p-10 text-white relative overflow-hidden shadow-lg"
            >
                <div class="relative z-10">
                    <h2 class="text-xs lg:text-3xl font-bold opacity-90 uppercase tracking-widest">
                        Inventory ERP Overview
                    </h2>
                    <p class="mt-3 text-white/80 text-sm lg:text-base max-w-2xl leading-relaxed">
                        Real-time snapshot of inventory, sales, purchases, stock
                        levels, and customer balances across your warehouses.
                    </p>
                </div>
                <div class="absolute -right-10 -top-10 w-40 h-40 lg:w-64 lg:h-64 bg-white/10 rounded-full blur-2xl"></div>
                <div class="absolute right-20 bottom-0 w-20 h-20 bg-white/5 rounded-full blur-xl"></div>
            </div>

            <!-- KPI Cards — 5 per row -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 lg:gap-4">
                <div
                    v-for="stat in kpiCards"
                    :key="stat.title"
                    class="group bg-white p-4 lg:p-5 rounded-sm shadow-sm border flex justify-between items-start transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md dark:bg-slate-800"
                    :class="
                        stat.alert
                            ? 'border-amber-200 hover:border-amber-300 dark:border-amber-500/30'
                            : 'border-slate-100 hover:border-indigo-200 dark:border-slate-700 dark:hover:border-indigo-500/40'
                    "
                >
                    <div class="min-w-0 pr-2">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.12em] dark:text-slate-500 truncate">
                            {{ stat.title }}
                        </p>
                        <p
                            class="text-xl lg:text-2xl font-black mt-1.5 truncate"
                            :class="
                                stat.alert
                                    ? 'text-amber-600 dark:text-amber-400'
                                    : stat.positive === false
                                      ? 'text-rose-600 dark:text-rose-400'
                                      : 'text-gray-800 dark:text-slate-100'
                            "
                        >
                            {{ stat.value }}
                        </p>
                        <p v-if="stat.sub" class="text-[10px] text-slate-400 mt-0.5 dark:text-slate-500">
                            {{ stat.sub }}
                        </p>
                    </div>
                    <div class="bg-slate-50 p-2 rounded-sm text-slate-400 shrink-0 transition-all duration-300 group-hover:bg-indigo-50 group-hover:text-indigo-600 dark:bg-slate-700 dark:text-slate-300">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="stat.iconPath" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Orders by Status -->
            <div class="space-y-3">
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider dark:text-slate-100">
                            Orders by Status
                        </h3>
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                            Total first, then amount and count for each order status
                        </p>
                    </div>
                    <Link
                        :href="route('orders.index')"
                        class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                    >
                        View orders
                    </Link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 lg:gap-4">
                    <div
                        class="group bg-white p-4 lg:p-5 rounded-sm shadow-sm border border-indigo-100 flex justify-between items-start transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md hover:border-indigo-300 dark:bg-slate-800 dark:border-indigo-500/30 dark:hover:border-indigo-500/50"
                    >
                        <div class="min-w-0 pr-2">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.12em] dark:text-slate-500 truncate">
                                Total Orders
                            </p>
                            <p class="text-xl lg:text-2xl font-black mt-1.5 truncate text-slate-800 dark:text-slate-100">
                                ${{ money(kpis.all_orders_amount ?? kpis.orders_amount) }}
                            </p>
                            <p class="text-[10px] text-slate-400 mt-0.5 dark:text-slate-500">
                                {{ num(kpis.all_orders_count ?? kpis.total_orders) }}
                                {{ Number((kpis.all_orders_count ?? kpis.total_orders) || 0) === 1 ? 'order' : 'orders' }}
                            </p>
                        </div>
                        <div class="bg-indigo-50 p-2 rounded-sm text-indigo-600 shrink-0 dark:bg-indigo-500/15 dark:text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                                />
                            </svg>
                        </div>
                    </div>
                    <div
                        v-for="stat in orderStatusCards"
                        :key="stat.status"
                        class="group bg-white p-4 lg:p-5 rounded-sm shadow-sm border border-slate-100 flex justify-between items-start transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md hover:border-indigo-200 dark:bg-slate-800 dark:border-slate-700 dark:hover:border-indigo-500/40"
                    >
                        <div class="min-w-0 pr-2">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.12em] dark:text-slate-500 truncate">
                                {{ stat.title }}
                            </p>
                            <p class="text-xl lg:text-2xl font-black mt-1.5 truncate" :class="stat.tone">
                                ${{ money(stat.amount) }}
                            </p>
                            <p class="text-[10px] text-slate-400 mt-0.5 dark:text-slate-500">
                                {{ num(stat.count) }} {{ stat.count === 1 ? 'order' : 'orders' }}
                            </p>
                        </div>
                        <div class="bg-slate-50 p-2 rounded-sm text-slate-400 shrink-0 transition-all duration-300 group-hover:bg-indigo-50 group-hover:text-indigo-600 dark:bg-slate-700 dark:text-slate-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="stat.iconPath" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Expenses by Status -->
            <div class="space-y-3">
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider dark:text-slate-100">
                            Expenses by Status
                        </h3>
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                            Total first, then amount and count for each expense status
                        </p>
                    </div>
                    <Link
                        :href="route('expenses.index')"
                        class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                    >
                        View expenses
                    </Link>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 lg:gap-4">
                    <div
                        class="group bg-white p-4 lg:p-5 rounded-sm shadow-sm border border-indigo-100 flex justify-between items-start transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md hover:border-indigo-300 dark:bg-slate-800 dark:border-indigo-500/30 dark:hover:border-indigo-500/50"
                    >
                        <div class="min-w-0 pr-2">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.12em] dark:text-slate-500 truncate">
                                Total Expenses
                            </p>
                            <p class="text-xl lg:text-2xl font-black mt-1.5 truncate text-slate-800 dark:text-slate-100">
                                ${{ money(kpis.all_expenses_amount) }}
                            </p>
                            <p class="text-[10px] text-slate-400 mt-0.5 dark:text-slate-500">
                                {{ num(kpis.all_expenses_count) }}
                                {{ Number(kpis.all_expenses_count || 0) === 1 ? 'expense' : 'expenses' }}
                            </p>
                        </div>
                        <div class="bg-indigo-50 p-2 rounded-sm text-indigo-600 shrink-0 dark:bg-indigo-500/15 dark:text-indigo-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                        </div>
                    </div>
                    <div
                        v-for="stat in expenseStatusCards"
                        :key="stat.status"
                        class="group bg-white p-4 lg:p-5 rounded-sm shadow-sm border border-slate-100 flex justify-between items-start transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md hover:border-indigo-200 dark:bg-slate-800 dark:border-slate-700 dark:hover:border-indigo-500/40"
                    >
                        <div class="min-w-0 pr-2">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.12em] dark:text-slate-500 truncate">
                                {{ stat.title }}
                            </p>
                            <p class="text-xl lg:text-2xl font-black mt-1.5 truncate" :class="stat.tone">
                                ${{ money(stat.amount) }}
                            </p>
                            <p class="text-[10px] text-slate-400 mt-0.5 dark:text-slate-500">
                                {{ num(stat.count) }} {{ stat.count === 1 ? 'expense' : 'expenses' }}
                            </p>
                        </div>
                        <div class="bg-slate-50 p-2 rounded-sm text-slate-400 shrink-0 transition-all duration-300 group-hover:bg-indigo-50 group-hover:text-indigo-600 dark:bg-slate-700 dark:text-slate-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="stat.iconPath" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 lg:gap-6 items-start">
                <div class="theme-form-card xl:col-span-2 overflow-hidden self-start">
                    <div class="theme-form-section-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <h3 class="theme-form-section-title">Monthly Purchases vs Sales</h3>
                            <p class="mt-1 text-xs text-slate-400">Last 12 months comparison · fixed chart height</p>
                        </div>
                    </div>
                    <div class="p-4 lg:p-6">
                        <ReportBarChart
                            :labels="monthlyLabels"
                            :series="[
                                { key: 'purchases', label: 'Purchases', color: '#4f46e5', data: monthlyPurchases },
                                { key: 'sales', label: 'Sales', color: '#10b981', data: monthlySalesVals },
                            ]"
                            :height="280"
                            :format-value="moneyShort"
                        />
                    </div>
                </div>

                <div class="theme-form-card overflow-hidden self-start">
                    <div class="theme-form-section-header">
                        <h3 class="theme-form-section-title">Warehouse Stock</h3>
                        <p class="mt-1 text-xs text-slate-400">Quantity by location</p>
                    </div>
                    <div class="p-4 lg:p-6">
                        <ReportHBarChart
                            :items="warehouseItems"
                            color="#4f46e5"
                            :height="280"
                            :format-value="(v) => Number(v || 0).toLocaleString()"
                            empty-text="No warehouse data."
                        />
                    </div>
                </div>
            </div>

            <!-- Category Inventory -->
            <div class="theme-form-card overflow-hidden">
                <div class="theme-form-section-header">
                    <h3 class="theme-form-section-title">Product Stocks</h3>
                    <p class="mt-1 text-xs text-slate-400">Top products by quantity · fixed chart height</p>
                </div>
                <div class="p-4 lg:p-6">
                    <div v-if="categoryInv.length" class="h-[220px] overflow-x-auto">
                        <div class="flex h-full min-w-full items-end gap-3 px-1">
                            <div
                                v-for="cat in categoryInv"
                                :key="cat.name"
                                class="flex h-full w-[72px] shrink-0 flex-col items-center justify-end sm:w-[88px]"
                            >
                                <span class="mb-1 text-[10px] font-bold tabular-nums text-slate-500">{{ num(cat.quantity) }}</span>
                                <div class="flex w-full flex-1 items-end justify-center rounded-md bg-slate-50 px-2 pt-2 dark:bg-slate-800/60">
                                    <div
                                        class="w-8 rounded-t-md bg-gradient-to-t from-indigo-600 to-indigo-400 transition-all duration-300 dark:from-indigo-500 dark:to-indigo-300 sm:w-10"
                                        :style="{ height: barHeight(cat.quantity, categoryMax) }"
                                        :title="`${cat.name}: ${num(cat.quantity)} units`"
                                    ></div>
                                </div>
                                <p class="mt-2 w-full truncate text-center text-[10px] font-bold text-slate-600 dark:text-slate-300">
                                    {{ cat.name }}
                                </p>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500">${{ money(cat.value) }}</p>
                            </div>
                        </div>
                    </div>
                    <p v-else class="py-8 text-center text-sm text-slate-400">No category data.</p>
                </div>
            </div>

            <!-- Top Selling + Top Customers -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                <div class="theme-table-card">
                    <div class="theme-form-section-header">
                        <h3 class="theme-form-section-title">Top Selling Products</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell">Product</th>
                                    <th class="theme-table-header-cell text-right">Qty</th>
                                    <th class="theme-table-header-cell text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="(item, i) in topSelling" :key="item.name" class="theme-table-row">
                                    <td class="px-6 py-3 text-sm font-medium text-slate-700 dark:text-slate-300">
                                        <span class="text-indigo-600 dark:text-indigo-400 mr-2 text-xs font-black">{{ i + 1 }}.</span>
                                        {{ item.name }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-right text-slate-600 dark:text-slate-400">{{ num(item.qty) }}</td>
                                    <td class="px-6 py-3 text-sm text-right font-bold text-slate-700 dark:text-slate-300">${{ money(item.amount) }}</td>
                                </tr>
                                <tr v-if="!topSelling.length">
                                    <td colspan="3" class="px-6 py-8 text-center text-sm text-slate-400">No sales data yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="theme-table-card">
                    <div class="theme-form-section-header">
                        <h3 class="theme-form-section-title">Top Customers</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell">Customer</th>
                                    <th class="theme-table-header-cell text-right">Invoices</th>
                                    <th class="theme-table-header-cell text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="(item, i) in topCustomers" :key="item.name" class="theme-table-row">
                                    <td class="px-6 py-3 text-sm font-medium text-slate-700 dark:text-slate-300">
                                        <span class="text-indigo-600 dark:text-indigo-400 mr-2 text-xs font-black">{{ i + 1 }}.</span>
                                        {{ item.name }}
                                    </td>
                                    <td class="px-6 py-3 text-sm text-right text-slate-600 dark:text-slate-400">{{ num(item.invoices) }}</td>
                                    <td class="px-6 py-3 text-sm text-right font-bold text-slate-700 dark:text-slate-300">${{ money(item.amount) }}</td>
                                </tr>
                                <tr v-if="!topCustomers.length">
                                    <td colspan="3" class="px-6 py-8 text-center text-sm text-slate-400">No customer data yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
                <div class="theme-table-card">
                    <div class="theme-form-section-header flex items-center justify-between">
                        <h3 class="theme-form-section-title">Recent Purchases</h3>
                        <Link :href="route('purchases.index')" class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">View all</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell">PO #</th>
                                    <th class="theme-table-header-cell">Date</th>
                                    <th class="theme-table-header-cell text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="p in recentPurchases" :key="p.id" class="theme-table-row">
                                    <td class="px-4 py-2.5">
                                        <Link :href="route('purchases.show', p.id)" class="text-xs font-bold text-indigo-600 hover:underline dark:text-indigo-400">{{ p.purchase_no }}</Link>
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-slate-500 dark:text-slate-400">{{ p.purchase_date }}</td>
                                    <td class="px-4 py-2.5 text-xs text-right font-bold text-slate-700 dark:text-slate-300">${{ money(p.grand_total) }}</td>
                                </tr>
                                <tr v-if="!recentPurchases.length">
                                    <td colspan="3" class="px-4 py-6 text-center text-xs text-slate-400">No recent purchases.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="theme-table-card">
                    <div class="theme-form-section-header flex items-center justify-between">
                        <h3 class="theme-form-section-title">Recent Sales</h3>
                        <Link :href="route('sales.index')" class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">View all</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell">Invoice</th>
                                    <th class="theme-table-header-cell">Date</th>
                                    <th class="theme-table-header-cell text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="s in recentSales" :key="s.id" class="theme-table-row">
                                    <td class="px-4 py-2.5">
                                        <Link :href="route('sales.show', s.id)" class="text-xs font-bold text-indigo-600 hover:underline dark:text-indigo-400">{{ s.invoice_no }}</Link>
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-slate-500 dark:text-slate-400">{{ s.sale_date }}</td>
                                    <td class="px-4 py-2.5 text-xs text-right font-bold text-slate-700 dark:text-slate-300">${{ money(s.grand_total) }}</td>
                                </tr>
                                <tr v-if="!recentSales.length">
                                    <td colspan="3" class="px-4 py-6 text-center text-xs text-slate-400">No recent sales.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="theme-table-card">
                    <div class="theme-form-section-header flex items-center justify-between">
                        <h3 class="theme-form-section-title">Recent Transfers</h3>
                        <Link :href="route('stock-transfers.index')" class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">View all</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell">Ref #</th>
                                    <th class="theme-table-header-cell">Date</th>
                                    <th class="theme-table-header-cell text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="t in recentTransfers" :key="t.id" class="theme-table-row">
                                    <td class="px-4 py-2.5">
                                        <Link :href="route('stock-transfers.show', t.id)" class="text-xs font-bold text-indigo-600 hover:underline dark:text-indigo-400">{{ t.reference_no }}</Link>
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-slate-500 dark:text-slate-400">{{ t.transfer_date }}</td>
                                    <td class="px-4 py-2.5 text-xs text-right font-bold text-slate-700 dark:text-slate-300">${{ money(t.total_amount) }}</td>
                                </tr>
                                <tr v-if="!recentTransfers.length">
                                    <td colspan="3" class="px-4 py-6 text-center text-xs text-slate-400">No recent transfers.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
                <div class="theme-table-card">
                    <div class="theme-form-section-header">
                        <h3 class="theme-form-section-title text-amber-700 dark:text-amber-400">Low Stock Alerts</h3>
                        <p class="mt-1 text-xs text-slate-400">{{ kpis.low_stock_count || 0 }} items below minimum</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell">Product</th>
                                    <th class="theme-table-header-cell">Warehouse</th>
                                    <th class="theme-table-header-cell text-right">Avail / Min</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="row in lowStock" :key="`${row.product_id}-${row.warehouse_id}`" class="theme-table-row">
                                    <td class="px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-300">{{ row.product?.name }}</td>
                                    <td class="px-4 py-2.5 text-xs text-slate-500 dark:text-slate-400">{{ row.warehouse?.name }}</td>
                                    <td class="px-4 py-2.5 text-xs text-right font-bold text-amber-600 dark:text-amber-400">{{ num(row.available_quantity) }} / {{ num(row.minimum_stock) }}</td>
                                </tr>
                                <tr v-if="!lowStock.length">
                                    <td colspan="3" class="px-4 py-6 text-center text-xs text-emerald-600 dark:text-emerald-400">All stock levels healthy.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="theme-table-card">
                    <div class="theme-form-section-header">
                        <h3 class="theme-form-section-title text-rose-700 dark:text-rose-400">Out of Stock</h3>
                        <p class="mt-1 text-xs text-slate-400">{{ kpis.out_of_stock_count || 0 }} items unavailable</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell">Product</th>
                                    <th class="theme-table-header-cell">Warehouse</th>
                                    <th class="theme-table-header-cell text-right">Available</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="row in outOfStock" :key="`oos-${row.product_id}-${row.warehouse_id}`" class="theme-table-row">
                                    <td class="px-4 py-2.5 text-xs font-medium text-slate-700 dark:text-slate-300">{{ row.product?.name }}</td>
                                    <td class="px-4 py-2.5 text-xs text-slate-500 dark:text-slate-400">{{ row.warehouse?.name }}</td>
                                    <td class="px-4 py-2.5 text-xs text-right font-bold text-rose-600 dark:text-rose-400">{{ num(row.available_quantity) }}</td>
                                </tr>
                                <tr v-if="!outOfStock.length">
                                    <td colspan="3" class="px-4 py-6 text-center text-xs text-emerald-600 dark:text-emerald-400">No out-of-stock items.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="theme-table-card">
                    <div class="theme-form-section-header">
                        <h3 class="theme-form-section-title">Outstanding Customers</h3>
                        <p class="mt-1 text-xs text-slate-400">Total receivable: ${{ money(kpis.outstanding_balance) }}</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell">Customer</th>
                                    <th class="theme-table-header-cell text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="c in outstandingCustomers" :key="c.id" class="theme-table-row">
                                    <td class="px-4 py-2.5">
                                        <Link :href="route('customers.show', c.id)" class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">{{ c.customer_name }}</Link>
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-right font-bold text-rose-600 dark:text-rose-400">${{ money(c.outstanding) }}</td>
                                </tr>
                                <tr v-if="!outstandingCustomers.length">
                                    <td colspan="2" class="px-4 py-6 text-center text-xs text-emerald-600 dark:text-emerald-400">No outstanding balances.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

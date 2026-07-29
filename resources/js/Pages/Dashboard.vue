<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ReportBarChart from "@/Components/Reports/ReportBarChart.vue";
import ReportHBarChart from "@/Components/Reports/ReportHBarChart.vue";
import { Head, Link } from "@inertiajs/vue3";
import { computed } from "vue";
import { useLocale } from "@/i18n";

const { t } = useLocale();

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

const num = (v) => Number(v || 0).toLocaleString();

const monthly = computed(() => props.charts?.monthly || []);
const warehouseDist = computed(() => props.charts?.warehouse_distribution || []);
const topSelling = computed(() => props.charts?.top_selling || []);
const topCustomers = computed(() => props.charts?.top_customers || []);
const recentPurchases = computed(() => props.tables?.recent_purchases || []);
const recentSales = computed(() => props.tables?.recent_sales || []);
const lowStock = computed(() => props.tables?.low_stock || []);
const outstandingCustomers = computed(() => props.tables?.outstanding_customers || []);

const kpiCards = computed(() => [
    {
        title: "Total Sales",
        value: `$${money(props.kpis.sales_amount)}`,
        sub: `${t("Today")}: $${money(props.kpis.sales_today)}`,
        iconPath: "M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z",
        tone: "from-emerald-500 to-teal-600",
    },
    {
        title: "Total Purchases",
        value: `$${money(props.kpis.purchase_amount)}`,
        sub: `${t("Today")}: $${money(props.kpis.purchase_today)}`,
        iconPath: "M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z",
        tone: "from-indigo-500 to-violet-600",
    },
    {
        title: "Net Profit",
        value: `$${money(props.kpis.net_profit)}`,
        sub: `${t("Gross")}: $${money(props.kpis.gross_profit)}`,
        iconPath: "M13 7h8m0 0v8m0-8l-8 8-4-4-6 6",
        tone: Number(props.kpis.net_profit || 0) >= 0 ? "from-violet-500 to-purple-600" : "from-rose-500 to-red-600",
        highlight: Number(props.kpis.net_profit || 0) < 0,
    },
    {
        title: "Stock Value",
        value: `$${money(props.kpis.inventory_value)}`,
        sub: `${num(props.kpis.total_inventory_qty)} ${t("units")}`,
        iconPath: "M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4",
        tone: "from-sky-500 to-blue-600",
    },
    {
        title: "Outstanding",
        value: `$${money(props.kpis.outstanding_balance)}`,
        sub: `${num(props.kpis.total_customers)} ${t("customers")}`,
        iconPath: "M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z",
        tone: "from-amber-500 to-orange-600",
        alert: Number(props.kpis.outstanding_balance || 0) > 0,
    },
    {
        title: "Operating Expenses",
        value: `$${money(props.kpis.operating_expenses)}`,
        sub: `${t("Today")}: $${money(props.kpis.expenses_today)}`,
        iconPath: "M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z",
        tone: "from-slate-600 to-slate-800",
    },
    {
        title: "Low Stock",
        value: num(props.kpis.low_stock_count),
        sub: `${num(props.kpis.out_of_stock_count)} ${t("out of stock")}`,
        iconPath: "M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z",
        tone: "from-amber-500 to-yellow-600",
        alert: Number(props.kpis.low_stock_count || 0) > 0,
    },
    {
        title: "Products",
        value: num(props.kpis.total_products),
        sub: `${num(props.kpis.total_warehouses)} ${t("warehouses")}`,
        iconPath: "M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4",
        tone: "from-indigo-400 to-indigo-600",
    },
]);

const quickActions = [
    { title: "New Sale", route: "sales.create", icon: "M12 5v14m7-7H5" },
    { title: "New Purchase", route: "purchases.create", icon: "M12 5v14m7-7H5" },
    { title: "Reports", route: "reports.index", icon: "M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" },
    { title: "Profit & Loss", route: "reports.financial.profit-loss", icon: "M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" },
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
const monthlyProfit = computed(() =>
    monthly.value.map((m) => Number(m.profit || 0)),
);
const warehouseItems = computed(() =>
    warehouseDist.value.map((w) => ({ name: w.name, amount: Number(w.quantity || 0) })),
);
const topSellingItems = computed(() =>
    topSelling.value.map((p) => ({ name: p.name, amount: Number(p.qty || 0) })),
);
const topCustomerItems = computed(() =>
    topCustomers.value.map((c) => ({ name: c.name, amount: Number(c.amount || 0) })),
);
</script>

<template>
    <Head :title="$t('Dashboard')" />

    <AuthenticatedLayout>
        <div class="max-w-[1600px] mx-auto space-y-6 lg:space-y-8">
            <!-- Hero -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-950 to-violet-900 p-6 lg:p-10 text-white shadow-xl">
                <div class="relative z-10 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-indigo-300">
                            {{ t("Aftab Traders") }}
                        </p>
                        <h2 class="mt-2 text-xl sm:text-2xl lg:text-3xl font-black tracking-tight">
                            {{ t("Business Dashboard") }}
                        </h2>
                        <p class="mt-2 text-sm text-white/70 max-w-xl">
                            {{ t("Essential KPIs, trends and alerts for your wholesale & retail operations.") }}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <div class="rounded-xl bg-white/10 px-4 py-3 backdrop-blur-sm">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-white/60">{{ t("Sales Today") }}</p>
                            <p class="text-lg font-black text-emerald-300">${{ money(kpis.sales_today) }}</p>
                        </div>
                        <div class="rounded-xl bg-white/10 px-4 py-3 backdrop-blur-sm">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-white/60">{{ t("Purchases Today") }}</p>
                            <p class="text-lg font-black text-indigo-200">${{ money(kpis.purchase_today) }}</p>
                        </div>
                    </div>
                </div>
                <div class="absolute -right-10 -top-10 h-48 w-48 rounded-full bg-indigo-500/20 blur-3xl"></div>
            </div>

            <!-- Quick actions -->
            <div class="flex flex-wrap gap-2">
                <Link
                    v-for="action in quickActions"
                    :key="action.route"
                    :href="route(action.route)"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-700 transition-all hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:border-indigo-500/40 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-300"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="action.icon" />
                    </svg>
                    {{ t(action.title) }}
                </Link>
            </div>

            <!-- KPI cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
                <div
                    v-for="stat in kpiCards"
                    :key="stat.title"
                    class="relative overflow-hidden rounded-xl border bg-white p-4 lg:p-5 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md dark:bg-slate-800"
                    :class="stat.alert ? 'border-amber-200 dark:border-amber-500/30' : 'border-slate-100 dark:border-slate-700'"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 truncate">
                                {{ t(stat.title) }}
                            </p>
                            <p
                                class="mt-1.5 text-xl lg:text-2xl font-black truncate"
                                :class="stat.highlight ? 'text-rose-600 dark:text-rose-400' : 'text-slate-800 dark:text-slate-100'"
                            >
                                {{ stat.value }}
                            </p>
                            <p v-if="stat.sub" class="mt-0.5 text-[10px] text-slate-400 dark:text-slate-500 truncate">
                                {{ stat.sub }}
                            </p>
                        </div>
                        <div
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gradient-to-br text-white shadow-sm"
                            :class="stat.tone"
                        >
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="stat.iconPath" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 lg:gap-6">
                <div class="theme-form-card xl:col-span-2 overflow-hidden">
                    <div class="theme-form-section-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <h3 class="theme-form-section-title">{{ t("Sales, Purchases & Profit") }}</h3>
                            <p class="mt-1 text-xs text-slate-400">{{ t("Last 12 months") }}</p>
                        </div>
                        <Link
                            :href="route('reports.financial.profit-loss')"
                            class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 hover:text-indigo-700 dark:text-indigo-400"
                        >
                            {{ t("Full P&L report") }} →
                        </Link>
                    </div>
                    <div class="p-4 lg:p-6">
                        <ReportBarChart
                            :labels="monthlyLabels"
                            :series="[
                                { key: 'sales', label: 'Sales', color: '#10b981', data: monthlySalesVals },
                                { key: 'purchases', label: 'Purchases', color: '#6366f1', data: monthlyPurchases },
                                { key: 'profit', label: 'Net Profit', color: '#8b5cf6', data: monthlyProfit },
                            ]"
                            :height="300"
                            :format-value="moneyShort"
                        />
                    </div>
                </div>

                <div class="theme-form-card overflow-hidden">
                    <div class="theme-form-section-header">
                        <h3 class="theme-form-section-title">{{ t("Warehouse Stock") }}</h3>
                        <p class="mt-1 text-xs text-slate-400">{{ t("Quantity by location") }}</p>
                    </div>
                    <div class="p-4 lg:p-6">
                        <ReportHBarChart
                            :items="warehouseItems"
                            color="#6366f1"
                            :height="300"
                            :format-value="(v) => Number(v || 0).toLocaleString()"
                            empty-text="No warehouse data."
                        />
                    </div>
                </div>
            </div>

            <!-- Top performers charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                <div class="theme-form-card overflow-hidden">
                    <div class="theme-form-section-header">
                        <h3 class="theme-form-section-title">{{ t("Top Selling Products") }}</h3>
                    </div>
                    <div class="p-4 lg:p-6">
                        <ReportHBarChart
                            :items="topSellingItems"
                            color="#10b981"
                            :height="220"
                            :format-value="(v) => Number(v || 0).toLocaleString()"
                            empty-text="No sales data yet."
                        />
                    </div>
                </div>
                <div class="theme-form-card overflow-hidden">
                    <div class="theme-form-section-header">
                        <h3 class="theme-form-section-title">{{ t("Top Customers") }}</h3>
                    </div>
                    <div class="p-4 lg:p-6">
                        <ReportHBarChart
                            :items="topCustomerItems"
                            color="#8b5cf6"
                            :height="220"
                            :format-value="moneyShort"
                            empty-text="No customer data yet."
                        />
                    </div>
                </div>
            </div>

            <!-- Recent activity + alerts -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
                <div class="theme-table-card">
                    <div class="theme-form-section-header flex items-center justify-between">
                        <h3 class="theme-form-section-title">{{ t("Recent Sales") }}</h3>
                        <Link :href="route('sales.index')" class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">{{ t("View all") }}</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell">{{ $t("Invoice") }}</th>
                                    <th class="theme-table-header-cell">{{ $t("Date") }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t("Total") }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="s in recentSales" :key="s.id" class="theme-table-row">
                                    <td class="px-4 py-2.5">
                                        <Link :href="route('sales.show', s.id)" class="text-xs font-bold text-indigo-600 hover:underline dark:text-indigo-400">{{ s.invoice_no }}</Link>
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-slate-500">{{ s.sale_date }}</td>
                                    <td class="px-4 py-2.5 text-xs text-right font-bold text-slate-700 dark:text-slate-300">${{ money(s.grand_total) }}</td>
                                </tr>
                                <tr v-if="!recentSales.length">
                                    <td colspan="3" class="px-4 py-6 text-center text-xs text-slate-400">{{ $t("No recent sales.") }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="theme-table-card">
                    <div class="theme-form-section-header flex items-center justify-between">
                        <h3 class="theme-form-section-title">{{ t("Recent Purchases") }}</h3>
                        <Link :href="route('purchases.index')" class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">{{ t("View all") }}</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell">{{ $t("PO #") }}</th>
                                    <th class="theme-table-header-cell">{{ $t("Date") }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t("Total") }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="p in recentPurchases" :key="p.id" class="theme-table-row">
                                    <td class="px-4 py-2.5">
                                        <Link :href="route('purchases.show', p.id)" class="text-xs font-bold text-indigo-600 hover:underline dark:text-indigo-400">{{ p.purchase_no }}</Link>
                                    </td>
                                    <td class="px-4 py-2.5 text-xs text-slate-500">{{ p.purchase_date }}</td>
                                    <td class="px-4 py-2.5 text-xs text-right font-bold text-slate-700 dark:text-slate-300">${{ money(p.grand_total) }}</td>
                                </tr>
                                <tr v-if="!recentPurchases.length">
                                    <td colspan="3" class="px-4 py-6 text-center text-xs text-slate-400">{{ $t("No recent purchases.") }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="theme-table-card">
                    <div class="theme-form-section-header">
                        <h3 class="theme-form-section-title text-amber-700 dark:text-amber-400">{{ t("Alerts") }}</h3>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ kpis.low_stock_count || 0 }} {{ t("low stock") }} · ${{ money(kpis.outstanding_balance) }} {{ t("receivable") }}
                        </p>
                    </div>
                    <div class="divide-y divide-slate-100 dark:divide-slate-700">
                        <div v-for="row in lowStock.slice(0, 3)" :key="`ls-${row.product_id}-${row.warehouse_id}`" class="flex items-center justify-between px-4 py-3">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-medium text-slate-700 dark:text-slate-300">{{ row.product?.name }}</p>
                                <p class="text-[10px] text-slate-400">{{ row.warehouse?.name }}</p>
                            </div>
                            <span class="shrink-0 text-xs font-bold text-amber-600">{{ num(row.quantity) }} / {{ num(row.minimum_stock) }}</span>
                        </div>
                        <div v-for="c in outstandingCustomers.slice(0, 3)" :key="`oc-${c.id}`" class="flex items-center justify-between px-4 py-3">
                            <Link :href="route('customers.show', c.id)" class="truncate text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">{{ c.customer_name }}</Link>
                            <span class="shrink-0 text-xs font-bold text-rose-600">${{ money(c.outstanding) }}</span>
                        </div>
                        <p v-if="!lowStock.length && !outstandingCustomers.length" class="px-4 py-8 text-center text-xs text-emerald-600 dark:text-emerald-400">
                            {{ $t("All clear — no alerts.") }}
                        </p>
                    </div>
                    <div class="border-t border-slate-100 px-4 py-3 dark:border-slate-700">
                        <Link :href="route('reports.inventory.low-stock')" class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">{{ t("View stock report") }}</Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

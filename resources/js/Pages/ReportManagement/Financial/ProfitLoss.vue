<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReportToolbar from '@/Components/Reports/ReportToolbar.vue';
import ReportSummaryCards from '@/Components/Reports/ReportSummaryCards.vue';
import ReportBarChart from '@/Components/Reports/ReportBarChart.vue';
import ReportHBarChart from '@/Components/Reports/ReportHBarChart.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    summary: Object,
    expenseDistribution: { type: Array, default: () => [] },
    monthlyTrend: { type: Array, default: () => [] },
    filters: Object,
    options: Object,
});

const money = (v) => Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const moneyShort = (v) => {
    const n = Number(v || 0);
    if (Math.abs(n) >= 1000000) return `$${(n / 1000000).toFixed(1)}M`;
    if (Math.abs(n) >= 1000) return `$${(n / 1000).toFixed(1)}K`;
    return `$${n.toFixed(0)}`;
};

const isPositive = (v) => Number(v || 0) >= 0;

const cards = computed(() => [
    {
        title: 'Total Revenue',
        value: `$${money(props.summary?.total_revenue)}`,
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        bg: 'bg-indigo-50 dark:bg-indigo-500/10',
        tone: 'text-indigo-700 dark:text-indigo-300',
    },
    {
        title: 'Net Sales',
        value: `$${money(props.summary?.net_sales)}`,
        icon: 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
        bg: 'bg-emerald-50 dark:bg-emerald-500/10',
        tone: 'text-emerald-700 dark:text-emerald-300',
    },
    {
        title: 'COGS',
        value: `$${money(props.summary?.cogs)}`,
        icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        bg: 'bg-sky-50 dark:bg-sky-500/10',
        tone: 'text-sky-700 dark:text-sky-300',
    },
    {
        title: 'Gross Profit',
        value: `$${money(props.summary?.gross_profit)}`,
        icon: 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
        bg: isPositive(props.summary?.gross_profit) ? 'bg-emerald-50 dark:bg-emerald-500/10' : 'bg-rose-50 dark:bg-rose-500/10',
        tone: isPositive(props.summary?.gross_profit) ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300',
        highlight: true,
    },
    {
        title: 'Expenses',
        value: `$${money(props.summary?.operating_expenses)}`,
        icon: 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z',
        bg: 'bg-violet-50 dark:bg-violet-500/10',
        tone: 'text-violet-700 dark:text-violet-300',
    },
    {
        title: 'Net Profit',
        value: `$${money(props.summary?.net_profit)}`,
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        bg: isPositive(props.summary?.net_profit) ? 'bg-indigo-50 dark:bg-indigo-500/10' : 'bg-rose-50 dark:bg-rose-500/10',
        tone: isPositive(props.summary?.net_profit) ? 'text-indigo-700 dark:text-indigo-300' : 'text-rose-700 dark:text-rose-300',
        highlight: true,
    },
]);

const plRows = computed(() => [
    { label: 'Sales Revenue', value: props.summary?.sales_revenue },
    { label: '− Sales Returns', value: props.summary?.sales_returns, indent: true },
    { label: '= Net Sales', value: props.summary?.net_sales, bold: true, section: true },
    { label: 'Opening Stock', value: props.summary?.opening_stock },
    { label: '+ Purchases', value: props.summary?.purchases, indent: true },
    { label: '+ Purchase Expenses', value: props.summary?.purchase_expenses, indent: true },
    { label: '− Purchase Returns', value: props.summary?.purchase_returns, indent: true },
    { label: '− Closing Stock', value: props.summary?.closing_stock, indent: true },
    { label: '= Cost of Goods Sold', value: props.summary?.cogs, bold: true, section: true },
    { label: 'Gross Profit (Net Sales − COGS)', value: props.summary?.gross_profit, bold: true, profit: true },
    { label: '− Operating Expenses', value: props.summary?.operating_expenses, indent: true },
    { label: '= Net Profit', value: props.summary?.net_profit, bold: true, highlight: true, profit: true },
]);

const trendLabels = computed(() => props.monthlyTrend.map((m) => m.label?.replace(/^\w+\s/, '') || m.month));
const revenueValues = computed(() => props.monthlyTrend.map((m) => Number(m.revenue || 0)));
const expenseValues = computed(() => props.monthlyTrend.map((m) => Number(m.expenses || 0)));
const profitValues = computed(() => props.monthlyTrend.map((m) => Number(m.profit || 0)));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Profit & Loss Summary" />
        <ReportToolbar
            title="Profit & Loss Summary"
            subtitle="Generated from sales, purchases, inventory valuation and expenses"
            category="Financial Reports"
            route-name="reports.financial.profit-loss"
            :filters="filters"
            :options="options"
            :show-search="false"
        />
        <ReportSummaryCards
            :cards="cards"
            title="P&L Overview"
            subtitle="Key financial metrics for the selected period"
        />

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6 items-start">
            <div class="theme-table-card xl:col-span-1 overflow-hidden self-start">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-indigo-50 to-white dark:from-indigo-950/30 dark:to-slate-800">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">{{ $t('P&L Statement') }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $t('Line-by-line breakdown') }}</p>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700 max-h-[400px] overflow-y-auto">
                    <div
                        v-for="row in plRows"
                        :key="row.label"
                        class="flex justify-between px-6 py-3 text-sm transition-colors"
                        :class="[
                            row.highlight ? 'bg-gradient-to-r from-indigo-50 to-violet-50 dark:from-indigo-950/40 dark:to-violet-950/20' : '',
                            row.section ? 'bg-slate-50/80 dark:bg-slate-800/40' : '',
                        ]"
                    >
                        <span
                            class="pr-4"
                            :class="[
                                row.bold ? 'font-black text-slate-800 dark:text-slate-100' : 'text-slate-600 dark:text-slate-300',
                                row.indent ? 'pl-3 text-xs' : '',
                            ]"
                        >
                            {{ row.label }}
                        </span>
                        <span
                            class="font-bold tabular-nums shrink-0"
                            :class="
                                row.profit
                                    ? (Number(row.value) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400')
                                    : 'text-slate-800 dark:text-slate-100'
                            "
                        >
                            ${{ money(row.value) }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="theme-table-card xl:col-span-2 overflow-hidden self-start">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-indigo-50 to-white dark:from-indigo-950/30 dark:to-slate-800">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">{{ $t('Revenue vs Expenses') }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $t('Last 12 months comparison') }}</p>
                </div>
                <div class="p-5 lg:p-6">
                    <ReportBarChart
                        :labels="trendLabels"
                        :series="[
                            { key: 'revenue', label: 'Revenue', color: '#10b981', data: revenueValues },
                            { key: 'expenses', label: 'Expenses', color: '#8b5cf6', data: expenseValues },
                        ]"
                        :height="300"
                        :format-value="moneyShort"
                    />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-start">
            <div class="theme-table-card overflow-hidden self-start">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-indigo-50 to-white dark:from-indigo-950/30 dark:to-slate-800">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">{{ $t('Monthly Profit Trend') }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $t('Net profit by month') }}</p>
                </div>
                <div class="p-5 lg:p-6">
                    <ReportBarChart
                        :labels="trendLabels"
                        :values="profitValues"
                        color="#6366f1"
                        :height="260"
                        :format-value="moneyShort"
                        :show-legend="false"
                    />
                </div>
            </div>
            <div class="theme-table-card overflow-hidden self-start">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-indigo-50 to-white dark:from-indigo-950/30 dark:to-slate-800">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">{{ $t('Expense Distribution') }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $t('Operating expenses by category') }}</p>
                </div>
                <div class="p-5 lg:p-6">
                    <ReportHBarChart
                        :items="expenseDistribution"
                        color="#8b5cf6"
                        :height="260"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

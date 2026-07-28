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

const cards = computed(() => [
    { title: 'Total Revenue', value: `$${money(props.summary?.total_revenue)}` },
    { title: 'Net Sales', value: `$${money(props.summary?.net_sales)}` },
    { title: 'COGS', value: `$${money(props.summary?.cogs)}` },
    { title: 'Gross Profit', value: `$${money(props.summary?.gross_profit)}`, tone: Number(props.summary?.gross_profit) >= 0 ? 'text-emerald-600' : 'text-rose-600' },
    { title: 'Expenses', value: `$${money(props.summary?.operating_expenses)}`, tone: 'text-rose-600' },
    { title: 'Net Profit', value: `$${money(props.summary?.net_profit)}`, tone: Number(props.summary?.net_profit) >= 0 ? 'text-emerald-600' : 'text-rose-600' },
]);

const plRows = computed(() => [
    { label: 'Sales Revenue', value: props.summary?.sales_revenue },
    { label: '− Sales Returns', value: props.summary?.sales_returns },
    { label: '= Net Sales', value: props.summary?.net_sales, bold: true },
    { label: 'Opening Stock', value: props.summary?.opening_stock },
    { label: '+ Purchases', value: props.summary?.purchases },
    { label: '+ Purchase Expenses', value: props.summary?.purchase_expenses },
    { label: '− Purchase Returns', value: props.summary?.purchase_returns },
    { label: '− Closing Stock', value: props.summary?.closing_stock },
    { label: '= Cost of Goods Sold', value: props.summary?.cogs, bold: true },
    { label: 'Gross Profit (Net Sales − COGS)', value: props.summary?.gross_profit, bold: true },
    { label: '− Operating Expenses', value: props.summary?.operating_expenses },
    { label: '= Net Profit', value: props.summary?.net_profit, bold: true, highlight: true },
]);

const trendLabels = computed(() => props.monthlyTrend.map((m) => m.label?.replace(/^\w+\s/, '') || m.month));
const revenueValues = computed(() => props.monthlyTrend.map((m) => Number(m.revenue || 0)));
const expenseValues = computed(() => props.monthlyTrend.map((m) => Number(m.expenses || 0)));
const profitValues = computed(() => props.monthlyTrend.map((m) => Math.max(Number(m.profit || 0), 0)));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Profit & Loss Summary" />
        <ReportToolbar title="Profit & Loss Summary" subtitle="Generated from sales, purchases, inventory valuation and expenses" route-name="reports.financial.profit-loss" :filters="filters" :options="options" :show-search="false" />
        <ReportSummaryCards :cards="cards" />

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-6 items-start">
            <div class="theme-form-card xl:col-span-1 overflow-hidden self-start">
                <div class="theme-form-section-header"><h3 class="theme-form-section-title">{{ $t('P&L Statement') }}</h3></div>
                <div class="divide-y divide-slate-100 dark:divide-slate-700 max-h-[360px] overflow-y-auto">
                    <div v-for="row in plRows" :key="row.label" class="flex justify-between px-6 py-3 text-sm" :class="row.highlight ? 'bg-indigo-50 dark:bg-indigo-500/10' : ''">
                        <span :class="row.bold ? 'font-black text-slate-800 dark:text-slate-100' : 'text-slate-600 dark:text-slate-300'">{{ row.label }}</span>
                        <span class="font-bold tabular-nums" :class="row.highlight ? (Number(row.value) >= 0 ? 'text-emerald-600' : 'text-rose-600') : 'text-slate-800 dark:text-slate-100'">${{ money(row.value) }}</span>
                    </div>
                </div>
            </div>

            <div class="theme-form-card xl:col-span-2 overflow-hidden self-start">
                <div class="theme-form-section-header">
                    <div>
                        <h3 class="theme-form-section-title">{{ $t('Revenue vs Expenses') }}</h3>
                        <p class="mt-1 text-xs text-slate-400">{{ $t('Last 12 months comparison · fixed chart height') }}</p>
                    </div>
                </div>
                <div class="p-5 lg:p-6">
                    <ReportBarChart
                        :labels="trendLabels"
                        :series="[
                            { key: 'revenue', label: 'Revenue', color: '#10b981', data: revenueValues },
                            { key: 'expenses', label: 'Expenses', color: '#e11d48', data: expenseValues },
                        ]"
                        :height="280"
                        :format-value="moneyShort"
                    />
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 items-start">
            <div class="theme-form-card overflow-hidden self-start">
                <div class="theme-form-section-header">
                    <div>
                        <h3 class="theme-form-section-title">{{ $t('Monthly Profit Trend') }}</h3>
                        <p class="mt-1 text-xs text-slate-400">{{ $t('Positive profit by month') }}</p>
                    </div>
                </div>
                <div class="p-5">
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
            <div class="theme-form-card overflow-hidden self-start">
                <div class="theme-form-section-header">
                    <div>
                        <h3 class="theme-form-section-title">{{ $t('Expense Distribution') }}</h3>
                        <p class="mt-1 text-xs text-slate-400">{{ $t('Operating expenses by head') }}</p>
                    </div>
                </div>
                <div class="p-5">
                    <ReportHBarChart
                        :items="expenseDistribution"
                        color="#e11d48"
                        :height="260"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

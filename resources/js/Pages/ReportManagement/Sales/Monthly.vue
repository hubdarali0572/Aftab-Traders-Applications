<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReportToolbar from '@/Components/Reports/ReportToolbar.vue';
import ReportSummaryCards from '@/Components/Reports/ReportSummaryCards.vue';
import ReportBarChart from '@/Components/Reports/ReportBarChart.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    rows: { type: Array, default: () => [] },
    summary: Object,
    chart: { type: Array, default: () => [] },
    filters: Object,
    options: Object,
});

const money = (v) => Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const moneyShort = (v) => {
    const n = Number(v || 0);
    if (n >= 1000000) return `$${(n / 1000000).toFixed(1)}M`;
    if (n >= 1000) return `$${(n / 1000).toFixed(1)}K`;
    return `$${n.toFixed(0)}`;
};
const num = (v) => Number(v || 0).toLocaleString();

const cards = computed(() => [
    { title: 'Net Sales', value: `$${money(props.summary?.total_sales)}` },
    { title: 'Invoices', value: num(props.summary?.total_invoices) },
    { title: 'Qty Sold', value: num(props.summary?.total_quantity) },
    { title: 'Paid', value: `$${money(props.summary?.total_paid)}` },
    { title: 'Outstanding', value: `$${money(props.summary?.total_outstanding)}`, tone: 'text-amber-600' },
]);

const chartLabels = computed(() => (props.chart || []).map((m) => m.label?.replace(/^\w+\s/, '') || m.month));
const netValues = computed(() => (props.chart || []).map((m) => Number(m.net_sales || 0)));
const paidValues = computed(() => (props.chart || []).map((m) => Number(m.payments_received || 0)));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Monthly Sales Report" />
        <ReportToolbar title="Monthly Sales Report" subtitle="Month-wise sales summary" route-name="reports.sales.monthly" :filters="filters" :options="options" :show-search="false" />
        <ReportSummaryCards :cards="cards" />

        <div class="theme-form-card mb-6 overflow-hidden">
            <div class="theme-form-section-header">
                <div>
                    <h3 class="theme-form-section-title">Monthly Sales Performance</h3>
                    <p class="mt-1 text-xs text-slate-400">Net sales vs payments received</p>
                </div>
            </div>
            <div class="p-5 lg:p-6">
                <ReportBarChart
                    :labels="chartLabels"
                    :series="[
                        { key: 'net', label: 'Net Sales', color: '#4f46e5', data: netValues },
                        { key: 'paid', label: 'Payments', color: '#10b981', data: paidValues },
                    ]"
                    :height="280"
                    :format-value="moneyShort"
                />
            </div>
        </div>

        <div class="theme-table-card overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1000px]">
                <thead>
                    <tr class="theme-table-header">
                        <th class="theme-table-header-cell">Month</th>
                        <th class="theme-table-header-cell text-right">Invoices</th>
                        <th class="theme-table-header-cell text-right">Customers</th>
                        <th class="theme-table-header-cell text-right">Qty</th>
                        <th class="theme-table-header-cell text-right">Gross</th>
                        <th class="theme-table-header-cell text-right">Discount</th>
                        <th class="theme-table-header-cell text-right">Tax</th>
                        <th class="theme-table-header-cell text-right">Net Sales</th>
                        <th class="theme-table-header-cell text-right">Payments</th>
                        <th class="theme-table-header-cell text-right">Outstanding</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="r in rows" :key="r.month" class="theme-table-row">
                        <td class="px-6 py-3 font-bold">{{ r.label }}</td>
                        <td class="px-6 py-3 text-right">{{ num(r.invoice_count) }}</td>
                        <td class="px-6 py-3 text-right">{{ num(r.customer_count) }}</td>
                        <td class="px-6 py-3 text-right">{{ num(r.total_quantity) }}</td>
                        <td class="px-6 py-3 text-right">${{ money(r.gross_sales) }}</td>
                        <td class="px-6 py-3 text-right">${{ money(r.discounts) }}</td>
                        <td class="px-6 py-3 text-right">${{ money(r.taxes) }}</td>
                        <td class="px-6 py-3 text-right font-bold">${{ money(r.net_sales) }}</td>
                        <td class="px-6 py-3 text-right">${{ money(r.payments_received) }}</td>
                        <td class="px-6 py-3 text-right">${{ money(r.outstanding) }}</td>
                    </tr>
                    <tr v-if="!rows.length"><td colspan="10" class="px-6 py-12 text-center text-slate-400">No data.</td></tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>

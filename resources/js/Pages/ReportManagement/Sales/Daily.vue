<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReportToolbar from '@/Components/Reports/ReportToolbar.vue';
import ReportSummaryCards from '@/Components/Reports/ReportSummaryCards.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    rows: Object,
    summary: Object,
    filters: Object,
    options: Object,
});

const money = (v) => Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const num = (v) => Number(v || 0).toLocaleString();
const formatDate = (v) => {
    if (!v) return '—';
    const d = new Date(v);
    return Number.isNaN(d.getTime()) ? v : d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const cards = computed(() => [
    { title: 'Total Sales', value: `$${money(props.summary?.total_sales)}` },
    { title: 'Qty Sold', value: num(props.summary?.total_quantity) },
    { title: 'Discount', value: `$${money(props.summary?.total_discount)}` },
    { title: 'Tax', value: `$${money(props.summary?.total_tax)}` },
    { title: 'Total Paid', value: `$${money(props.summary?.total_paid)}` },
    { title: 'Outstanding', value: `$${money(props.summary?.total_outstanding)}`, tone: 'text-amber-600' },
]);

const statusClass = (s) => ({
    paid: 'bg-emerald-100 text-emerald-700',
    partial: 'bg-amber-100 text-amber-700',
    unpaid: 'bg-rose-100 text-rose-700',
}[s] || 'bg-slate-100 text-slate-600');
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Daily Sales Report" />
        <div class="print:block">
            <ReportToolbar
                title="Daily Sales Report"
                subtitle="Invoice-level sales from sales module"
                route-name="reports.sales.daily"
                :filters="filters"
                :options="options"
                show-customer
                show-user
                show-payment-status
                show-payment-method
            />
            <ReportSummaryCards :cards="cards" />
            <div class="theme-table-card overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[1100px]">
                        <thead>
                            <tr class="theme-table-header">
                                <th class="theme-table-header-cell">Invoice No</th>
                                <th class="theme-table-header-cell">Date</th>
                                <th class="theme-table-header-cell">Customer</th>
                                <th class="theme-table-header-cell">Sales Person</th>
                                <th class="theme-table-header-cell">Warehouse</th>
                                <th class="theme-table-header-cell text-right">Qty</th>
                                <th class="theme-table-header-cell text-right">Gross</th>
                                <th class="theme-table-header-cell text-right">Discount</th>
                                <th class="theme-table-header-cell text-right">Tax</th>
                                <th class="theme-table-header-cell text-right">Net</th>
                                <th class="theme-table-header-cell text-right">Paid</th>
                                <th class="theme-table-header-cell text-right">Due</th>
                                <th class="theme-table-header-cell">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr v-for="r in rows.data" :key="r.id" class="theme-table-row">
                                <td class="px-6 py-3 font-bold text-indigo-600">{{ r.invoice_no }}</td>
                                <td class="px-6 py-3 text-sm">{{ formatDate(r.invoice_date) }}</td>
                                <td class="px-6 py-3 text-sm font-medium">{{ r.customer || '—' }}</td>
                                <td class="px-6 py-3 text-sm">{{ r.sales_person || '—' }}</td>
                                <td class="px-6 py-3 text-sm">{{ r.warehouse || '—' }}</td>
                                <td class="px-6 py-3 text-sm text-right">{{ num(r.total_quantity) }}</td>
                                <td class="px-6 py-3 text-sm text-right">${{ money(r.gross_amount) }}</td>
                                <td class="px-6 py-3 text-sm text-right">${{ money(r.discount) }}</td>
                                <td class="px-6 py-3 text-sm text-right">${{ money(r.tax) }}</td>
                                <td class="px-6 py-3 text-sm text-right font-bold">${{ money(r.net_amount) }}</td>
                                <td class="px-6 py-3 text-sm text-right">${{ money(r.paid_amount) }}</td>
                                <td class="px-6 py-3 text-sm text-right">${{ money(r.due_amount) }}</td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold capitalize" :class="statusClass(r.payment_status)">{{ r.payment_status }}</span>
                                </td>
                            </tr>
                            <tr v-if="!rows.data?.length">
                                <td colspan="13" class="px-6 py-12 text-center text-slate-400">No sales found for selected filters.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="theme-table-footer flex justify-between items-center print:hidden">
                    <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">Showing {{ rows.from || 0 }} to {{ rows.to || 0 }} of {{ rows.total || 0 }}</div>
                    <div class="flex gap-1.5">
                        <template v-for="(link, k) in rows.links || []" :key="k">
                            <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border" :class="link.active ? 'theme-pagination-active' : 'theme-pagination-inactive'" />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReportToolbar from '@/Components/Reports/ReportToolbar.vue';
import ReportSummaryCards from '@/Components/Reports/ReportSummaryCards.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ rows: Object, summary: Object, profile: Object, filters: Object, options: Object });
const money = (v) => Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const num = (v) => Number(v || 0).toLocaleString(undefined, { maximumFractionDigits: 2 });
const formatDate = (v) => (!v ? '—' : new Date(v).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }));
const cards = computed(() => {
    if (props.profile) {
        return [
            { title: 'Total Purchases', value: `$${money(props.profile.total_purchases)}` },
            { title: 'Total Paid', value: `$${money(props.profile.total_paid)}` },
            { title: 'Outstanding', value: `$${money(props.profile.outstanding_balance)}`, tone: 'text-amber-600' },
            { title: 'Last Purchase', value: formatDate(props.profile.last_purchase_date) },
        ];
    }
    return [
        { title: 'Invoices', value: num(props.summary?.invoices) },
        { title: 'Amount', value: `$${money(props.summary?.total_amount)}` },
        { title: 'Quantity', value: num(props.summary?.total_quantity) },
    ];
});
const statusClass = (s) => ({ paid: 'bg-emerald-100 text-emerald-700', partial: 'bg-amber-100 text-amber-700', unpaid: 'bg-rose-100 text-rose-700' }[s] || 'bg-slate-100 text-slate-600');
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Customer Sales History" />
        <ReportToolbar title="Sales History Report" subtitle="Select a customer to view complete purchase history" route-name="reports.customers.sales-history" :filters="filters" :options="options" show-customer />
        <div v-if="profile" class="mb-4 text-sm font-bold text-slate-700 dark:text-slate-200">
            Customer: <span class="text-indigo-600">{{ profile.customer }}</span>
            <span class="text-slate-400 font-medium ml-2">{{ profile.customer_code }}</span>
        </div>
        <ReportSummaryCards :cards="cards" />
        <div class="theme-table-card overflow-x-auto">
            <table class="w-full text-left min-w-[1100px]">
                <thead>
                    <tr class="theme-table-header">
                        <th class="theme-table-header-cell">Invoice No</th>
                        <th class="theme-table-header-cell">Date</th>
                        <th class="theme-table-header-cell">Products</th>
                        <th class="theme-table-header-cell text-right">Qty</th>
                        <th class="theme-table-header-cell text-right">Amount</th>
                        <th class="theme-table-header-cell text-right">Discount</th>
                        <th class="theme-table-header-cell text-right">Net Total</th>
                        <th class="theme-table-header-cell">Payment Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="r in rows.data" :key="r.id" class="theme-table-row">
                        <td class="px-6 py-3 font-bold text-indigo-600">{{ r.invoice_no }}</td>
                        <td class="px-6 py-3 text-sm">{{ formatDate(r.invoice_date) }}</td>
                        <td class="px-6 py-3 text-sm max-w-xs truncate" :title="r.products">{{ r.products || '—' }}</td>
                        <td class="px-6 py-3 text-right">{{ num(r.quantity) }}</td>
                        <td class="px-6 py-3 text-right">${{ money(r.amount) }}</td>
                        <td class="px-6 py-3 text-right">${{ money(r.discount) }}</td>
                        <td class="px-6 py-3 text-right font-bold">${{ money(r.net_total) }}</td>
                        <td class="px-6 py-3"><span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold capitalize" :class="statusClass(r.payment_status)">{{ r.payment_status }}</span></td>
                    </tr>
                    <tr v-if="!rows.data?.length"><td colspan="8" class="px-6 py-12 text-center text-slate-400">Select a customer to view sales history.</td></tr>
                </tbody>
            </table>
            <div class="theme-table-footer flex justify-between print:hidden">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">{{ rows.total || 0 }} invoices</div>
                <div class="flex gap-1.5">
                    <template v-for="(link, k) in rows.links || []" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border" :class="link.active ? 'theme-pagination-active' : 'theme-pagination-inactive'" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

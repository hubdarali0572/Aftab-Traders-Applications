<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReportToolbar from '@/Components/Reports/ReportToolbar.vue';
import ReportSummaryCards from '@/Components/Reports/ReportSummaryCards.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ rows: Object, summary: Object, filters: Object, options: Object });
const money = (v) => Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const num = (v) => Number(v || 0).toLocaleString();
const formatDate = (v) => (!v ? '—' : new Date(v).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }));
const cards = computed(() => [
    { title: 'Customers', value: num(props.summary?.customers) },
    { title: 'Total Sales', value: `$${money(props.summary?.total_sales)}` },
    { title: 'Paid', value: `$${money(props.summary?.total_paid)}` },
    { title: 'Outstanding', value: `$${money(props.summary?.total_outstanding)}`, tone: 'text-amber-600' },
    { title: 'Quantity', value: num(props.summary?.total_quantity) },
]);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Customer-wise Sales Report" />
        <ReportToolbar title="Customer-wise Sales Report" route-name="reports.sales.customer-wise" :filters="filters" :options="options" show-customer />
        <ReportSummaryCards :cards="cards" />
        <div class="theme-table-card overflow-x-auto">
            <table class="w-full text-left min-w-[1000px]">
                <thead>
                    <tr class="theme-table-header">
                        <th class="theme-table-header-cell">Customer</th>
                        <th class="theme-table-header-cell text-right">Invoices</th>
                        <th class="theme-table-header-cell text-right">Products</th>
                        <th class="theme-table-header-cell text-right">Quantity</th>
                        <th class="theme-table-header-cell text-right">Total Sales</th>
                        <th class="theme-table-header-cell text-right">Paid</th>
                        <th class="theme-table-header-cell text-right">Remaining</th>
                        <th class="theme-table-header-cell">Last Purchase</th>
                        <th class="theme-table-header-cell text-right print:hidden">History</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="r in rows.data" :key="r.customer_id" class="theme-table-row">
                        <td class="px-6 py-3 font-bold">{{ r.customer }} <span class="text-xs text-slate-400 font-medium">{{ r.customer_code }}</span></td>
                        <td class="px-6 py-3 text-right">{{ num(r.invoice_count) }}</td>
                        <td class="px-6 py-3 text-right">{{ num(r.total_products) }}</td>
                        <td class="px-6 py-3 text-right">{{ num(r.total_quantity) }}</td>
                        <td class="px-6 py-3 text-right font-bold">${{ money(r.total_sales) }}</td>
                        <td class="px-6 py-3 text-right">${{ money(r.paid_amount) }}</td>
                        <td class="px-6 py-3 text-right text-amber-600 font-bold">${{ money(r.remaining_balance) }}</td>
                        <td class="px-6 py-3">{{ formatDate(r.last_purchase_date) }}</td>
                        <td class="px-6 py-3 text-right print:hidden">
                            <Link :href="route('reports.customers.sales-history', { customer_id: r.customer_id })" class="text-xs font-bold text-indigo-600">View</Link>
                        </td>
                    </tr>
                    <tr v-if="!rows.data?.length"><td colspan="9" class="px-6 py-12 text-center text-slate-400">No data.</td></tr>
                </tbody>
            </table>
            <div class="theme-table-footer flex justify-between print:hidden">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">{{ rows.total || 0 }} customers</div>
                <div class="flex gap-1.5">
                    <template v-for="(link, k) in rows.links || []" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border" :class="link.active ? 'theme-pagination-active' : 'theme-pagination-inactive'" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

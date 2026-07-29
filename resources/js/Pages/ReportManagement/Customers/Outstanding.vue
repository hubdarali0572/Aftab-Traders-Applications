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
    { title: 'Outstanding', value: `$${money(props.summary?.total_outstanding)}`, tone: 'text-amber-600' },
    { title: 'Pending Invoices', value: num(props.summary?.pending_invoices) },
]);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Outstanding Balance Report" />
        <ReportToolbar title="Outstanding Balance Report" category="Customer Reports" route-name="reports.customers.outstanding" :filters="filters" :options="options" :show-date="false" show-customer show-aging />
        <ReportSummaryCards :cards="cards" subtitle="Receivable balances and aging" />
        <div class="theme-table-card overflow-x-auto">
            <table class="w-full text-left min-w-[1000px]">
                <thead>
                    <tr class="theme-table-header">
                        <th class="theme-table-header-cell">{{ $t('Customer') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Total Sales') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Total Paid') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Outstanding') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Pending Invoices') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Oldest Due') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Days') }}</th>
                        <th class="theme-table-header-cell text-right print:hidden">{{ $t('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="r in rows.data" :key="r.customer_id" class="theme-table-row">
                        <td class="px-6 py-3 font-bold">{{ r.customer }}</td>
                        <td class="px-6 py-3 text-right">${{ money(r.total_sales) }}</td>
                        <td class="px-6 py-3 text-right">${{ money(r.total_paid) }}</td>
                        <td class="px-6 py-3 text-right font-black text-amber-600">${{ money(r.outstanding_balance) }}</td>
                        <td class="px-6 py-3 text-right">{{ num(r.pending_invoices) }}</td>
                        <td class="px-6 py-3">{{ formatDate(r.oldest_due_date) }}</td>
                        <td class="px-6 py-3 text-right font-bold" :class="r.oldest_due_days > 90 ? 'text-rose-600' : ''">{{ num(r.oldest_due_days) }}</td>
                        <td class="px-6 py-3 text-right print:hidden">
                            <Link :href="route('reports.customers.ledger', { customer_id: r.customer_id })" class="text-xs font-bold text-indigo-600">{{ $t('Ledger') }}</Link>
                        </td>
                    </tr>
                    <tr v-if="!rows.data?.length"><td colspan="8" class="px-6 py-12 text-center text-slate-400">{{ $t('No outstanding balances.') }}</td></tr>
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

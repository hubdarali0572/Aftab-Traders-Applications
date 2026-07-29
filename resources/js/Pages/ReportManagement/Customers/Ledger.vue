<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReportToolbar from '@/Components/Reports/ReportToolbar.vue';
import ReportSummaryCards from '@/Components/Reports/ReportSummaryCards.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ rows: Object, summary: Object, filters: Object, options: Object, transactionTypes: Object });
const money = (v) => Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const formatDate = (v) => (!v ? '—' : new Date(v).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }));
const cards = computed(() => [
    { title: 'Entries', value: Number(props.summary?.entries || 0).toLocaleString() },
    { title: 'Total Debit', value: `$${money(props.summary?.total_debit)}` },
    { title: 'Total Credit', value: `$${money(props.summary?.total_credit)}` },
]);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Customer Ledger Report" />
        <ReportToolbar :title="$t('Customer Ledger')" subtitle="Select a customer for full ledger detail" category="Customer Reports" route-name="reports.customers.ledger" :filters="filters" :options="options" show-customer show-transaction-type :transaction-types="transactionTypes" />
        <ReportSummaryCards :cards="cards" subtitle="Ledger summary for selected filters" />
        <div class="theme-table-card overflow-x-auto">
            <table class="w-full text-left min-w-[1000px]">
                <thead>
                    <tr class="theme-table-header">
                        <th class="theme-table-header-cell">{{ $t('Date') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Customer') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Voucher Type') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Voucher No') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Debit') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Credit') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Balance') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="(r, i) in rows.data" :key="i" class="theme-table-row">
                        <td class="px-6 py-3 text-sm">{{ formatDate(r.date) }}</td>
                        <td class="px-6 py-3 font-medium text-sm">{{ r.customer }}</td>
                        <td class="px-6 py-3 text-xs font-bold uppercase text-slate-500">{{ r.voucher_type }}</td>
                        <td class="px-6 py-3 text-sm text-indigo-600">{{ r.voucher_number || '—' }}</td>
                        <td class="px-6 py-3 text-right">${{ money(r.debit) }}</td>
                        <td class="px-6 py-3 text-right">${{ money(r.credit) }}</td>
                        <td class="px-6 py-3 text-right font-black">${{ money(r.balance) }}</td>
                    </tr>
                    <tr v-if="!rows.data?.length"><td colspan="7" class="px-6 py-12 text-center text-slate-400">{{ $t('No ledger entries. Select a customer or date range.') }}</td></tr>
                </tbody>
            </table>
            <div class="theme-table-footer flex justify-between print:hidden">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">{{ rows.total || 0 }} entries</div>
                <div class="flex gap-1.5">
                    <template v-for="(link, k) in rows.links || []" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border" :class="link.active ? 'theme-pagination-active' : 'theme-pagination-inactive'" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

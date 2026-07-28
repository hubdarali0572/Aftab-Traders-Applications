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
    { title: 'Payments', value: num(props.summary?.payment_count) },
    { title: 'Total Received', value: `$${money(props.summary?.total_payments)}`, tone: 'text-emerald-600' },
]);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Payment History Report" />
        <ReportToolbar title="Payment History Report" route-name="reports.customers.payment-history" :filters="filters" :options="options" show-customer show-payment-method />
        <ReportSummaryCards :cards="cards" />
        <div class="theme-table-card overflow-x-auto">
            <table class="w-full text-left min-w-[1000px]">
                <thead>
                    <tr class="theme-table-header">
                        <th class="theme-table-header-cell">{{ $t('Payment Date') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Customer') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Invoice') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Payment Method') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Reference') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Amount') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Received By') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="(r, i) in rows.data" :key="i" class="theme-table-row">
                        <td class="px-6 py-3 text-sm">{{ formatDate(r.payment_date) }}</td>
                        <td class="px-6 py-3 font-bold">{{ r.customer }}</td>
                        <td class="px-6 py-3 text-indigo-600 text-sm">{{ r.invoice }}</td>
                        <td class="px-6 py-3 text-sm capitalize">{{ r.payment_method }}</td>
                        <td class="px-6 py-3 text-sm">{{ r.reference_number || '—' }}</td>
                        <td class="px-6 py-3 text-right font-black text-emerald-600">${{ money(r.amount) }}</td>
                        <td class="px-6 py-3 text-sm">{{ r.received_by || '—' }}</td>
                    </tr>
                    <tr v-if="!rows.data?.length"><td colspan="7" class="px-6 py-12 text-center text-slate-400">{{ $t('No payments found.') }}</td></tr>
                </tbody>
            </table>
            <div class="theme-table-footer flex justify-between print:hidden">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">{{ rows.total || 0 }} payments</div>
                <div class="flex gap-1.5">
                    <template v-for="(link, k) in rows.links || []" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border" :class="link.active ? 'theme-pagination-active' : 'theme-pagination-inactive'" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

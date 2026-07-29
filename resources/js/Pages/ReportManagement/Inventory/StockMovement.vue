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
    transactionTypes: Object,
});
const num = (v) => Number(v || 0).toLocaleString(undefined, { maximumFractionDigits: 2 });
const formatDate = (v) => (!v ? '—' : new Date(v).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }));
const cards = computed(() => [
    { title: 'Movements', value: num(props.summary?.movements) },
    { title: 'Stock In', value: num(props.summary?.stock_in), tone: 'text-emerald-600' },
    { title: 'Stock Out', value: num(props.summary?.stock_out), tone: 'text-rose-600' },
]);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Stock Movement Report" />
        <ReportToolbar title="Stock Movement Report" category="Inventory Reports" route-name="reports.inventory.stock-movement" :filters="filters" :options="options" show-product show-user show-transaction-type :transaction-types="transactionTypes" />
        <ReportSummaryCards :cards="cards" subtitle="Stock in/out movement summary" />
        <div class="theme-table-card overflow-x-auto">
            <table class="w-full text-left min-w-[1100px]">
                <thead>
                    <tr class="theme-table-header">
                        <th class="theme-table-header-cell">{{ $t('Date') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Product') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Warehouse') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Type') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Reference') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Stock In') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Stock Out') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Running Balance') }}</th>
                        <th class="theme-table-header-cell">{{ $t('User') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="(r, i) in rows.data" :key="i" class="theme-table-row">
                        <td class="px-6 py-3 text-sm">{{ formatDate(r.date) }}</td>
                        <td class="px-6 py-3 font-bold text-sm">{{ r.product }} <span class="text-xs text-slate-400">{{ r.sku }}</span></td>
                        <td class="px-6 py-3 text-sm">{{ r.warehouse }}</td>
                        <td class="px-6 py-3 text-xs font-bold uppercase text-slate-500">{{ r.transaction_type }}</td>
                        <td class="px-6 py-3 text-sm text-indigo-600">{{ r.reference_no || '—' }}</td>
                        <td class="px-6 py-3 text-right text-emerald-600 font-bold">{{ num(r.stock_in) }}</td>
                        <td class="px-6 py-3 text-right text-rose-600 font-bold">{{ num(r.stock_out) }}</td>
                        <td class="px-6 py-3 text-right font-black">{{ num(r.running_balance) }}</td>
                        <td class="px-6 py-3 text-sm">{{ r.user || '—' }}</td>
                    </tr>
                    <tr v-if="!rows.data?.length"><td colspan="9" class="px-6 py-12 text-center text-slate-400">{{ $t('No movements.') }}</td></tr>
                </tbody>
            </table>
            <div class="theme-table-footer flex justify-between print:hidden">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">Showing {{ rows.from || 0 }}–{{ rows.to || 0 }} of {{ rows.total || 0 }}</div>
                <div class="flex gap-1.5">
                    <template v-for="(link, k) in rows.links || []" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border" :class="link.active ? 'theme-pagination-active' : 'theme-pagination-inactive'" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

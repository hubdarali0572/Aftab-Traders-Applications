<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReportToolbar from '@/Components/Reports/ReportToolbar.vue';
import ReportSummaryCards from '@/Components/Reports/ReportSummaryCards.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ rows: Object, summary: Object, filters: Object, options: Object });
const money = (v) => Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const num = (v) => Number(v || 0).toLocaleString(undefined, { maximumFractionDigits: 2 });
const cards = computed(() => [
    { title: 'Products', value: num(props.summary?.total_products) },
    { title: 'Stock Qty', value: num(props.summary?.total_quantity) },
    { title: 'Stock Value', value: `$${money(props.summary?.total_stock_value)}` },
]);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Current Stock Report" />
        <ReportToolbar title="Current Stock Report" subtitle="Calculated from stock balances and movement history" route-name="reports.inventory.current-stock" :filters="filters" :options="options" :show-date="false" show-product show-category show-brand />
        <ReportSummaryCards :cards="cards" />
        <div class="theme-table-card overflow-x-auto">
            <table class="w-full text-left min-w-[1300px]">
                <thead>
                    <tr class="theme-table-header">
                        <th class="theme-table-header-cell">{{ $t('Warehouse') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Product') }}</th>
                        <th class="theme-table-header-cell">{{ $t('SKU') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Category') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Unit') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Opening') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Purchased') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Sold') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('PR') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('SR') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('In') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Out') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Adj') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Available') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Stock Value') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="(r, i) in rows.data" :key="i" class="theme-table-row">
                        <td class="px-4 py-3 text-sm">{{ r.warehouse }}</td>
                        <td class="px-4 py-3 font-bold text-sm">{{ r.product }}</td>
                        <td class="px-4 py-3 text-xs text-slate-500">{{ r.sku || '—' }}</td>
                        <td class="px-4 py-3 text-sm">{{ r.category }}</td>
                        <td class="px-4 py-3 text-sm">{{ r.unit }}</td>
                        <td class="px-4 py-3 text-right text-sm">{{ num(r.opening_stock) }}</td>
                        <td class="px-4 py-3 text-right text-sm">{{ num(r.purchased) }}</td>
                        <td class="px-4 py-3 text-right text-sm">{{ num(r.sold) }}</td>
                        <td class="px-4 py-3 text-right text-sm">{{ num(r.purchase_returns) }}</td>
                        <td class="px-4 py-3 text-right text-sm">{{ num(r.sales_returns) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-emerald-600">{{ num(r.stock_in) }}</td>
                        <td class="px-4 py-3 text-right text-sm text-rose-600">{{ num(r.stock_out) }}</td>
                        <td class="px-4 py-3 text-right text-sm">{{ num(r.adjustments) }}</td>
                        <td class="px-4 py-3 text-right font-black text-sm">{{ num(r.current_available) }}</td>
                        <td class="px-4 py-3 text-right font-bold text-sm">${{ money(r.stock_value) }}</td>
                    </tr>
                    <tr v-if="!rows.data?.length"><td colspan="15" class="px-6 py-12 text-center text-slate-400">{{ $t('No stock records.') }}</td></tr>
                </tbody>
            </table>
            <div class="theme-table-footer flex justify-between print:hidden">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">{{ rows.total || 0 }} rows</div>
                <div class="flex gap-1.5">
                    <template v-for="(link, k) in rows.links || []" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border" :class="link.active ? 'theme-pagination-active' : 'theme-pagination-inactive'" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

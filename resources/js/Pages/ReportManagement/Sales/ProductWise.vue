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
    { title: 'Products', value: num(props.summary?.products) },
    { title: 'Qty Sold', value: num(props.summary?.quantity_sold) },
    { title: 'Sales Amount', value: `$${money(props.summary?.sales_amount)}` },
    { title: 'Returns', value: num(props.summary?.sales_returns) },
    { title: 'Net Qty', value: num(props.summary?.net_quantity) },
]);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Product-wise Sales Report" />
        <ReportToolbar title="Product-wise Sales Report" category="Sales Reports" route-name="reports.sales.product-wise" :filters="filters" :options="options" show-product show-category show-brand />
        <ReportSummaryCards :cards="cards" subtitle="Product performance summary" />
        <div class="theme-table-card overflow-x-auto">
            <table class="w-full text-left min-w-[1000px]">
                <thead>
                    <tr class="theme-table-header">
                        <th class="theme-table-header-cell">{{ $t('Product') }}</th>
                        <th class="theme-table-header-cell">{{ $t('SKU') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Category') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Brand') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Qty Sold') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Sales Amount') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Avg Price') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Returns') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Net Qty') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="r in rows.data" :key="r.product_id" class="theme-table-row">
                        <td class="px-6 py-3 font-bold">{{ r.product }}</td>
                        <td class="px-6 py-3 text-sm text-slate-500">{{ r.sku || '—' }}</td>
                        <td class="px-6 py-3 text-sm">{{ r.category }}</td>
                        <td class="px-6 py-3 text-sm">{{ r.brand }}</td>
                        <td class="px-6 py-3 text-right font-bold">{{ num(r.quantity_sold) }}</td>
                        <td class="px-6 py-3 text-right">${{ money(r.sales_amount) }}</td>
                        <td class="px-6 py-3 text-right">${{ money(r.avg_selling_price) }}</td>
                        <td class="px-6 py-3 text-right text-rose-600">{{ num(r.sales_returns) }}</td>
                        <td class="px-6 py-3 text-right font-bold text-emerald-600">{{ num(r.net_quantity_sold) }}</td>
                    </tr>
                    <tr v-if="!rows.data?.length"><td colspan="9" class="px-6 py-12 text-center text-slate-400">{{ $t('No data.') }}</td></tr>
                </tbody>
            </table>
            <div class="theme-table-footer flex justify-between print:hidden">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">{{ rows.total || 0 }} products</div>
                <div class="flex gap-1.5">
                    <template v-for="(link, k) in rows.links || []" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border" :class="link.active ? 'theme-pagination-active' : 'theme-pagination-inactive'" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

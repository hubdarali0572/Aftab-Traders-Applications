<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReportToolbar from '@/Components/Reports/ReportToolbar.vue';
import ReportSummaryCards from '@/Components/Reports/ReportSummaryCards.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ rows: Object, summary: Object, filters: Object, options: Object });
const num = (v) => Number(v || 0).toLocaleString(undefined, { maximumFractionDigits: 2 });
const cards = computed(() => [
    { title: 'Low Stock Items', value: num(props.summary?.low_stock_items), tone: 'text-amber-600' },
    { title: 'Out of Stock', value: num(props.summary?.out_of_stock), tone: 'text-rose-600' },
    { title: 'Total Shortage', value: num(props.summary?.total_shortage) },
]);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Low Stock Report" />
        <ReportToolbar title="Low Stock Report" subtitle="Current stock ≤ minimum stock level" route-name="reports.inventory.low-stock" :filters="filters" :options="options" :show-date="false" />
        <ReportSummaryCards :cards="cards" />
        <div class="theme-table-card overflow-x-auto">
            <table class="w-full text-left min-w-[900px]">
                <thead>
                    <tr class="theme-table-header">
                        <th class="theme-table-header-cell">Product</th>
                        <th class="theme-table-header-cell">SKU</th>
                        <th class="theme-table-header-cell">Warehouse</th>
                        <th class="theme-table-header-cell text-right">Current Qty</th>
                        <th class="theme-table-header-cell text-right">Minimum Qty</th>
                        <th class="theme-table-header-cell text-right">Difference</th>
                        <th class="theme-table-header-cell">Reorder Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="(r, i) in rows.data" :key="i" class="theme-table-row bg-rose-50/40 dark:bg-rose-500/5">
                        <td class="px-6 py-3 font-bold text-rose-700 dark:text-rose-400">{{ r.product }}</td>
                        <td class="px-6 py-3 text-sm">{{ r.sku || '—' }}</td>
                        <td class="px-6 py-3 text-sm">{{ r.warehouse }}</td>
                        <td class="px-6 py-3 text-right font-black text-rose-700">{{ num(r.current_quantity) }}</td>
                        <td class="px-6 py-3 text-right">{{ num(r.minimum_quantity) }}</td>
                        <td class="px-6 py-3 text-right font-bold">{{ num(r.difference) }}</td>
                        <td class="px-6 py-3"><span class="text-xs font-bold uppercase text-rose-600">{{ r.reorder_status }}</span></td>
                    </tr>
                    <tr v-if="!rows.data?.length"><td colspan="7" class="px-6 py-12 text-center text-slate-400">No low stock items.</td></tr>
                </tbody>
            </table>
            <div class="theme-table-footer flex justify-between print:hidden">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">{{ rows.total || 0 }} items</div>
                <div class="flex gap-1.5">
                    <template v-for="(link, k) in rows.links || []" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border" :class="link.active ? 'theme-pagination-active' : 'theme-pagination-inactive'" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

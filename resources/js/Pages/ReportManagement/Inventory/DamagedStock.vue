<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReportToolbar from '@/Components/Reports/ReportToolbar.vue';
import ReportSummaryCards from '@/Components/Reports/ReportSummaryCards.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ rows: Object, summary: Object, filters: Object, options: Object });
const money = (v) => Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const num = (v) => Number(v || 0).toLocaleString(undefined, { maximumFractionDigits: 2 });
const formatDate = (v) => (!v ? '—' : new Date(v).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }));
const cards = computed(() => [
    { title: 'Records', value: num(props.summary?.records) },
    { title: 'Damaged Qty', value: num(props.summary?.total_damaged_quantity), tone: 'text-rose-600' },
    { title: 'Damage Value', value: `$${money(props.summary?.total_damage_value)}`, tone: 'text-rose-600' },
]);
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Damaged Stock Report" />
        <ReportToolbar title="Damaged Stock Report" category="Inventory Reports" route-name="reports.inventory.damaged-stock" :filters="filters" :options="options" show-product />
        <ReportSummaryCards :cards="cards" subtitle="Damage quantity and value" />
        <div class="theme-table-card overflow-x-auto">
            <table class="w-full text-left min-w-[1000px]">
                <thead>
                    <tr class="theme-table-header">
                        <th class="theme-table-header-cell">{{ $t('Product') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Warehouse') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Damage Date') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Quantity') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Value') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Reason') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Recorded By') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="(r, i) in rows.data" :key="i" class="theme-table-row">
                        <td class="px-6 py-3 font-bold">{{ r.product }} <span class="text-xs text-slate-400">{{ r.sku }}</span></td>
                        <td class="px-6 py-3 text-sm">{{ r.warehouse }}</td>
                        <td class="px-6 py-3 text-sm">{{ formatDate(r.damage_date) }}</td>
                        <td class="px-6 py-3 text-right font-bold text-rose-600">{{ num(r.quantity) }}</td>
                        <td class="px-6 py-3 text-right">${{ money(r.total_cost) }}</td>
                        <td class="px-6 py-3 text-sm">{{ r.reason || '—' }}</td>
                        <td class="px-6 py-3 text-sm">{{ r.recorded_by || '—' }}</td>
                    </tr>
                    <tr v-if="!rows.data?.length"><td colspan="7" class="px-6 py-12 text-center text-slate-400">{{ $t('No damaged stock.') }}</td></tr>
                </tbody>
            </table>
            <div class="theme-table-footer flex justify-between print:hidden">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">{{ rows.total || 0 }} records</div>
                <div class="flex gap-1.5">
                    <template v-for="(link, k) in rows.links || []" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border" :class="link.active ? 'theme-pagination-active' : 'theme-pagination-inactive'" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReportToolbar from '@/Components/Reports/ReportToolbar.vue';
import ReportSummaryCards from '@/Components/Reports/ReportSummaryCards.vue';
import ReportBarChart from '@/Components/Reports/ReportBarChart.vue';
import ReportHBarChart from '@/Components/Reports/ReportHBarChart.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ rows: Object, summary: Object, filters: Object, options: Object });
const money = (v) => Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const moneyShort = (v) => {
    const n = Number(v || 0);
    if (n >= 1000000) return `$${(n / 1000000).toFixed(1)}M`;
    if (n >= 1000) return `$${(n / 1000).toFixed(1)}K`;
    return `$${n.toFixed(0)}`;
};
const formatDate = (v) => (!v ? '—' : new Date(v).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' }));
const cards = computed(() => [
    { title: 'Total Expenses', value: `$${money(props.summary?.total_expenses)}`, tone: 'text-rose-600' },
    { title: 'Records', value: Number(props.summary?.expense_count || 0).toLocaleString() },
]);

const trendLabels = computed(() => (props.summary?.monthly_trend || []).map((m) => m.label?.replace(/^\w+\s/, '') || m.month));
const trendValues = computed(() => (props.summary?.monthly_trend || []).map((m) => Number(m.total || 0)));
const categoryItems = computed(() => props.summary?.by_category || []);
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Expense Report')" />
        <ReportToolbar :title="$t('Expense Report')" route-name="reports.financial.expenses" :filters="filters" :options="options" show-payment-method />
        <ReportSummaryCards :cards="cards" />

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-6 items-start">
            <div class="theme-form-card overflow-hidden self-start">
                <div class="theme-form-section-header">
                    <div>
                        <h3 class="theme-form-section-title">{{ $t('Monthly Expense Trend') }}</h3>
                        <p class="mt-1 text-xs text-slate-400">{{ $t('Fixed-height performance chart') }}</p>
                    </div>
                </div>
                <div class="p-5">
                    <ReportBarChart
                        :labels="trendLabels"
                        :values="trendValues"
                        color="#e11d48"
                        :height="260"
                        :format-value="moneyShort"
                        :show-legend="false"
                    />
                </div>
            </div>
            <div class="theme-form-card overflow-hidden self-start">
                <div class="theme-form-section-header">
                    <div>
                        <h3 class="theme-form-section-title">{{ $t('Expense by Category') }}</h3>
                        <p class="mt-1 text-xs text-slate-400">{{ $t('Share of spend by expense name') }}</p>
                    </div>
                </div>
                <div class="p-5">
                    <ReportHBarChart
                        :items="categoryItems"
                        color="#4f46e5"
                        :height="260"
                    />
                </div>
            </div>
        </div>

        <div class="theme-table-card overflow-x-auto">
            <table class="w-full text-left min-w-[1100px]">
                <thead>
                    <tr class="theme-table-header">
                        <th class="theme-table-header-cell">{{ $t('Date') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Expense Name') }}</th>
                        <th class="theme-table-header-cell text-right">{{ $t('Amount') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Paid To') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Payment Method') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Remarks') }}</th>
                        <th class="theme-table-header-cell">{{ $t('Recorded By') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <tr v-for="(r, i) in rows.data" :key="i" class="theme-table-row">
                        <td class="px-6 py-3 text-sm">{{ formatDate(r.expense_date) }}</td>
                        <td class="px-6 py-3 font-bold">{{ r.expense_name }}</td>
                        <td class="px-6 py-3 text-right font-black text-rose-600">${{ money(r.amount) }}</td>
                        <td class="px-6 py-3 text-sm">{{ r.paid_to || '—' }}</td>
                        <td class="px-6 py-3 text-sm capitalize">{{ r.payment_method }}</td>
                        <td class="px-6 py-3 text-sm max-w-xs truncate">{{ r.remarks || '—' }}</td>
                        <td class="px-6 py-3 text-sm">{{ r.recorded_by || '—' }}</td>
                    </tr>
                    <tr v-if="!rows.data?.length"><td colspan="7" class="px-6 py-12 text-center text-slate-400">{{ $t('No expenses.') }}</td></tr>
                </tbody>
            </table>
            <div class="theme-table-footer flex justify-between print:hidden">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">{{ rows.total || 0 }} expenses</div>
                <div class="flex gap-1.5">
                    <template v-for="(link, k) in rows.links || []" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border" :class="link.active ? 'theme-pagination-active' : 'theme-pagination-inactive'" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

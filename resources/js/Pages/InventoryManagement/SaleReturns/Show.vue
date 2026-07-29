<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

const props = defineProps({
    saleReturn: Object,
    summary: Object,
});

const money = (v) => `$${Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const formatDateTime = (d) => {
    if (!d) return '—';
    return new Date(d).toLocaleString(undefined, { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const primaryReason = computed(() => {
    const reasons = props.saleReturn.details?.map((d) => d.reason).filter(Boolean) ?? [];
    return reasons.length ? [...new Set(reasons)].join(', ') : (props.saleReturn.remarks || '—');
});

const returnSummary = computed(() => [
    { label: 'Total Returned Quantity', value: props.summary?.total_quantity ?? props.saleReturn.total_quantity, bold: true },
    { label: 'Total Return Amount', value: money(props.summary?.total_amount ?? props.saleReturn.total_amount), bold: true, highlight: true, tone: 'text-emerald-600' },
]);

const printPage = () => window.print();

onMounted(() => {
    if (new URLSearchParams(window.location.search).get('print') === '1') {
        setTimeout(() => window.print(), 400);
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${saleReturn.reference_no} — ${$t('Sales Return')}`" />

        <!-- Return header -->
        <div class="mb-6 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 border-b border-slate-200 dark:border-slate-700 pb-6">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.2em] text-violet-500 mb-2">{{ $t('Return Invoice / Credit Note') }}</p>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ saleReturn.reference_no }}</h1>
                <p class="text-sm text-slate-500 mt-2 dark:text-slate-400">{{ formatDateTime(saleReturn.created_at || saleReturn.return_date) }}</p>
            </div>
            <div class="flex flex-wrap gap-2 print:hidden">
                <button type="button" @click="printPage" class="theme-btn-primary px-6 py-2.5 text-sm font-bold bg-violet-600 hover:bg-violet-700">{{ $t('Print Return Invoice') }}</button>
                <Link :href="route('sales-history.index')" class="theme-form-back-link px-5 py-2.5 text-sm font-bold">{{ $t('Sales History') }}</Link>
                <Link :href="route('sale-returns.index')" class="theme-form-back-link px-5 py-2.5 text-sm font-bold">{{ $t('Back') }}</Link>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
            <div class="xl:col-span-2 space-y-6">
                <!-- Return Information -->
                <div class="theme-table-card overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/50">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ $t('Return Information') }}</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                        <div><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Return Reference No.') }}</p><p class="text-sm font-black text-violet-600">{{ saleReturn.reference_no }}</p></div>
                        <div><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Original Invoice No.') }}</p>
                            <Link v-if="saleReturn.sale_id" :href="route('sales.show', saleReturn.sale_id)" class="text-sm font-bold text-indigo-600 hover:underline">{{ saleReturn.sale?.invoice_no || '—' }}</Link>
                            <p v-else class="text-sm font-bold">{{ saleReturn.sale?.invoice_no || '—' }}</p>
                        </div>
                        <div><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Return Date & Time') }}</p><p class="text-sm font-bold">{{ formatDateTime(saleReturn.created_at || saleReturn.return_date) }}</p></div>
                        <div><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Customer Name') }}</p><p class="text-sm font-bold">{{ saleReturn.customer?.customer_name || $t('Walk-in Customer') }}</p></div>
                        <div><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Customer Mobile') }}</p><p class="text-sm font-bold">{{ saleReturn.customer?.phone || '—' }}</p></div>
                        <div><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Warehouse') }}</p><p class="text-sm font-bold">{{ saleReturn.warehouse?.name || '—' }}</p></div>
                        <div><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Processed By') }}</p><p class="text-sm font-bold">{{ saleReturn.user?.name || '—' }}</p></div>
                        <div><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Return Reason') }}</p><p class="text-sm text-slate-600 dark:text-slate-300">{{ primaryReason }}</p></div>
                        <div class="sm:col-span-2"><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Remarks') }}</p><p class="text-sm text-slate-600 italic">{{ saleReturn.remarks || '—' }}</p></div>
                    </div>
                </div>

                <!-- Returned Product Details -->
                <div class="theme-table-card overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ $t('Returned Product Details') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm min-w-[640px]">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell w-10">#</th>
                                    <th class="theme-table-header-cell">{{ $t('Product Name') }}</th>
                                    <th class="theme-table-header-cell">{{ $t('SKU') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Returned Quantity') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Unit Price') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Return Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="(detail, i) in saleReturn.details" :key="detail.id" class="theme-table-row">
                                    <td class="px-4 py-3 text-slate-400 font-bold">{{ i + 1 }}</td>
                                    <td class="px-4 py-3 font-bold text-slate-800 dark:text-slate-200">{{ detail.product?.name || '—' }}</td>
                                    <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ detail.product?.sku || '—' }}</td>
                                    <td class="px-4 py-3 text-right font-bold">{{ detail.quantity }}</td>
                                    <td class="px-4 py-3 text-right">{{ money(detail.unit_price) }}</td>
                                    <td class="px-4 py-3 text-right font-black text-emerald-600">{{ money(detail.line_total) }}</td>
                                </tr>
                                <tr v-if="!saleReturn.details?.length"><td colspan="6" class="px-6 py-10 text-center text-slate-400">{{ $t('No returned products.') }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="theme-table-card overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/50">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ $t('Additional Information') }}</h3>
                    </div>
                    <dl class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-[10px] font-bold uppercase text-slate-400">{{ $t('Warehouse (Stock Returned To)') }}</dt><dd class="font-bold mt-1">{{ saleReturn.warehouse?.name || '—' }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase text-slate-400">{{ $t('Processed By (User)') }}</dt><dd class="font-bold mt-1">{{ saleReturn.user?.name || '—' }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase text-slate-400">{{ $t('Customer') }}</dt><dd class="font-bold mt-1">{{ saleReturn.customer?.customer_name || $t('Walk-in') }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase text-slate-400">{{ $t('Customer Mobile') }}</dt><dd class="font-bold mt-1">{{ saleReturn.customer?.phone || '—' }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase text-slate-400">{{ $t('Return Created At') }}</dt><dd class="mt-1 text-slate-600">{{ formatDateTime(saleReturn.created_at) }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase text-slate-400">{{ $t('Last Updated') }}</dt><dd class="mt-1 text-slate-600">{{ formatDateTime(saleReturn.updated_at) }}</dd></div>
                    </dl>
                </div>
            </div>

            <!-- Sticky Return Summary -->
            <div class="xl:col-span-1">
                <div class="theme-table-card overflow-hidden xl:sticky xl:top-4 border-violet-100 dark:border-violet-900/50 shadow-lg shadow-violet-100/50 dark:shadow-none">
                    <div class="px-6 py-4 bg-gradient-to-r from-violet-600 to-violet-700 text-white">
                        <h3 class="text-xs font-black uppercase tracking-widest opacity-90">{{ $t('Return Summary') }}</h3>
                    </div>
                    <dl class="p-6 space-y-4">
                        <div v-for="line in returnSummary" :key="line.label" class="flex justify-between gap-4 py-3 border-b border-slate-100 dark:border-slate-700 last:border-0" :class="line.highlight ? 'bg-violet-50/50 dark:bg-violet-950/20 -mx-2 px-2 rounded-lg' : ''">
                            <dt class="text-xs font-bold uppercase text-slate-500">{{ $t(line.label) }}</dt>
                            <dd class="text-lg font-black" :class="line.tone">{{ line.value }}</dd>
                        </div>
                        <div class="pt-2 text-xs text-slate-500 leading-relaxed">
                            {{ $t('Returned stock was added back to') }} <strong class="text-slate-700 dark:text-slate-300">{{ saleReturn.warehouse?.name || '—' }}</strong>.
                        </div>
                    </dl>
                    <div class="px-6 pb-6 print:hidden">
                        <Link :href="route('sale-returns.edit', saleReturn.id)" class="block w-full text-center text-xs font-bold text-violet-600 hover:underline">{{ $t('Edit Return') }}</Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@media print {
    :deep(.print\:hidden) { display: none !important; }
}
</style>

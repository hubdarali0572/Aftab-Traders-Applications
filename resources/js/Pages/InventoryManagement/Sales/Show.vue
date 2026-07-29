<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

const props = defineProps({
    sale: Object,
    summary: Object,
});

const money = (v) => `$${Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const formatDate = (d) => {
    if (!d) return '—';
    return new Date(d).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatDateTime = (d) => {
    if (!d) return '—';
    return new Date(d).toLocaleString(undefined, { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const formatMethod = (m) => m?.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) ?? '—';

const statusBadge = (status) => ({
    draft: 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
    completed: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-500/15 dark:text-emerald-300',
    cancelled: 'bg-rose-100 text-rose-800 dark:bg-rose-500/15 dark:text-rose-300',
}[status] ?? 'bg-slate-100 text-slate-600');

const paymentStatus = computed(() => {
    const due = parseFloat(props.sale.due_amount) || 0;
    const paid = parseFloat(props.sale.paid_amount) || 0;
    const grand = parseFloat(props.sale.grand_total) || 0;
    if (grand <= 0) return { label: 'No Amount', badge: 'bg-slate-100 text-slate-600' };
    if (due <= 0) return { label: 'Paid', badge: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' };
    if (paid > 0) return { label: 'Partial', badge: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300' };
    return { label: 'Unpaid', badge: 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300' };
});

const invoiceSummary = computed(() => [
    { label: 'Total Quantity', value: props.summary?.total_quantity ?? 0, bold: false },
    { label: 'Subtotal', value: money(props.sale.subtotal), bold: false },
    { label: 'Discount', value: money(props.sale.discount), tone: 'text-amber-600' },
    { label: 'Tax', value: money(props.sale.tax), tone: 'text-sky-600' },
    { label: 'Grand Total', value: money(props.sale.grand_total), bold: true, highlight: true },
    { label: 'Paid Amount', value: money(props.sale.paid_amount), tone: 'text-emerald-600', bold: true },
    { label: 'Remaining Amount', value: money(props.sale.due_amount), tone: parseFloat(props.sale.due_amount) > 0 ? 'text-rose-600' : 'text-emerald-600', bold: true },
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
        <Head :title="`${sale.invoice_no} — ${$t('Sale Invoice')}`" />

        <!-- Invoice header -->
        <div class="mb-6 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 border-b border-slate-200 dark:border-slate-700 pb-6">
            <div>
                <p class="text-[11px] font-black uppercase tracking-[0.2em] text-indigo-500 mb-2">{{ $t('Tax Invoice / Sale') }}</p>
                <h1 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">{{ sale.invoice_no }}</h1>
                <p class="text-sm text-slate-500 mt-2 dark:text-slate-400">{{ formatDateTime(sale.created_at || sale.sale_date) }}</p>
            </div>
            <div class="flex flex-wrap gap-2 print:hidden">
                <button type="button" @click="printPage" class="theme-btn-primary px-6 py-2.5 text-sm font-bold">{{ $t('Print Invoice') }}</button>
                <Link :href="route('sales-history.index')" class="theme-form-back-link px-5 py-2.5 text-sm font-bold">{{ $t('Sales History') }}</Link>
                <Link :href="route('sales.index')" class="theme-form-back-link px-5 py-2.5 text-sm font-bold">{{ $t('Back') }}</Link>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
            <!-- Main content -->
            <div class="xl:col-span-2 space-y-6">
                <!-- Sale Information -->
                <div class="theme-table-card overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/50">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ $t('Sale Information') }}</h3>
                    </div>
                    <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                        <div><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Invoice Number') }}</p><p class="text-sm font-black text-indigo-600">{{ sale.invoice_no }}</p></div>
                        <div><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Sale Date & Time') }}</p><p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ formatDateTime(sale.created_at || sale.sale_date) }}</p></div>
                        <div><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Customer Name') }}</p><p class="text-sm font-bold">{{ sale.customer?.customer_name || $t('Walk-in Customer') }}</p></div>
                        <div><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Customer Mobile') }}</p><p class="text-sm font-bold">{{ sale.customer?.phone || '—' }}</p></div>
                        <div><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Warehouse') }}</p><p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ sale.warehouse?.name || '—' }}</p></div>
                        <div><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Sold By') }}</p><p class="text-sm font-bold">{{ sale.user?.name || '—' }}</p></div>
                        <div><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Payment Method') }}</p><p class="text-sm font-bold">{{ formatMethod(sale.payment_method) }}</p></div>
                        <div><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Payment Status') }}</p><span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-black uppercase" :class="paymentStatus.badge">{{ paymentStatus.label }}</span></div>
                        <div><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Sale Status') }}</p><span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-black uppercase" :class="statusBadge(sale.sale_status)">{{ sale.sale_status }}</span></div>
                        <div class="sm:col-span-2"><p class="text-[10px] font-bold uppercase text-slate-400 mb-1">{{ $t('Remarks') }}</p><p class="text-sm text-slate-600 dark:text-slate-300 italic">{{ sale.remarks || '—' }}</p></div>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="theme-table-card overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ $t('Product Details') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm min-w-[700px]">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell w-10">#</th>
                                    <th class="theme-table-header-cell">{{ $t('Product Name') }}</th>
                                    <th class="theme-table-header-cell">{{ $t('SKU') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Quantity Sold') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Unit Price') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Discount') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Tax') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Line Total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="(item, i) in sale.details" :key="item.id" class="theme-table-row">
                                    <td class="px-4 py-3 text-slate-400 font-bold">{{ i + 1 }}</td>
                                    <td class="px-4 py-3 font-bold text-slate-800 dark:text-slate-200">{{ item.product?.name || '—' }}</td>
                                    <td class="px-4 py-3 font-mono text-slate-500 text-xs">{{ item.product?.sku || '—' }}</td>
                                    <td class="px-4 py-3 text-right font-bold">{{ item.quantity }}</td>
                                    <td class="px-4 py-3 text-right">{{ money(item.unit_price) }}</td>
                                    <td class="px-4 py-3 text-right text-amber-600">{{ Number(item.discount) > 0 ? money(item.discount) : '—' }}</td>
                                    <td class="px-4 py-3 text-right text-sky-600">{{ Number(item.tax) > 0 ? money(item.tax) : '—' }}</td>
                                    <td class="px-4 py-3 text-right font-black text-indigo-600">{{ money(item.line_total) }}</td>
                                </tr>
                                <tr v-if="!sale.details?.length"><td colspan="8" class="px-6 py-10 text-center text-slate-400">{{ $t('No products on this invoice.') }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="theme-table-card overflow-hidden print:break-inside-avoid">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-800/50">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ $t('Additional Information') }}</h3>
                    </div>
                    <dl class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div><dt class="text-[10px] font-bold uppercase text-slate-400">{{ $t('Warehouse (Sold From)') }}</dt><dd class="font-bold mt-1">{{ sale.warehouse?.name || '—' }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase text-slate-400">{{ $t('Sold By (User)') }}</dt><dd class="font-bold mt-1">{{ sale.user?.name || '—' }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase text-slate-400">{{ $t('Customer') }}</dt><dd class="font-bold mt-1">{{ sale.customer?.customer_name || $t('Walk-in') }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase text-slate-400">{{ $t('Customer Mobile') }}</dt><dd class="font-bold mt-1">{{ sale.customer?.phone || '—' }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase text-slate-400">{{ $t('Created At') }}</dt><dd class="mt-1 text-slate-600">{{ formatDateTime(sale.created_at) }}</dd></div>
                        <div><dt class="text-[10px] font-bold uppercase text-slate-400">{{ $t('Last Updated') }}</dt><dd class="mt-1 text-slate-600">{{ formatDateTime(sale.updated_at) }}</dd></div>
                    </dl>
                </div>

                <!-- Linked returns -->
                <div v-if="sale.sale_returns?.length" class="theme-table-card overflow-hidden print:hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                        <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ $t('Linked Returns') }}</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead><tr class="theme-table-header">
                                <th class="theme-table-header-cell">{{ $t('Reference') }}</th>
                                <th class="theme-table-header-cell">{{ $t('Date') }}</th>
                                <th class="theme-table-header-cell text-right">{{ $t('Amount') }}</th>
                                <th class="theme-table-header-cell text-right">{{ $t('View') }}</th>
                            </tr></thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="ret in sale.sale_returns" :key="ret.id" class="theme-table-row">
                                    <td class="px-4 py-2 font-bold text-violet-600">{{ ret.reference_no }}</td>
                                    <td class="px-4 py-2">{{ formatDate(ret.return_date) }}</td>
                                    <td class="px-4 py-2 text-right font-bold">{{ money(ret.total_amount) }}</td>
                                    <td class="px-4 py-2 text-right"><Link :href="route('sale-returns.show', ret.id)" class="text-xs font-bold text-indigo-600">{{ $t('View') }}</Link></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sticky Invoice Summary -->
            <div class="xl:col-span-1">
                <div class="theme-table-card overflow-hidden xl:sticky xl:top-4 border-indigo-100 dark:border-indigo-900/50 shadow-lg shadow-indigo-100/50 dark:shadow-none">
                    <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white">
                        <h3 class="text-xs font-black uppercase tracking-widest opacity-90">{{ $t('Invoice Summary') }}</h3>
                    </div>
                    <dl class="p-6 space-y-3">
                        <div v-for="line in invoiceSummary" :key="line.label" class="flex justify-between gap-4 py-2 border-b border-slate-100 dark:border-slate-700 last:border-0" :class="line.highlight ? 'bg-indigo-50/50 dark:bg-indigo-950/20 -mx-2 px-2 rounded-lg' : ''">
                            <dt class="text-xs font-bold uppercase text-slate-500" :class="line.bold ? 'text-slate-700 dark:text-slate-300' : ''">{{ $t(line.label) }}</dt>
                            <dd class="text-sm font-black text-right" :class="[line.tone, line.bold ? 'text-base' : '']">{{ line.value }}</dd>
                        </div>
                    </dl>
                    <div class="px-6 pb-6 print:hidden space-y-2">
                        <Link v-if="sale.sale_status === 'completed'" :href="route('sale-returns.create', { sale_id: sale.id })" class="block w-full text-center theme-form-back-link py-2.5 text-sm font-bold">{{ $t('Create Return') }}</Link>
                        <Link :href="route('sales.edit', sale.id)" class="block w-full text-center text-xs font-bold text-indigo-600 hover:underline">{{ $t('Edit Sale') }}</Link>
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

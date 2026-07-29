<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    purchase: { type: Object, required: true },
    snapshot: { type: Object, required: true },
});

const money = (v) => (v == null || v === '' ? '—' : `$${parseFloat(v).toFixed(2)}`);
const num = (v) => (v == null || v === '' ? '—' : Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 }));

const formatTxn = (type) => type?.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) ?? '—';

const txnBadgeClass = (type) => ({
    purchase: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300',
    payment: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
    purchase_return: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
    adjustment: 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
    cancellation: 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
}[type] || 'bg-slate-100 text-slate-700');

const purchaseStatusClass = (status) => ({
    draft: 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
    received: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
    cancelled: 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
}[status] || '');

const paymentStatusClass = (status) => ({
    unpaid: 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
    partial: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
    paid: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
}[status] || '');

const financialStats = computed(() => [
    { title: 'Grand Total', value: money(props.snapshot.grandTotal), tone: 'text-indigo-600 dark:text-indigo-400', bg: 'bg-indigo-50 dark:bg-indigo-500/10' },
    { title: 'Returns', value: money(props.snapshot.returnsTotal), tone: 'text-amber-600 dark:text-amber-400', bg: 'bg-amber-50 dark:bg-amber-500/10' },
    { title: 'Net Payable', value: money(props.snapshot.netPayable), tone: 'text-slate-700 dark:text-slate-200', bg: 'bg-slate-50 dark:bg-slate-700/40' },
    { title: 'Amount Paid', value: money(props.snapshot.paidTotal), tone: 'text-emerald-600 dark:text-emerald-400', bg: 'bg-emerald-50 dark:bg-emerald-500/10' },
    { title: 'Amount Remaining', value: money(props.snapshot.dueTotal), tone: 'text-rose-600 dark:text-rose-400', bg: 'bg-rose-50 dark:bg-rose-500/10' },
]);

const chargeRows = computed(() => [
    { label: 'Subtotal', value: money(props.purchase.subtotal) },
    { label: 'Discount', value: money(props.purchase.discount) },
    { label: 'Tax', value: money(props.purchase.tax) },
    { label: 'Shipping Cost', value: money(props.purchase.shipping_cost) },
    { label: 'Other Charges', value: money(props.purchase.other_charges) },
]);
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${$t('Purchase History Detail')} · ${purchase.purchase_no}`" />

        <div class="mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-indigo-500 mb-1">{{ $t('Purchase Order Record') }}</p>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ purchase.purchase_no }}</h2>
                    <p class="text-sm text-slate-500 mt-1 font-medium">{{ purchase.purchase_date }} · {{ purchase.supplier_name || $t('No supplier') }}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                <Link :href="route('purchases.show', purchase.id)" class="theme-btn-primary inline-flex items-center px-5 py-2.5">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    {{ $t('View Purchase') }}
                </Link>
                <Link :href="route('purchase-history.index')" class="theme-form-back-link inline-flex items-center px-4 py-2.5">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    {{ $t('Back to Purchase History') }}
                </Link>
            </div>
        </div>

        <!-- Financial summary -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div v-for="stat in financialStats" :key="stat.title" class="theme-form-card p-5 text-center" :class="stat.bg">
                <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t(stat.title) }}</p>
                <p class="text-xl lg:text-2xl font-black mt-1 truncate" :class="stat.tone">{{ stat.value }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- General info -->
            <div class="theme-table-card">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-slate-50 to-white dark:from-slate-800/80 dark:to-slate-800">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">{{ $t('Purchase Information') }}</h3>
                </div>
                <dl class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Supplier') }}</dt>
                        <dd class="text-sm font-bold text-slate-800 dark:text-slate-100 mt-1">{{ purchase.supplier_name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Supplier Invoice') }}</dt>
                        <dd class="text-sm font-semibold text-slate-600 dark:text-slate-300 mt-1">{{ purchase.supplier_invoice_no || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Purchase Date') }}</dt>
                        <dd class="text-sm font-semibold text-slate-700 dark:text-slate-200 mt-1">{{ purchase.purchase_date || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Warehouse') }}</dt>
                        <dd class="text-sm font-bold text-slate-800 dark:text-slate-100 mt-1">{{ purchase.warehouse?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Purchase Status') }}</dt>
                        <dd class="mt-1">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-black uppercase" :class="purchaseStatusClass(purchase.purchase_status)">{{ purchase.purchase_status }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Payment Status') }}</dt>
                        <dd class="mt-1">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-[10px] font-black uppercase" :class="paymentStatusClass(purchase.payment_status)">{{ purchase.payment_status }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Created By') }}</dt>
                        <dd class="text-sm font-semibold text-slate-700 dark:text-slate-200 mt-1">{{ purchase.user?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Recorded At') }}</dt>
                        <dd class="text-sm font-semibold text-slate-700 dark:text-slate-200 mt-1">{{ purchase.created_at || '—' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Amount breakdown -->
            <div class="theme-table-card">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Amount Breakdown') }}</h3>
                </div>
                <div class="p-6 space-y-3">
                    <div v-for="row in chargeRows" :key="row.label" class="flex items-center justify-between text-sm py-2 border-b border-slate-50 dark:border-slate-700/50 last:border-0">
                        <span class="text-slate-500">{{ $t(row.label) }}</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">{{ row.value }}</span>
                    </div>
                    <div class="flex items-center justify-between pt-3 mt-2 border-t-2 border-indigo-100 dark:border-indigo-500/30">
                        <span class="text-sm font-black uppercase text-indigo-600 dark:text-indigo-400">{{ $t('Grand Total') }}</span>
                        <span class="text-xl font-black text-indigo-700 dark:text-indigo-300">{{ money(purchase.grand_total) }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-center">
                            <p class="text-[10px] font-bold uppercase text-emerald-500">{{ $t('Paid') }}</p>
                            <p class="text-lg font-black text-emerald-700 dark:text-emerald-300">{{ money(purchase.paid_amount) }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-rose-50 dark:bg-rose-500/10 text-center">
                            <p class="text-[10px] font-bold uppercase text-rose-500">{{ $t('Remaining') }}</p>
                            <p class="text-lg font-black text-rose-700 dark:text-rose-300">{{ money(purchase.due_amount) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction ledger -->
        <div class="theme-table-card mb-6">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Transaction History') }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $t('Complete financial audit trail for this purchase.') }}</p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell">{{ $t('Date') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Type') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Reference') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Debit') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Credit') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Balance') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Paid') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Remaining') }}</th>
                            <th class="theme-table-header-cell">{{ $t('By') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Remarks') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="t in purchase.transactions" :key="t.id" class="theme-table-row">
                            <td class="px-4 py-2.5 whitespace-nowrap text-slate-600 dark:text-slate-300">{{ t.transaction_date }}</td>
                            <td class="px-4 py-2.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase" :class="txnBadgeClass(t.transaction_type)">{{ formatTxn(t.transaction_type) }}</span>
                            </td>
                            <td class="px-4 py-2.5 font-bold text-indigo-600 dark:text-indigo-400">{{ t.reference_no || '—' }}</td>
                            <td class="px-4 py-2.5 text-right text-rose-600 dark:text-rose-400">{{ t.debit > 0 ? money(t.debit) : '—' }}</td>
                            <td class="px-4 py-2.5 text-right text-emerald-600 dark:text-emerald-400">{{ t.credit > 0 ? money(t.credit) : '—' }}</td>
                            <td class="px-4 py-2.5 text-right font-bold">{{ money(t.balance) }}</td>
                            <td class="px-4 py-2.5 text-right text-emerald-600">{{ money(t.paid_total) }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-rose-600">{{ money(t.due_total) }}</td>
                            <td class="px-4 py-2.5">{{ t.user?.name || '—' }}</td>
                            <td class="px-4 py-2.5 max-w-[180px] text-slate-500 whitespace-pre-wrap">{{ t.remarks || '—' }}</td>
                        </tr>
                        <tr v-if="!purchase.transactions?.length">
                            <td colspan="10" class="px-6 py-12 text-center text-slate-400">{{ $t('No transactions recorded yet.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Purchase items -->
        <div v-if="purchase.details?.length" class="theme-table-card mb-6">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Purchase Items') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell">{{ $t('Product') }}</th>
                            <th class="theme-table-header-cell">{{ $t('SKU') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Qty') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Free') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Unit Price') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Discount') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Tax') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Line Total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="d in purchase.details" :key="d.id" class="theme-table-row">
                            <td class="px-4 py-2.5 font-bold text-slate-800 dark:text-slate-100">{{ d.product?.name }}</td>
                            <td class="px-4 py-2.5 text-slate-500">{{ d.product?.sku || '—' }}</td>
                            <td class="px-4 py-2.5 text-right">{{ num(d.quantity) }}</td>
                            <td class="px-4 py-2.5 text-right">{{ num(d.free_quantity) }}</td>
                            <td class="px-4 py-2.5 text-right">{{ money(d.unit_price) }}</td>
                            <td class="px-4 py-2.5 text-right">{{ money(d.discount) }}</td>
                            <td class="px-4 py-2.5 text-right">{{ money(d.tax) }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-indigo-700 dark:text-indigo-300">{{ money(d.line_total) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Returns -->
        <div v-if="purchase.returns?.length" class="theme-table-card mb-6">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                    </div>
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Purchase Returns') }}</h3>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell">{{ $t('Ref #') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Return Date') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Total Qty') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Total Amount') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Items') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="r in purchase.returns" :key="r.id" class="theme-table-row">
                            <td class="px-4 py-2.5 font-bold text-amber-600 dark:text-amber-400">{{ r.reference_no }}</td>
                            <td class="px-4 py-2.5">{{ r.return_date }}</td>
                            <td class="px-4 py-2.5 text-right">{{ num(r.total_quantity) }}</td>
                            <td class="px-4 py-2.5 text-right font-bold text-amber-600">{{ money(r.total_amount) }}</td>
                            <td class="px-4 py-2.5 text-slate-500 text-xs">
                                <span v-for="(item, idx) in r.details" :key="item.id">
                                    {{ item.product?.name }} ({{ num(item.quantity) }})<span v-if="idx < r.details.length - 1">, </span>
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-right">
                                <Link :href="route('purchase-returns.show', r.id)" class="theme-table-action-btn" :title="$t('View')">
                                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Expenses -->
        <div v-if="purchase.expenses?.length" class="theme-table-card mb-6">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Purchase Expenses') }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell">{{ $t('Description') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="e in purchase.expenses" :key="e.id" class="theme-table-row">
                            <td class="px-4 py-2.5">{{ e.expense_type || e.remarks || '—' }}</td>
                            <td class="px-4 py-2.5 text-right font-bold">{{ money(e.amount) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Remarks -->
        <div class="theme-table-card">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Remarks') }}</h3>
            </div>
            <div class="p-6">
                <p v-if="purchase.remarks" class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap bg-slate-50 dark:bg-slate-800/50 p-5 rounded-xl border border-slate-100 dark:border-slate-700">{{ purchase.remarks }}</p>
                <p v-else class="text-sm text-slate-400 italic">{{ $t('No remarks recorded.') }}</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

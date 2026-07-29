<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ purchase: Object });
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Purchase Details')" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">{{ purchase.purchase_no }}</h2>
            <div class="flex gap-3">
                <Link :href="route('purchase-history.show', purchase.id)" class="theme-btn-primary px-6 py-2 rounded-full bg-slate-700 hover:bg-slate-800">{{ $t('History') }}</Link>
                <Link :href="route('purchases.edit', purchase.id)" class="theme-btn-primary px-6 py-2 rounded-full">{{ $t('Edit') }}</Link>
                <Link :href="route('purchases.index')" class="theme-form-back-link">{{ $t('Back') }}</Link>
            </div>
        </div>

        <div class="space-y-6">
            <div class="theme-form-card p-10">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Supplier') }}</dt><dd class="font-bold">{{ purchase.supplier_name || '—' }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Reference') }}</dt><dd class="font-bold text-slate-700">{{ purchase.supplier_invoice_no || '—' }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Purchase Date') }}</dt><dd class="font-bold text-slate-700">{{ purchase.purchase_date }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Warehouse') }}</dt><dd class="font-bold">{{ purchase.warehouse?.name }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Purchase Status') }}</dt><dd class="font-bold uppercase text-indigo-600">{{ purchase.purchase_status }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Payment Status') }}</dt><dd class="font-bold uppercase text-emerald-600">{{ purchase.payment_status }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Created By') }}</dt><dd class="font-bold text-slate-700">{{ purchase.user?.name || '—' }}</dd></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="theme-form-card p-8 text-center bg-indigo-50 dark:bg-indigo-900/10 border-indigo-100">
                    <dt class="text-xs font-black uppercase text-indigo-400 mb-2">{{ $t('Subtotal') }}</dt>
                    <dd class="text-3xl font-black text-indigo-700">${{ purchase.subtotal }}</dd>
                </div>
                <div class="theme-form-card p-8 text-center">
                    <dt class="text-xs font-black uppercase text-slate-400 mb-2">{{ $t('Grand Total') }}</dt>
                    <dd class="text-3xl font-black text-slate-700">${{ purchase.grand_total }}</dd>
                </div>
                <div class="theme-form-card p-8 text-center bg-emerald-50 dark:bg-emerald-900/10 border-emerald-100">
                    <dt class="text-xs font-black uppercase text-emerald-400 mb-2">{{ $t('Paid') }}</dt>
                    <dd class="text-3xl font-black text-emerald-700">${{ purchase.paid_amount }}</dd>
                </div>
                <div class="theme-form-card p-8 text-center bg-rose-50 dark:bg-rose-900/10 border-rose-100">
                    <dt class="text-xs font-black uppercase text-rose-400 mb-2">{{ $t('Due') }}</dt>
                    <dd class="text-3xl font-black text-rose-700">${{ purchase.due_amount }}</dd>
                </div>
            </div>

            <div class="theme-form-card p-8" v-if="purchase.details?.length">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Purchase Items') }}</h3>
                    <Link :href="route('purchases.edit', purchase.id)" class="text-xs font-bold text-indigo-600 hover:underline">{{ $t('Edit Items') }}</Link>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead><tr class="theme-table-header"><th class="theme-table-header-cell">{{ $t('Product') }}</th><th class="theme-table-header-cell text-right">{{ $t('Qty') }}</th><th class="theme-table-header-cell text-right">{{ $t('Free') }}</th><th class="theme-table-header-cell text-right">{{ $t('Unit Price') }}</th><th class="theme-table-header-cell text-right">{{ $t('Line Total') }}</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="d in purchase.details" :key="d.id" class="theme-table-row">
                                <td class="px-4 py-3 font-bold">{{ d.product?.name }}</td>
                                <td class="px-4 py-3 text-right">{{ d.quantity }}</td>
                                <td class="px-4 py-3 text-right">{{ d.free_quantity }}</td>
                                <td class="px-4 py-3 text-right">${{ d.unit_price }}</td>
                                <td class="px-4 py-3 text-right font-bold">${{ d.line_total }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="theme-form-card p-8" v-if="purchase.remarks">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-4">{{ $t('Remarks') }}</h3>
                <p class="text-sm text-slate-600 italic">"{{ purchase.remarks }}"</p>
            </div>

            <div class="theme-form-card p-8">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Purchase Returns') }}</h3>
                    <Link :href="route('purchase-returns.create', { purchase_id: purchase.id })" class="text-xs font-bold text-indigo-600 hover:underline">{{ $t('New Return') }}</Link>
                </div>
                <div v-if="purchase.returns?.length" class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead><tr class="theme-table-header"><th class="theme-table-header-cell">{{ $t('Ref #') }}</th><th class="theme-table-header-cell">{{ $t('Return Date') }}</th><th class="theme-table-header-cell text-right">{{ $t('Total Qty') }}</th><th class="theme-table-header-cell text-right">{{ $t('Total Amount') }}</th><th class="theme-table-header-cell text-right">{{ $t('Actions') }}</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="r in purchase.returns" :key="r.id" class="theme-table-row">
                                <td class="px-4 py-3 font-bold">{{ r.reference_no }}</td>
                                <td class="px-4 py-3">{{ r.return_date }}</td>
                                <td class="px-4 py-3 text-right">{{ r.total_quantity }}</td>
                                <td class="px-4 py-3 text-right font-bold">${{ r.total_amount }}</td>
                                <td class="px-4 py-3 text-right">
                                    <Link :href="route('purchase-returns.show', r.id)" class="text-xs font-bold text-indigo-600 hover:underline">{{ $t('View') }}</Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p v-else class="text-sm text-slate-500">{{ $t('No returns linked to this purchase yet.') }}</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

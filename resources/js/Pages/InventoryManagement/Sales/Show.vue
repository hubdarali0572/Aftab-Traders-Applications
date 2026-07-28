<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ sale: Object });

const statusClass = (status) => ({
    draft: 'text-slate-600',
    completed: 'text-emerald-600',
    cancelled: 'text-rose-600',
}[status] ?? 'text-slate-600');
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="sale.invoice_no" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ sale.invoice_no }}</h2>
                <p class="text-sm text-slate-500 font-medium uppercase tracking-widest" :class="statusClass(sale.sale_status)">{{ sale.sale_status }}</p>
            </div>
            <div class="flex gap-3">
                <Link :href="route('sales.edit', sale.id)" class="theme-btn-primary px-6 py-2 rounded-full">Edit</Link>
                <Link :href="route('sale-details.index', { sale_id: sale.id })" class="theme-btn-primary px-6 py-2 rounded-full bg-emerald-600 hover:bg-emerald-700">Line Items</Link>
                <Link :href="route('sales.index')" class="theme-form-back-link">Back</Link>
            </div>
        </div>

        <div class="space-y-6">
            <div class="theme-form-card p-10">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Customer</dt><dd class="font-bold">{{ sale.customer?.customer_name || 'Walk-in' }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Warehouse</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ sale.warehouse?.name }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Sale Date</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ sale.sale_date }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Payment Method</dt><dd class="font-bold uppercase text-indigo-600">{{ sale.payment_method }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Sale Type</dt><dd class="font-bold">{{ sale.sale_type?.replace(/_/g, ' ') }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Created By</dt><dd class="font-bold text-slate-700">{{ sale.user?.name || '—' }}</dd></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="theme-form-card p-8 text-center">
                    <dt class="text-xs font-black uppercase text-slate-400 mb-2">Subtotal</dt>
                    <dd class="text-2xl font-black text-slate-700">${{ sale.subtotal }}</dd>
                </div>
                <div class="theme-form-card p-8 text-center">
                    <dt class="text-xs font-black uppercase text-slate-400 mb-2">Discount</dt>
                    <dd class="text-2xl font-black text-rose-600">${{ sale.discount }}</dd>
                </div>
                <div class="theme-form-card p-8 text-center bg-indigo-50 dark:bg-indigo-900/10">
                    <dt class="text-xs font-black uppercase text-indigo-400 mb-2">Grand Total</dt>
                    <dd class="text-3xl font-black text-indigo-700">${{ sale.grand_total }}</dd>
                </div>
                <div class="theme-form-card p-8 text-center" :class="sale.due_amount > 0 ? 'bg-rose-50 dark:bg-rose-900/10' : 'bg-emerald-50 dark:bg-emerald-900/10'">
                    <dt class="text-xs font-black uppercase mb-2" :class="sale.due_amount > 0 ? 'text-rose-400' : 'text-emerald-400'">Due Amount</dt>
                    <dd class="text-3xl font-black" :class="sale.due_amount > 0 ? 'text-rose-700' : 'text-emerald-700'">${{ sale.due_amount }}</dd>
                    <p class="text-[10px] mt-1 text-slate-400">Paid: ${{ sale.paid_amount }}</p>
                </div>
            </div>

            <div class="theme-form-card" v-if="sale.details?.length">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-6">Line Items</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell">Product</th>
                                    <th class="theme-table-header-cell">Unit</th>
                                    <th class="theme-table-header-cell text-right">Qty</th>
                                    <th class="theme-table-header-cell text-right">Price</th>
                                    <th class="theme-table-header-cell text-right">Line Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="item in sale.details" :key="item.id" class="theme-table-row">
                                    <td class="px-4 py-3 font-bold">{{ item.product?.name }}</td>
                                    <td class="px-4 py-3 text-sm uppercase">{{ item.selling_unit }}</td>
                                    <td class="px-4 py-3 text-sm text-right">{{ item.quantity }}</td>
                                    <td class="px-4 py-3 text-sm text-right">${{ item.unit_price }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-bold">${{ item.line_total }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="theme-form-card" v-if="sale.remarks">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-4">Remarks</h3>
                    <div class="text-sm text-slate-600 bg-slate-50 p-6 rounded-xl border italic leading-relaxed">"{{ sale.remarks }}"</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

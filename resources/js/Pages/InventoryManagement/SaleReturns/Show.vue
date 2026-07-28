<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ saleReturn: Object });
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Sales Return Details" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">{{ saleReturn.reference_no }}</h2>
            <div class="flex gap-3">
                <Link :href="route('sale-returns.edit', saleReturn.id)" class="theme-btn-primary px-6 py-2 rounded-full">Edit</Link>
                <Link :href="route('sale-returns.index')" class="theme-form-back-link">Back</Link>
            </div>
        </div>

        <div class="space-y-6">
            <div class="theme-form-card p-10">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Invoice</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ saleReturn.sale?.invoice_no }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Customer</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ saleReturn.customer?.customer_name || 'Walk-in' }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Warehouse</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ saleReturn.warehouse?.name }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Return Date</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ saleReturn.return_date }}</dd></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="theme-form-card p-10 bg-indigo-50 dark:bg-indigo-900/10 text-center border-indigo-100 dark:border-indigo-800">
                    <dt class="text-xs font-black uppercase text-indigo-400 mb-2">Total Quantity</dt>
                    <dd class="text-4xl font-black text-indigo-700 dark:text-indigo-300">{{ saleReturn.total_quantity }}</dd>
                </div>
                <div class="theme-form-card p-10 bg-emerald-50 dark:bg-emerald-900/10 text-center border-emerald-100 dark:border-emerald-800">
                    <dt class="text-xs font-black uppercase text-emerald-400 mb-2">Total Amount</dt>
                    <dd class="text-4xl font-black text-emerald-700 dark:text-emerald-300">${{ saleReturn.total_amount }}</dd>
                </div>
            </div>

            <div class="theme-table-card">
                <div class="theme-form-section-header">
                    <h3 class="theme-form-section-title">Returned Items</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="theme-table-header">
                                <th class="theme-table-header-cell">Product</th>
                                <th class="theme-table-header-cell">Unit</th>
                                <th class="theme-table-header-cell text-right">Qty</th>
                                <th class="theme-table-header-cell text-right">Rate</th>
                                <th class="theme-table-header-cell text-right">Discount</th>
                                <th class="theme-table-header-cell text-right">Tax</th>
                                <th class="theme-table-header-cell text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr v-for="detail in saleReturn.details" :key="detail.id" class="theme-table-row">
                                <td class="px-6 py-3 font-bold text-slate-700 dark:text-slate-300">{{ detail.product?.name }}</td>
                                <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400">{{ detail.unit?.name }}</td>
                                <td class="px-6 py-3 text-right text-sm font-bold text-slate-700 dark:text-slate-300">{{ detail.quantity }}</td>
                                <td class="px-6 py-3 text-right text-sm text-slate-600 dark:text-slate-400">${{ detail.unit_price }}</td>
                                <td class="px-6 py-3 text-right text-sm text-slate-600 dark:text-slate-400">${{ detail.discount }}</td>
                                <td class="px-6 py-3 text-right text-sm text-slate-600 dark:text-slate-400">${{ detail.tax }}</td>
                                <td class="px-6 py-3 text-right text-sm font-black text-slate-700 dark:text-slate-300">${{ detail.line_total }}</td>
                            </tr>
                            <tr v-if="saleReturn.details.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-medium dark:text-slate-500">No return line items found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="theme-form-card" v-if="saleReturn.remarks">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4">Remarks</h3>
                    <div class="text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl border border-slate-100 dark:border-slate-700 italic leading-relaxed">
                        "{{ saleReturn.remarks }}"
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

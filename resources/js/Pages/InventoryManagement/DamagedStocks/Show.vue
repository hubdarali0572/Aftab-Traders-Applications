<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ stock: { type: Object, required: true } });

const fmt = (value, fallback = '—') =>
    value === null || value === undefined || value === '' ? fallback : value;

const fmtDate = (value) => {
    if (!value) return '—';
    return new Date(value).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const fmtNumber = (value, prefix = '') => {
    if (value === null || value === undefined || value === '') return '—';
    return `${prefix}${Number(value).toLocaleString(undefined, { minimumFractionDigits: 2 })}`;
};
</script>

<template>
    <Head :title="`Damaged Stock · ${stock.reference_no}`" />
    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-rose-50 dark:bg-slate-700 flex items-center justify-center shrink-0">
                    <svg class="w-8 h-8 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">{{ stock.reference_no }}</h2>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Warehouse: {{ fmt(stock.warehouse?.name) }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <Link :href="route('damaged-stocks.edit', stock.id)" class="theme-btn-primary px-6 py-3 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95">
                    Edit Record
                </Link>
                <Link :href="route('damaged-stocks.index')" class="theme-form-back-link">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="text-slate-900 dark:text-slate-100">Back to List</span>
                </Link>
            </div>
        </div>

        <div class="max-w-8xl mx-auto pb-24 space-y-6">
            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <div class="flex flex-wrap items-center gap-3 mb-8">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-widest" :class="stock.status ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300'">
                            <span class="h-1.5 w-1.5 rounded-full" :class="stock.status ? 'bg-emerald-500' : 'bg-slate-400'" />
                            {{ stock.status ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">General Information</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-6">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Warehouse</dt>
                            <dd class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100">{{ fmt(stock.warehouse?.name) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Damage Date</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmtDate(stock.damage_date) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Created By</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(stock.user?.name) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="theme-form-card p-10 bg-indigo-50 dark:bg-indigo-900/10 text-center border-indigo-100 dark:border-indigo-800">
                    <dt class="text-xs font-black uppercase text-indigo-400 mb-2">Total Quantity</dt>
                    <dd class="text-4xl font-black text-indigo-700 dark:text-indigo-300">{{ fmtNumber(stock.total_quantity) }}</dd>
                </div>
                <div class="theme-form-card p-10 bg-emerald-50 dark:bg-emerald-900/10 text-center border-emerald-100 dark:border-emerald-800">
                    <dt class="text-xs font-black uppercase text-emerald-400 mb-2">Total Amount</dt>
                    <dd class="text-4xl font-black text-emerald-700 dark:text-emerald-300">{{ fmtNumber(stock.total_amount, '$') }}</dd>
                </div>
            </div>

            <div class="theme-form-card" v-if="stock.remarks">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4">Remarks</h3>
                    <div class="text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700 italic">
                        {{ stock.remarks }}
                    </div>
                </div>
            </div>

            <div class="theme-form-card" v-if="stock.details?.length">
                <div class="p-8 md:p-10">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Damaged Items</h3>
                        <Link :href="route('damaged-stock-details.create')" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">+ Add Item</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell">Product</th>
                                    <th class="theme-table-header-cell">Reason</th>
                                    <th class="theme-table-header-cell text-right">Qty</th>
                                    <th class="theme-table-header-cell text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="item in stock.details" :key="item.id" class="theme-table-row">
                                    <td class="px-6 py-3 font-bold text-slate-800 dark:text-slate-200">{{ item.product?.name }}</td>
                                    <td class="px-6 py-3 text-sm text-rose-600">{{ item.damage_reason }}</td>
                                    <td class="px-6 py-3 text-right text-slate-600">{{ item.quantity }}</td>
                                    <td class="px-6 py-3 text-right font-bold text-indigo-600">${{ item.total_cost }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

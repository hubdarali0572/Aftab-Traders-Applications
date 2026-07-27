<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stock: { type: Object, required: true },
});

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
    <Head :title="`Opening Stock · ${stock.reference_no}`" />
    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-indigo-50 dark:bg-slate-700 flex items-center justify-center shrink-0">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.75c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">{{ stock.reference_no }}</h2>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Warehouse: {{ fmt(stock.warehouse?.name) }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <Link :href="route('opening-stocks.edit', stock.id)" class="theme-btn-primary px-6 py-3 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95">
                    Edit Opening Stock
                </Link>
                <Link :href="route('opening-stocks.index')" class="theme-form-back-link">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="text-slate-900 dark:text-slate-100">Back to List</span>
                </Link>
            </div>
        </div>

        <div class="max-w-8xl mx-auto pb-24 space-y-6">
            <!-- Status and Location -->
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
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Opening Date</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmtDate(stock.opening_date) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Created By</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(stock.user?.name) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Totals -->
            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">Stock Totals</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-6">
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl border border-slate-100 dark:border-slate-700">
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Total Quantity</dt>
                            <dd class="mt-2 text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ fmtNumber(stock.total_quantity) }}</dd>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl border border-slate-100 dark:border-slate-700">
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Total Amount Value</dt>
                            <dd class="mt-2 text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ fmtNumber(stock.total_amount, '$') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Remarks -->
            <div class="theme-form-card" v-if="stock.remarks">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4">Remarks</h3>
                    <div class="text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50 p-4 rounded-xl border border-slate-100 dark:border-slate-700 italic">
                        {{ stock.remarks }}
                    </div>
                </div>
            </div>

            <!-- Audit Info -->
            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">Activity & Record Info</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-6">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Created At</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmtDate(stock.created_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Last Updated</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmtDate(stock.updated_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Record ID</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">#{{ stock.id }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
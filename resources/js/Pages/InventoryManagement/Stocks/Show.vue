<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stock: { type: Object, required: true },
});

const fmt = (value, fallback = '—') =>
    value === null || value === undefined || value === '' ? fallback : value;

const fmtDate = (value) => {
    if (!value) return '—';
    return new Date(value).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const stockValue = (s) => (parseFloat(s.quantity || 0) * parseFloat(s.average_cost || 0)).toFixed(2);
</script>

<template>
    <Head :title="`Stock · ${stock.product?.name ?? 'Stock'}`" />
    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-slate-100 dark:bg-slate-700 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.75c0 .621.504 1.125 1.125 1.125z" /></svg>
                </div>
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">{{ fmt(stock.product?.name) }}</h2>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Warehouse: {{ fmt(stock.warehouse?.name) }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <Link :href="route('stocks.edit', stock.id)" class="theme-btn-primary px-6 py-3 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95">
                    {{ $t('Edit Thresholds') }}
                </Link>
                <Link :href="route('stocks.index')" class="theme-form-back-link">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="text-slate-900">{{ $t('Back to Stock List') }}</span>
                </Link>
            </div>
        </div>

        <div class="max-w-8xl mx-auto pb-24 space-y-6">
            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <div class="flex flex-wrap items-center gap-3 mb-8">
                        <span v-if="Number(stock.quantity) <= Number(stock.reorder_level)" class="px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-widest bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">{{ $t('Below Reorder Level') }}</span>
                    </div>

                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Location') }}</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-6">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Warehouse') }}</dt>
                            <dd class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100">{{ fmt(stock.warehouse?.name) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Product') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(stock.product?.name) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('SKU') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(stock.product?.sku) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Stock Quantities') }} <span class="text-xs font-normal normal-case">({{ $t('read-only') }})</span></h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-6">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Quantity') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(stock.quantity) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Minimum Stock') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(stock.minimum_stock) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Reorder Level') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(stock.reorder_level) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Costing') }}</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-6">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Average Cost') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(stock.average_cost) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Stock Value') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ stockValue(stock) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Last Updated') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmtDate(stock.updated_at) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

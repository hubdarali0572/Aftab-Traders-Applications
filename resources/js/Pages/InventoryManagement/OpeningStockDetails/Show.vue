<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    detail: { type: Object, required: true },
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
    <Head :title="`Detail · ${detail.product?.name || detail.id}`" />
    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-indigo-50 dark:bg-slate-700 flex items-center justify-center shrink-0">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.75c0 .621.504 1.125 1.125 1.125z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">{{ detail.product?.name }}</h2>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ $t('Parent Ref:') }} <span class="text-indigo-600 font-bold">{{ detail.opening_stock?.reference_no }}</span></p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <Link :href="route('opening-stock-details.edit', detail.id)" class="theme-btn-primary px-6 py-3 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95">
                    {{ $t('Edit Item') }}
                </Link>
                <Link :href="route('opening-stock-details.index')" class="theme-form-back-link">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="text-slate-900 dark:text-slate-100">{{ $t('Back to Items') }}</span>
                </Link>
            </div>
        </div>

        <div class="max-w-8xl mx-auto pb-24 space-y-6">
            <!-- Basic Info & Status -->
            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <div class="flex flex-wrap items-center gap-3 mb-8">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-widest" :class="detail.status ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300'">
                            <span class="h-1.5 w-1.5 rounded-full" :class="detail.status ? 'bg-emerald-500' : 'bg-slate-400'" />
                            {{ detail.status ? 'Active' : 'Inactive' }}
                        </span>
                    </div>

                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Product & Reference') }}</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-10 gap-y-6">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Parent Reference') }}</dt>
                            <dd class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100">{{ fmt(detail.opening_stock?.reference_no) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Product Name') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(detail.product?.name) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Batch Number') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(detail.batch_no) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Serial Number') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(detail.serial_no) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Quantities & Costing -->
            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Quantities & Financials') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-6 rounded-2xl border border-slate-100 dark:border-slate-700 text-center">
                            <dt class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">{{ $t('Quantity') }}</dt>
                            <dd class="text-3xl font-black text-slate-800 dark:text-slate-100">{{ fmtNumber(detail.quantity) }}</dd>
                        </div>
                        <div class="bg-emerald-50 dark:bg-emerald-900/10 p-6 rounded-2xl border border-emerald-100 dark:border-emerald-800 text-center">
                            <dt class="text-[10px] font-black uppercase tracking-widest text-emerald-600 mb-1">{{ $t('Unit Cost') }}</dt>
                            <dd class="text-3xl font-black text-emerald-700">{{ fmtNumber(detail.unit_cost, '$') }}</dd>
                        </div>
                        <div class="bg-indigo-50 dark:bg-indigo-900/10 p-6 rounded-2xl border border-indigo-100 dark:border-indigo-800 text-center">
                            <dt class="text-[10px] font-black uppercase tracking-widest text-indigo-600 mb-1">{{ $t('Total Cost Value') }}</dt>
                            <dd class="text-3xl font-black text-indigo-700">{{ fmtNumber(detail.total_cost, '$') }}</dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tracking & Record Info -->
            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Tracking & Records') }}</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-10 gap-y-6">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Expiry Date') }}</dt>
                            <dd class="mt-1 text-sm font-semibold" :class="detail.expiry_date ? 'text-rose-600 font-bold' : 'text-slate-600'">
                                {{ fmtDate(detail.expiry_date) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Date Added') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmtDate(detail.created_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Last Updated') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmtDate(detail.updated_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Line ID') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">#{{ detail.id }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Remarks -->
            <div class="theme-form-card" v-if="detail.remarks">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4">{{ $t('Item Remarks') }}</h3>
                    <div class="text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl border border-slate-100 dark:border-slate-700 italic">
                        "{{ detail.remarks }}"
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
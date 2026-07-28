<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    ledger: { type: Object, required: true },
});

const fmt = (value, fallback = '—') =>
    value === null || value === undefined || value === '' ? fallback : value;

const fmtDate = (value) => {
    if (!value) return '—';
    return new Date(value).toLocaleDateString(undefined, { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const formatType = (type) => {
    if (!type) return '—';
    return type.replace('_', ' ').toUpperCase();
};

const fmtNumber = (value) => {
    if (value === null || value === undefined) return '0.00';
    return parseFloat(value).toLocaleString(undefined, { minimumFractionDigits: 2 });
};
</script>

<template>
    <Head :title="`Ledger · ${ledger.reference_no || ledger.id}`" />
    <AuthenticatedLayout>
        <!-- Header -->
        <div class="max-w-8xl mx-auto mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center shrink-0">
                    <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">
                        {{ formatType(ledger.transaction_type) }}
                    </h2>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                        {{ $t('Reference:') }} <span class="text-indigo-600">{{ fmt(ledger.reference_no) }}</span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <Link :href="route('stock-ledgers.edit', ledger.id)" class="theme-btn-primary px-6 py-3 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95">
                    {{ $t('Edit Ledger') }}
                </Link>
                <Link :href="route('stock-ledgers.index')" class="theme-form-back-link">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="text-slate-900">{{ $t('Back to List') }}</span>
                </Link>
            </div>
        </div>

        <div class="max-w-8xl mx-auto pb-24 space-y-6">
            <!-- Main Info -->
            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <div class="flex flex-wrap items-center gap-3 mb-8">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-widest bg-indigo-50 text-indigo-700">
                            {{ ledger.reference_type }}
                        </span>
                        <span class="px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-widest" :class="ledger.status ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                            {{ ledger.status ? 'Active' : 'Voided' }}
                        </span>
                    </div>

                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-6">{{ $t('Transaction Details') }}</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-10 gap-y-6">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Transaction Date') }}</dt>
                            <dd class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100">{{ fmtDate(ledger.transaction_date) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Warehouse') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(ledger.warehouse?.name) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Product') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(ledger.product?.name) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Created By') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(ledger.user?.name) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Movement -->
            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-6">{{ $t('Stock Movement') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="bg-emerald-50 dark:bg-emerald-900/10 p-6 rounded-2xl border border-emerald-100 dark:border-emerald-800">
                            <dt class="text-xs font-bold uppercase tracking-widest text-emerald-600">{{ $t('Quantity In') }}</dt>
                            <dd class="mt-2 text-3xl font-black text-emerald-700">{{ fmtNumber(ledger.quantity_in) }}</dd>
                        </div>
                        <div class="bg-rose-50 dark:bg-rose-900/10 p-6 rounded-2xl border border-rose-100 dark:border-rose-800">
                            <dt class="text-xs font-bold uppercase tracking-widest text-rose-600">{{ $t('Quantity Out') }}</dt>
                            <dd class="mt-2 text-3xl font-black text-rose-700">{{ fmtNumber(ledger.quantity_out) }}</dd>
                        </div>
                        <div class="bg-slate-50 dark:bg-slate-700/50 p-6 rounded-2xl border border-slate-200 dark:border-slate-600">
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-500">{{ $t('Balance After Transaction') }}</dt>
                            <dd class="mt-2 text-3xl font-black text-slate-800 dark:text-slate-100">{{ fmtNumber(ledger.balance_quantity) }}</dd>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financials -->
            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-6">{{ $t('Cost Information') }}</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Unit Cost') }}</dt>
                            <dd class="mt-1 text-lg font-bold text-slate-800 dark:text-slate-100">${{ fmtNumber(ledger.unit_cost) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Total Transaction Cost') }}</dt>
                            <dd class="mt-1 text-lg font-bold text-slate-800 dark:text-slate-100">${{ fmtNumber(ledger.total_cost) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Remarks -->
            <div class="theme-form-card" v-if="ledger.remarks">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-4">{{ $t('Remarks') }}</h3>
                    <p class="text-slate-600 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-800 p-4 rounded-xl italic">
                        "{{ ledger.remarks }}"
                    </p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
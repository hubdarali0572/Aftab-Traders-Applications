<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ transfer: { type: Object, required: true } });

const money = (v) => (v == null ? '—' : `$${parseFloat(v).toFixed(2)}`);
const num = (v) => (v == null ? '—' : Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 }));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${$t('Transfer Detail')} · ${transfer.reference_no}`" />

        <div class="mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-indigo-500 mb-1">{{ $t('Stock Transfer Record') }}</p>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ transfer.reference_no }}</h2>
                    <p class="text-sm text-slate-500 mt-1 font-medium">{{ transfer.transfer_datetime }}</p>
                </div>
            </div>
            <Link :href="route('stock-history.index')" class="theme-form-back-link inline-flex items-center shrink-0">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                {{ $t('Back to Stock History') }}
            </Link>
        </div>

        <!-- Transfer flow -->
        <div class="theme-table-card mb-6 overflow-hidden">
            <div class="p-6 md:p-8 bg-gradient-to-r from-indigo-50 via-white to-sky-50 dark:from-indigo-950/30 dark:via-slate-800 dark:to-sky-950/20">
                <p class="text-[10px] font-black uppercase tracking-[0.14em] text-slate-400 mb-4 text-center">{{ $t('Transfer Flow') }}</p>
                <div class="flex flex-col md:flex-row items-center justify-center gap-4 md:gap-6">
                    <div class="flex-1 max-w-xs w-full text-center p-5 rounded-xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $t('From Warehouse') }}</p>
                        <p class="text-lg font-black text-slate-800 dark:text-slate-100 mt-1">{{ transfer.from_warehouse }}</p>
                        <p class="text-xs text-rose-500 font-bold mt-2">− {{ num(transfer.quantity) }} {{ $t('units') }}</p>
                    </div>
                    <div class="flex flex-col items-center shrink-0">
                        <div class="w-12 h-12 rounded-full bg-indigo-600 text-white flex items-center justify-center shadow-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </div>
                        <p class="text-xs font-black text-indigo-600 dark:text-indigo-400 mt-2">{{ num(transfer.quantity) }} {{ $t('Qty') }}</p>
                    </div>
                    <div class="flex-1 max-w-xs w-full text-center p-5 rounded-xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 shadow-sm">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $t('To Warehouse') }}</p>
                        <p class="text-lg font-black text-slate-800 dark:text-slate-100 mt-1">{{ transfer.to_warehouse }}</p>
                        <p class="text-xs text-emerald-600 font-bold mt-2">+ {{ num(transfer.quantity) }} {{ $t('units') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="theme-form-card p-5 text-center bg-indigo-50 dark:bg-indigo-500/10">
                <p class="text-[10px] font-black uppercase text-indigo-400">{{ $t('Quantity') }}</p>
                <p class="text-2xl font-black text-indigo-700 dark:text-indigo-300 mt-1">{{ num(transfer.quantity) }}</p>
            </div>
            <div class="theme-form-card p-5 text-center">
                <p class="text-[10px] font-black uppercase text-slate-400">{{ $t('Unit Cost') }}</p>
                <p class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">{{ money(transfer.unit_cost) }}</p>
            </div>
            <div class="theme-form-card p-5 text-center bg-emerald-50 dark:bg-emerald-500/10">
                <p class="text-[10px] font-black uppercase text-emerald-400">{{ $t('Total Value') }}</p>
                <p class="text-2xl font-black text-emerald-700 dark:text-emerald-300 mt-1">{{ money(transfer.total_value) }}</p>
            </div>
            <div class="theme-form-card p-5 text-center">
                <p class="text-[10px] font-black uppercase text-slate-400">{{ $t('Status') }}</p>
                <p class="mt-2">
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-black uppercase" :class="transfer.status ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-slate-200 text-slate-600'">
                        {{ transfer.status ? $t('Active') : $t('Inactive') }}
                    </span>
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Product & general info -->
            <div class="theme-table-card">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Product & Transfer Info') }}</h3>
                </div>
                <dl class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                    <div class="sm:col-span-2">
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Product') }}</dt>
                        <dd class="text-sm font-bold text-slate-800 dark:text-slate-100 mt-1">{{ transfer.product_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('SKU') }}</dt>
                        <dd class="text-sm font-semibold text-slate-600 dark:text-slate-300 mt-1">{{ transfer.sku || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Reference') }}</dt>
                        <dd class="text-sm font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ transfer.reference_no }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Transfer Date') }}</dt>
                        <dd class="text-sm font-semibold text-slate-700 dark:text-slate-200 mt-1">{{ transfer.transfer_date || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Transfer Time') }}</dt>
                        <dd class="text-sm font-semibold text-slate-700 dark:text-slate-200 mt-1">{{ transfer.transfer_time || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Created By') }}</dt>
                        <dd class="text-sm font-semibold text-slate-700 dark:text-slate-200 mt-1">{{ transfer.created_by || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Recorded At') }}</dt>
                        <dd class="text-sm font-semibold text-slate-700 dark:text-slate-200 mt-1">{{ transfer.created_at || '—' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Balance impact -->
            <div class="theme-table-card">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Balance Impact') }}</h3>
                </div>
                <div class="p-6 space-y-5">
                    <div class="p-4 rounded-xl bg-rose-50/60 dark:bg-rose-500/10 border border-rose-100 dark:border-rose-500/20">
                        <p class="text-[10px] font-black uppercase text-rose-500 mb-2">{{ transfer.from_warehouse }} ({{ $t('Source') }})</p>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">{{ $t('Bal. Before') }}</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200">{{ num(transfer.from_balance_before) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm mt-2">
                            <span class="text-slate-500">{{ $t('Bal. After') }}</span>
                            <span class="font-black text-rose-600 dark:text-rose-400">{{ num(transfer.from_balance_after) }}</span>
                        </div>
                    </div>
                    <div class="p-4 rounded-xl bg-emerald-50/60 dark:bg-emerald-500/10 border border-emerald-100 dark:border-emerald-500/20">
                        <p class="text-[10px] font-black uppercase text-emerald-600 mb-2">{{ transfer.to_warehouse }} ({{ $t('Destination') }})</p>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">{{ $t('Bal. Before') }}</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200">{{ num(transfer.to_balance_before) }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm mt-2">
                            <span class="text-slate-500">{{ $t('Bal. After') }}</span>
                            <span class="font-black text-emerald-600 dark:text-emerald-400">{{ num(transfer.to_balance_after) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Remarks -->
        <div class="theme-table-card">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Remarks') }}</h3>
            </div>
            <div class="p-6">
                <p v-if="transfer.remarks" class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap bg-slate-50 dark:bg-slate-800/50 p-5 rounded-xl border border-slate-100 dark:border-slate-700">{{ transfer.remarks }}</p>
                <p v-else class="text-sm text-slate-400 italic">{{ $t('No remarks recorded.') }}</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

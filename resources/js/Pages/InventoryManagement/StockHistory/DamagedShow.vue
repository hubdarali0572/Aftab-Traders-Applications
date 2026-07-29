<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ record: { type: Object, required: true } });

const money = (v) => (v == null ? '—' : `$${parseFloat(v).toFixed(2)}`);
const num = (v) => (v == null ? '—' : Number(v).toLocaleString(undefined, { maximumFractionDigits: 2 }));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${$t('Damage Detail')} · ${record.reference_no}`" />

        <div class="mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-rose-100 dark:bg-rose-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.14em] text-rose-500 mb-1">{{ $t('Damaged Stock Record') }}</p>
                    <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ record.reference_no }}</h2>
                    <p class="text-sm text-slate-500 mt-1 font-medium">{{ record.damage_datetime }}</p>
                </div>
            </div>
            <Link :href="route('stock-history.index')" class="theme-form-back-link inline-flex items-center shrink-0">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                {{ $t('Back to Stock History') }}
            </Link>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="theme-form-card p-5 text-center bg-rose-50 dark:bg-rose-500/10">
                <p class="text-[10px] font-black uppercase text-rose-400">{{ $t('Damaged Qty') }}</p>
                <p class="text-2xl font-black text-rose-700 dark:text-rose-300 mt-1">{{ num(record.quantity) }}</p>
            </div>
            <div class="theme-form-card p-5 text-center">
                <p class="text-[10px] font-black uppercase text-slate-400">{{ $t('Unit Cost') }}</p>
                <p class="text-2xl font-black text-slate-800 dark:text-slate-100 mt-1">{{ money(record.unit_cost) }}</p>
            </div>
            <div class="theme-form-card p-5 text-center bg-amber-50 dark:bg-amber-500/10">
                <p class="text-[10px] font-black uppercase text-amber-500">{{ $t('Total Loss') }}</p>
                <p class="text-2xl font-black text-amber-700 dark:text-amber-300 mt-1">{{ money(record.total_loss) }}</p>
            </div>
            <div class="theme-form-card p-5 text-center">
                <p class="text-[10px] font-black uppercase text-slate-400">{{ $t('Status') }}</p>
                <p class="mt-2">
                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-black uppercase" :class="record.status ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-slate-200 text-slate-600'">
                        {{ record.status ? $t('Active') : $t('Inactive') }}
                    </span>
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="theme-table-card">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Product & Damage Info') }}</h3>
                </div>
                <dl class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                    <div class="sm:col-span-2">
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Product') }}</dt>
                        <dd class="text-sm font-bold text-slate-800 dark:text-slate-100 mt-1">{{ record.product_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('SKU') }}</dt>
                        <dd class="text-sm font-semibold text-slate-600 dark:text-slate-300 mt-1">{{ record.sku || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Warehouse') }}</dt>
                        <dd class="text-sm font-bold text-slate-800 dark:text-slate-100 mt-1">{{ record.warehouse }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Reference') }}</dt>
                        <dd class="text-sm font-bold text-rose-600 dark:text-rose-400 mt-1">{{ record.reference_no }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Damage Date') }}</dt>
                        <dd class="text-sm font-semibold text-slate-700 dark:text-slate-200 mt-1">{{ record.damage_date || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Damage Time') }}</dt>
                        <dd class="text-sm font-semibold text-slate-700 dark:text-slate-200 mt-1">{{ record.damage_time || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Recorded By') }}</dt>
                        <dd class="text-sm font-semibold text-slate-700 dark:text-slate-200 mt-1">{{ record.recorded_by || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t('Recorded At') }}</dt>
                        <dd class="text-sm font-semibold text-slate-700 dark:text-slate-200 mt-1">{{ record.created_at || '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="theme-table-card">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Balance Impact') }}</h3>
                </div>
                <div class="p-6 space-y-5">
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700">
                        <p class="text-[10px] font-black uppercase text-slate-400 mb-3">{{ record.warehouse }}</p>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">{{ $t('Bal. Before') }}</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200">{{ num(record.balance_before) }}</span>
                        </div>
                        <div class="flex items-center justify-center my-2">
                            <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">{{ $t('Bal. After') }}</span>
                            <span class="font-black text-rose-600 dark:text-rose-400">{{ num(record.balance_after) }}</span>
                        </div>
                        <p class="text-xs text-rose-500 font-bold text-center mt-3">− {{ num(record.quantity) }} {{ $t('units removed') }}</p>
                    </div>

                    <div class="p-4 rounded-xl bg-rose-50/60 dark:bg-rose-500/10 border border-rose-100 dark:border-rose-500/20">
                        <p class="text-[10px] font-black uppercase text-rose-500 mb-2">{{ $t('Damage Reason') }}</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">{{ record.damage_reason || $t('No reason provided.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="theme-table-card">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500">{{ $t('Remarks') }}</h3>
            </div>
            <div class="p-6">
                <p v-if="record.remarks" class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-wrap bg-slate-50 dark:bg-slate-800/50 p-5 rounded-xl border border-slate-100 dark:border-slate-700">{{ record.remarks }}</p>
                <p v-else class="text-sm text-slate-400 italic">{{ $t('No remarks recorded.') }}</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ adjustment: Object });

const correctionType = () => {
    const items = props.adjustment.items ?? [];
    if (!items.length) return '—';

    const hasIn = items.some((item) => Number(item.adjustment_quantity) > 0);
    const hasOut = items.some((item) => Number(item.adjustment_quantity) < 0);

    if (hasIn && hasOut) return 'Mixed';
    if (hasIn) return 'Stock In';
    if (hasOut) return 'Stock Out';
    return '—';
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${$t('Inventory Correction')} · ${adjustment.reference_no}`" />
        <div class="max-w-8xl mx-auto mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ adjustment.reference_no }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $t('Inventory correction record — not a customer sale') }}</p>
            </div>
            <div class="flex gap-3 shrink-0">
                <Link :href="route('stock-adjustments.edit', adjustment.id)" class="theme-btn-primary px-6 py-2 rounded-full">{{ $t('Edit') }}</Link>
                <Link :href="route('stock-adjustments.index')" class="theme-form-back-link">{{ $t('Back') }}</Link>
            </div>
        </div>

        <div class="max-w-8xl mx-auto space-y-6">
            <div class="theme-form-card p-8 md:p-10">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Correction Summary') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Warehouse') }}</dt>
                        <dd class="mt-1 font-bold text-slate-800 dark:text-slate-100">{{ adjustment.warehouse?.name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Correction Date') }}</dt>
                        <dd class="mt-1 font-bold text-slate-700 dark:text-slate-200">{{ adjustment.adjustment_date }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Correction Type') }}</dt>
                        <dd class="mt-1 font-bold text-indigo-600 dark:text-indigo-300">{{ $t(correctionType()) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Created By') }}</dt>
                        <dd class="mt-1 font-bold text-slate-700 dark:text-slate-200">{{ adjustment.user?.name ?? '—' }}</dd>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="theme-form-card p-10 bg-indigo-50 dark:bg-indigo-900/10 text-center border-indigo-100 dark:border-indigo-800">
                    <dt class="text-xs font-black uppercase text-indigo-400 mb-2">{{ $t('Total Quantity Corrected') }}</dt>
                    <dd class="text-4xl font-black text-indigo-700 dark:text-indigo-300">{{ adjustment.total_quantity }}</dd>
                </div>
                <div class="theme-form-card p-10 bg-emerald-50 dark:bg-emerald-900/10 text-center border-emerald-100 dark:border-emerald-800">
                    <dt class="text-xs font-black uppercase text-emerald-400 mb-2">{{ $t('Total Value') }}</dt>
                    <dd class="text-4xl font-black text-emerald-700 dark:text-emerald-300">{{ adjustment.total_amount }}</dd>
                </div>
            </div>

            <div v-if="adjustment.items?.length" class="theme-form-card p-8 md:p-10">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Corrected Products') }}</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="theme-table-header">
                                <th class="theme-table-header-cell">{{ $t('Product') }}</th>
                                <th class="theme-table-header-cell">{{ $t('Qty (+ add / − remove)') }}</th>
                                <th class="theme-table-header-cell">{{ $t('Unit Cost') }}</th>
                                <th class="theme-table-header-cell">{{ $t('Total') }}</th>
                                <th class="theme-table-header-cell">{{ $t('Reason') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr v-for="item in adjustment.items" :key="item.id" class="theme-table-row">
                                <td class="px-4 py-3 text-sm font-bold">{{ item.product?.name }}</td>
                                <td class="px-4 py-3 text-sm font-semibold" :class="Number(item.adjustment_quantity) < 0 ? 'text-rose-600' : 'text-emerald-600'">{{ item.adjustment_quantity }}</td>
                                <td class="px-4 py-3 text-sm">{{ item.unit_cost }}</td>
                                <td class="px-4 py-3 text-sm font-bold">{{ item.total_cost }}</td>
                                <td class="px-4 py-3 text-sm">{{ item.reason || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="adjustment.remarks" class="theme-form-card p-8 md:p-10">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4">{{ $t('Remarks') }}</h3>
                <div class="text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl border border-slate-100 dark:border-slate-700 italic leading-relaxed">{{ adjustment.remarks }}</div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

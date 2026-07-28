<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ transfer: Object });

const statusClass = (status) => {
    const map = {
        draft: 'bg-slate-100 text-slate-600',
        in_transit: 'bg-amber-100 text-amber-700',
        completed: 'bg-emerald-100 text-emerald-700',
        cancelled: 'bg-rose-100 text-rose-700',
    };
    return map[status] || map.draft;
};

const statusLabel = (status) => status?.replace('_', ' ') ?? 'draft';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Transfer Details" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">{{ transfer.reference_no }}</h2>
            <div class="flex gap-3">
                <Link :href="route('stock-transfers.edit', transfer.id)" class="theme-btn-primary px-6 py-2 rounded-full">{{ $t('Edit') }}</Link>
                <Link :href="route('stock-transfers.index')" class="theme-form-back-link">{{ $t('Back') }}</Link>
            </div>
        </div>

        <div class="space-y-6">
            <div class="theme-form-card p-10">
                <div class="flex flex-wrap items-center gap-3 mb-8">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-widest" :class="statusClass(transfer.stock_status)">
                        {{ statusLabel(transfer.stock_status) }}
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('From Warehouse') }}</dt><dd class="font-bold">{{ transfer.from_warehouse?.name }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('To Warehouse') }}</dt><dd class="font-bold">{{ transfer.to_warehouse?.name }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Transfer Date') }}</dt><dd class="font-bold text-slate-700">{{ transfer.transfer_date }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Created By') }}</dt><dd class="font-bold text-slate-700">{{ transfer.user?.name }}</dd></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="theme-form-card p-10 bg-indigo-50 dark:bg-indigo-900/10 text-center border-indigo-100 dark:border-indigo-800">
                    <dt class="text-xs font-black uppercase text-indigo-400 mb-2">{{ $t('Total Quantity') }}</dt>
                    <dd class="text-4xl font-black text-indigo-700 dark:text-indigo-300">{{ transfer.total_quantity }}</dd>
                </div>
                <div class="theme-form-card p-10 bg-emerald-50 dark:bg-emerald-900/10 text-center border-emerald-100 dark:border-emerald-800">
                    <dt class="text-xs font-black uppercase text-emerald-400 mb-2">{{ $t('Total Amount') }}</dt>
                    <dd class="text-4xl font-black text-emerald-700 dark:text-emerald-300">${{ transfer.total_amount }}</dd>
                </div>
            </div>

            <div class="theme-form-card" v-if="transfer.remarks">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4">{{ $t('Remarks') }}</h3>
                    <div class="text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl border border-slate-100 dark:border-slate-700 italic leading-relaxed">
                        "{{ transfer.remarks }}"
                    </div>
                </div>
            </div>

            <div class="theme-form-card" v-if="transfer.details?.length">
                <div class="p-8 md:p-10">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $t('Line Items') }}</h3>
                        <Link :href="route('stock-transfer-details.create')" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">{{ $t('+ Add Item') }}</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell">{{ $t('Product') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Qty') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Unit Cost') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="item in transfer.details" :key="item.id" class="theme-table-row">
                                    <td class="px-6 py-3 font-bold text-slate-800 dark:text-slate-200">{{ item.product?.name }}</td>
                                    <td class="px-6 py-3 text-right text-slate-600">{{ item.quantity }}</td>
                                    <td class="px-6 py-3 text-right text-slate-600">${{ item.unit_cost }}</td>
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

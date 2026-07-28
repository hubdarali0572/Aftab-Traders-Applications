<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ purchaseReturn: Object });
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Purchase Return Details')" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">{{ purchaseReturn.reference_no }}</h2>
            <div class="flex gap-3">
                <Link :href="route('purchase-returns.edit', purchaseReturn.id)" class="theme-btn-primary px-6 py-2 rounded-full">{{ $t('Edit') }}</Link>
                <Link :href="route('purchase-returns.index')" class="theme-form-back-link">{{ $t('Back') }}</Link>
            </div>
        </div>

        <div class="space-y-6">
            <div class="theme-form-card p-10">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Purchase') }}</dt><dd class="font-bold text-indigo-600">{{ purchaseReturn.purchase?.purchase_no }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Return Date') }}</dt><dd class="font-bold text-slate-700">{{ purchaseReturn.return_date }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Warehouse') }}</dt><dd class="font-bold">{{ purchaseReturn.warehouse?.name }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Created By') }}</dt><dd class="font-bold text-slate-700">{{ purchaseReturn.user?.name || '—' }}</dd></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="theme-form-card p-10 bg-indigo-50 dark:bg-indigo-900/10 text-center border-indigo-100">
                    <dt class="text-xs font-black uppercase text-indigo-400 mb-2">{{ $t('Total Quantity') }}</dt>
                    <dd class="text-4xl font-black text-indigo-700">{{ purchaseReturn.total_quantity }}</dd>
                </div>
                <div class="theme-form-card p-10 bg-emerald-50 dark:bg-emerald-900/10 text-center border-emerald-100">
                    <dt class="text-xs font-black uppercase text-emerald-400 mb-2">{{ $t('Total Amount') }}</dt>
                    <dd class="text-4xl font-black text-emerald-700">${{ purchaseReturn.total_amount }}</dd>
                </div>
            </div>

            <div class="theme-form-card p-8" v-if="purchaseReturn.details?.length">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-4">{{ $t('Return Line Items') }}</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead><tr class="theme-table-header"><th class="theme-table-header-cell">{{ $t('Product') }}</th><th class="theme-table-header-cell text-right">{{ $t('Qty') }}</th><th class="theme-table-header-cell text-right">{{ $t('Unit Price') }}</th><th class="theme-table-header-cell text-right">{{ $t('Total') }}</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="d in purchaseReturn.details" :key="d.id" class="theme-table-row">
                                <td class="px-4 py-3 font-bold">{{ d.product?.name }}</td>
                                <td class="px-4 py-3 text-right">{{ d.quantity }}</td>
                                <td class="px-4 py-3 text-right">${{ d.unit_price }}</td>
                                <td class="px-4 py-3 text-right font-bold">${{ d.total_price }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="theme-form-card p-8" v-if="purchaseReturn.remarks">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-4">{{ $t('Remarks') }}</h3>
                <p class="text-sm text-slate-600 italic">"{{ purchaseReturn.remarks }}"</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ transfer: Object });

const formatDateTime = (transfer) => {
    if (!transfer?.transfer_date) {
        return '—';
    }

    const date = new Date(transfer.transfer_date);
    const datePart = date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });

    if (transfer.created_at) {
        const timePart = new Date(transfer.created_at).toLocaleTimeString(undefined, {
            hour: '2-digit',
            minute: '2-digit',
        });
        return `${datePart} ${timePart}`;
    }

    return datePart;
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`Transfer · ${transfer.reference_no}`" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ transfer.reference_no }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $t('Stock transfer history record') }}</p>
            </div>
            <div class="flex gap-3">
                <Link :href="route('stock-transfers.edit', transfer.id)" class="theme-btn-primary px-6 py-2 rounded-full">{{ $t('Edit') }}</Link>
                <Link :href="route('stock-transfers.index')" class="theme-form-back-link">{{ $t('Back') }}</Link>
            </div>
        </div>

        <div class="max-w-8xl mx-auto space-y-6">
            <div class="theme-form-card p-10">
                <div class="flex flex-wrap items-center gap-3 mb-8">
                    <span
                        class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-widest"
                        :class="transfer.status ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300'"
                    >
                        {{ transfer.status ? $t('Active') : $t('Inactive') }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Transfer Reference') }}</dt>
                        <dd class="font-bold text-indigo-600 dark:text-indigo-300">{{ transfer.reference_no }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Transfer Date & Time') }}</dt>
                        <dd class="font-bold text-slate-700 dark:text-slate-200">{{ formatDateTime(transfer) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Product') }}</dt>
                        <dd class="font-bold text-slate-700 dark:text-slate-200">{{ transfer.product?.name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('From Warehouse') }}</dt>
                        <dd class="font-bold text-slate-700 dark:text-slate-200">{{ transfer.from_warehouse?.name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('To Warehouse') }}</dt>
                        <dd class="font-bold text-slate-700 dark:text-slate-200">{{ transfer.to_warehouse?.name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Quantity Transferred') }}</dt>
                        <dd class="font-bold text-slate-700 dark:text-slate-200">{{ transfer.quantity }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Performed By') }}</dt>
                        <dd class="font-bold text-slate-700 dark:text-slate-200">{{ transfer.user?.name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Status') }}</dt>
                        <dd class="font-bold text-slate-700 dark:text-slate-200">{{ transfer.status ? $t('Active') : $t('Inactive') }}</dd>
                    </div>
                </div>
            </div>

            <div v-if="transfer.remarks" class="theme-form-card p-10">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4">{{ $t('Remarks') }}</h3>
                <div class="text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl border border-slate-100 dark:border-slate-700 italic leading-relaxed">
                    "{{ transfer.remarks }}"
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

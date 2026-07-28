<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    customer: Object,
    outstanding: Number,
    salesSummary: Object,
});

const formatType = (type) => type?.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) ?? '—';
const formatTxn = (type) => type?.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) ?? '—';
const formatMoney = (v) => Number(v || 0).toFixed(2);
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${customer.customer_name} — Profile`" />
        <div class="max-w-8xl mx-auto mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-indigo-500 mb-1">{{ $t('Customer Profile') }}</p>
                <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ customer.customer_name }}</h2>
                <p class="text-sm text-slate-500 font-medium">{{ customer.customer_code }} · {{ formatType(customer.customer_type) }}</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <Link :href="route('customer-ledgers.create', { customer_id: customer.id })" class="theme-form-back-link px-4 py-2">{{ $t('Add Debit / Credit') }}</Link>
                <Link :href="route('customers.sales-history', { customer_id: customer.id })" class="theme-form-back-link px-4 py-2">{{ $t('Sales History') }}</Link>
                <Link :href="route('customers.edit', customer.id)" class="theme-btn-primary px-6 py-2 rounded-full">{{ $t('Edit') }}</Link>
                <Link :href="route('customers.index')" class="theme-form-back-link">{{ $t('Back') }}</Link>
            </div>
        </div>

        <div class="space-y-6">
            <div class="theme-form-card p-10">
                <h3 class="text-xs font-black uppercase text-slate-400 mb-6 tracking-widest">{{ $t('Contact Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Type') }}</dt><dd class="font-bold uppercase text-indigo-600">{{ formatType(customer.customer_type) }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Company') }}</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ customer.company_name || '—' }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Phone') }}</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ customer.phone }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Alternate Phone') }}</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ customer.alternate_phone || '—' }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Email') }}</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ customer.email || '—' }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Tax Number') }}</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ customer.tax_number || '—' }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Address') }}</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ [customer.address, customer.city, customer.state, customer.country].filter(Boolean).join(', ') || '—' }}</dd></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="theme-form-card p-8 bg-indigo-50 dark:bg-indigo-900/10 text-center border-indigo-100 dark:border-indigo-800">
                    <dt class="text-xs font-black uppercase text-indigo-400 mb-2">{{ $t('Opening Balance') }}</dt>
                    <dd class="text-2xl font-black text-indigo-700 dark:text-indigo-300">
                        {{ customer.opening_balance_type === 'credit' ? 'Cr' : 'Dr' }} {{ formatMoney(customer.opening_balance) }}
                    </dd>
                </div>
                <div class="theme-form-card p-8 bg-emerald-50 dark:bg-emerald-900/10 text-center border-emerald-100 dark:border-emerald-800">
                    <dt class="text-xs font-black uppercase text-emerald-400 mb-2">{{ $t('Outstanding') }}</dt>
                    <dd class="text-2xl font-black text-emerald-700 dark:text-emerald-300">{{ formatMoney(outstanding) }}</dd>
                </div>
                <div class="theme-form-card p-8 bg-slate-50 dark:bg-slate-800/50 text-center">
                    <dt class="text-xs font-black uppercase text-slate-400 mb-2">{{ $t('Credit Limit') }}</dt>
                    <dd class="text-2xl font-black text-slate-700 dark:text-slate-300">{{ formatMoney(customer.credit_limit) }}</dd>
                </div>
                <div class="theme-form-card p-8 bg-amber-50 dark:bg-amber-900/10 text-center border-amber-100 dark:border-amber-800">
                    <dt class="text-xs font-black uppercase text-amber-500 mb-2">{{ $t('Completed Sales') }}</dt>
                    <dd class="text-2xl font-black text-amber-700 dark:text-amber-300">${{ formatMoney(salesSummary?.completed_amount) }}</dd>
                    <p class="text-[10px] text-amber-500 mt-2 uppercase font-bold tracking-widest">{{ salesSummary?.total_invoices || 0 }} invoices</p>
                </div>
            </div>


            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $t('Recent Ledger Entries') }}</h3>
                        <Link :href="route('customer-ledgers.index', { customer_id: customer.id })" class="text-xs font-bold text-indigo-600 hover:underline">{{ $t('View All') }}</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell">{{ $t('Date') }}</th>
                                    <th class="theme-table-header-cell">{{ $t('Type') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Debit') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Credit') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Balance') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="entry in customer.ledgers" :key="entry.id" class="theme-table-row">
                                    <td class="px-4 py-3 text-sm">{{ entry.transaction_date }}</td>
                                    <td class="px-4 py-3 text-sm font-bold">{{ formatTxn(entry.transaction_type) }}</td>
                                    <td class="px-4 py-3 text-sm text-right">{{ formatMoney(entry.debit) }}</td>
                                    <td class="px-4 py-3 text-sm text-right">{{ formatMoney(entry.credit) }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-bold">{{ formatMoney(entry.balance) }}</td>
                                </tr>
                                <tr v-if="!customer.ledgers?.length">
                                    <td colspan="5" class="px-4 py-10 text-center text-slate-400 font-medium">{{ $t('No ledger entries yet.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="theme-form-card" v-if="customer.remarks">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4">{{ $t('Remarks') }}</h3>
                    <div class="text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl border border-slate-100 dark:border-slate-700 italic leading-relaxed">
                        "{{ customer.remarks }}"
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    customer: Object,
    profile: Object,
});

const formatType = (type) => type?.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) ?? '—';
const formatTxn = (type) => type?.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) ?? '—';
const money = (v) => `$${Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
const formatDate = (d) => d ? new Date(d).toLocaleDateString() : '—';
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${customer.customer_name} — Profile`" />

        <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-indigo-500 mb-1">{{ $t('Customer Profile') }}</p>
                <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ customer.customer_name }}</h2>
                <p class="text-sm text-slate-500 font-medium mt-1">
                    {{ customer.customer_code }} · {{ formatType(customer.customer_type) }}
                    <span v-if="customer.company_name"> · {{ customer.company_name }}</span>
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Link :href="route('customers.customer-ledger', customer.id)" class="theme-form-back-link px-4 py-2">{{ $t('Full Ledger') }}</Link>
                <Link :href="route('customer-ledgers.create', { customer_id: customer.id, mode: 'payment' })" class="theme-form-back-link px-4 py-2">{{ $t('Record Payment') }}</Link>
                <Link :href="route('customers.edit', customer.id)" class="theme-btn-primary px-6 py-2">{{ $t('Edit') }}</Link>
                <Link :href="route('customers.index')" class="theme-form-back-link px-4 py-2">{{ $t('Back') }}</Link>
            </div>
        </div>

        <!-- Account summary -->
        <div class="theme-table-card mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-indigo-50 to-white dark:from-indigo-950/30 dark:to-slate-800">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">{{ $t('Account Summary') }}</h3>
            </div>
            <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800/50">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $t('Outstanding Balance') }}</p>
                    <p class="text-xl font-black mt-1" :class="profile.outstanding > 0 ? 'text-rose-600' : 'text-emerald-600'">{{ money(profile.outstanding) }}</p>
                </div>
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800/50">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $t('Opening Balance') }}</p>
                    <p class="text-xl font-black mt-1 text-indigo-600">{{ money(profile.opening_balance) }}</p>
                </div>
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800/50">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $t('Total Sales') }}</p>
                    <p class="text-xl font-black mt-1 text-amber-600">{{ money(profile.total_sales) }}</p>
                </div>
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800/50">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $t('Total Amount Paid') }}</p>
                    <p class="text-xl font-black mt-1 text-teal-600">{{ money(profile.total_paid) }}</p>
                </div>
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800/50">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $t('Total Amount Due') }}</p>
                    <p class="text-xl font-black mt-1 text-rose-600">{{ money(profile.total_due) }}</p>
                </div>
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800/50">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $t('Total Invoices') }}</p>
                    <p class="text-xl font-black mt-1 text-slate-700 dark:text-slate-300">{{ profile.total_invoices ?? 0 }}</p>
                </div>
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800/50">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $t('Last Purchase') }}</p>
                    <p class="text-sm font-bold mt-2 text-slate-700 dark:text-slate-300">{{ formatDate(profile.last_purchase_date) }}</p>
                </div>
                <div class="p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800/50">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $t('Last Payment') }}</p>
                    <p class="text-sm font-bold mt-2 text-slate-700 dark:text-slate-300">{{ formatDate(profile.last_payment_date) }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Basic info -->
            <div class="theme-form-card p-8 lg:col-span-1">
                <h3 class="text-xs font-black uppercase text-slate-400 mb-6 tracking-widest">{{ $t('Basic Information') }}</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-[10px] font-bold text-slate-400 uppercase">{{ $t('Mobile Number') }}</dt>
                        <dd class="font-bold text-slate-800 dark:text-slate-200">{{ customer.phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold text-slate-400 uppercase">{{ $t('Email') }}</dt>
                        <dd class="font-bold text-slate-800 dark:text-slate-200">{{ customer.email || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold text-slate-400 uppercase">{{ $t('Address') }}</dt>
                        <dd class="font-bold text-slate-800 dark:text-slate-200">{{ [customer.address, customer.city].filter(Boolean).join(', ') || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[10px] font-bold text-slate-400 uppercase">{{ $t('Account Status') }}</dt>
                        <dd>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                :class="customer.status ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-slate-100 text-slate-500'">
                                {{ customer.status ? $t('Active') : $t('Inactive') }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Recent ledger -->
            <div class="theme-form-card lg:col-span-2">
                <div class="p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $t('Recent Ledger Entries') }}</h3>
                        <Link :href="route('customers.customer-ledger', customer.id)" class="text-xs font-bold text-indigo-600 hover:underline">{{ $t('View Full Ledger') }}</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell">{{ $t('Date') }}</th>
                                    <th class="theme-table-header-cell">{{ $t('Reference') }}</th>
                                    <th class="theme-table-header-cell">{{ $t('Type') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Invoice Amount') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Payment Received') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Running Balance') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="entry in customer.ledgers" :key="entry.id" class="theme-table-row">
                                    <td class="px-4 py-3 text-sm">{{ entry.transaction_date }}</td>
                                    <td class="px-4 py-3 text-sm font-bold text-indigo-600">{{ entry.reference_no || '—' }}</td>
                                    <td class="px-4 py-3 text-sm">{{ formatTxn(entry.transaction_type) }}</td>
                                    <td class="px-4 py-3 text-sm text-right">{{ Number(entry.debit) > 0 ? money(entry.debit) : '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-right text-emerald-600">{{ Number(entry.credit) > 0 ? money(entry.credit) : '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-bold">{{ money(entry.balance) }}</td>
                                </tr>
                                <tr v-if="!customer.ledgers?.length">
                                    <td colspan="6" class="px-4 py-10 text-center text-slate-400 font-medium">{{ $t('No ledger entries yet.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="customer.remarks" class="theme-form-card mt-6 p-8">
            <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4">{{ $t('Remarks') }}</h3>
            <p class="text-sm text-slate-600 dark:text-slate-300 italic">{{ customer.remarks }}</p>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ customer: Object, outstanding: Number });

const formatType = (type) => type?.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) ?? '—';
const formatTxn = (type) => type?.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) ?? '—';
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="customer.customer_name" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ customer.customer_name }}</h2>
                <p class="text-sm text-slate-500 font-medium">{{ customer.customer_code }}</p>
            </div>
            <div class="flex gap-3">
                <Link :href="route('customers.edit', customer.id)" class="theme-btn-primary px-6 py-2 rounded-full">Edit</Link>
                <Link :href="route('customers.index')" class="theme-form-back-link">Back</Link>
            </div>
        </div>

        <div class="space-y-6">
            <div class="theme-form-card p-10">
                <h3 class="text-xs font-black uppercase text-slate-400 mb-6 tracking-widest">Contact Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Type</dt><dd class="font-bold uppercase text-indigo-600">{{ formatType(customer.customer_type) }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Company</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ customer.company_name || '—' }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Phone</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ customer.phone }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Alternate Phone</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ customer.alternate_phone || '—' }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Email</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ customer.email || '—' }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Tax Number</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ customer.tax_number || '—' }}</dd></div>
                    <div class="md:col-span-2"><dt class="text-xs font-bold text-slate-400 uppercase">Address</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ [customer.address, customer.city, customer.state, customer.country].filter(Boolean).join(', ') || '—' }}</dd></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="theme-form-card p-10 bg-indigo-50 dark:bg-indigo-900/10 text-center border-indigo-100 dark:border-indigo-800">
                    <dt class="text-xs font-black uppercase text-indigo-400 mb-2">Opening Balance</dt>
                    <dd class="text-3xl font-black text-indigo-700 dark:text-indigo-300">
                        {{ customer.opening_balance_type === 'credit' ? 'Cr' : 'Dr' }} {{ customer.opening_balance }}
                    </dd>
                    <p class="text-[10px] text-indigo-400 mt-2 uppercase font-bold tracking-widest">{{ customer.opening_balance_type }} side</p>
                </div>
                <div class="theme-form-card p-10 bg-emerald-50 dark:bg-emerald-900/10 text-center border-emerald-100 dark:border-emerald-800">
                    <dt class="text-xs font-black uppercase text-emerald-400 mb-2">Current Outstanding</dt>
                    <dd class="text-3xl font-black text-emerald-700 dark:text-emerald-300">{{ outstanding }}</dd>
                </div>
                <div class="theme-form-card p-10 bg-slate-50 dark:bg-slate-800/50 text-center">
                    <dt class="text-xs font-black uppercase text-slate-400 mb-2">Credit Limit</dt>
                    <dd class="text-3xl font-black text-slate-700 dark:text-slate-300">{{ customer.credit_limit }}</dd>
                </div>
            </div>

            <div class="theme-form-card" v-if="customer.ledgers?.length">
                <div class="p-8 md:p-10">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">Recent Ledger Entries</h3>
                        <Link :href="route('customer-ledgers.index', { customer_id: customer.id })" class="text-xs font-bold text-indigo-600 hover:underline">View All</Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell">Date</th>
                                    <th class="theme-table-header-cell">Type</th>
                                    <th class="theme-table-header-cell text-right">Debit</th>
                                    <th class="theme-table-header-cell text-right">Credit</th>
                                    <th class="theme-table-header-cell text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="entry in customer.ledgers" :key="entry.id" class="theme-table-row">
                                    <td class="px-4 py-3 text-sm">{{ entry.transaction_date }}</td>
                                    <td class="px-4 py-3 text-sm font-bold">{{ formatTxn(entry.transaction_type) }}</td>
                                    <td class="px-4 py-3 text-sm text-right">{{ entry.debit }}</td>
                                    <td class="px-4 py-3 text-sm text-right">{{ entry.credit }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-bold">{{ entry.balance }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="theme-form-card" v-if="customer.remarks">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4">Remarks</h3>
                    <div class="text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl border border-slate-100 dark:border-slate-700 italic leading-relaxed">
                        "{{ customer.remarks }}"
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ ledger: Object });

const formatTxn = (type) => type?.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) ?? '—';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Ledger Entry Details" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">{{ formatTxn(ledger.transaction_type) }}</h2>
            <div class="flex gap-3">
                <Link v-if="ledger.reference_type === 'Manual'" :href="route('customer-ledgers.edit', ledger.id)" class="theme-btn-primary px-6 py-2">Edit</Link>
                <Link :href="route('customer-ledgers.index')" class="theme-form-back-link">Back</Link>
            </div>
        </div>

        <div class="space-y-6">
            <div class="theme-form-card p-10">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Customer</dt><dd class="font-bold">{{ ledger.customer?.customer_name }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Date</dt><dd class="font-bold text-slate-700">{{ ledger.transaction_date }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Reference</dt><dd class="font-bold">{{ ledger.reference_no || '—' }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Source</dt><dd class="font-bold uppercase text-indigo-600">{{ ledger.reference_type }}</dd></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="theme-form-card p-10 bg-rose-50 dark:bg-rose-900/10 text-center border-rose-100">
                    <dt class="text-xs font-black uppercase text-rose-400 mb-2">Debit</dt>
                    <dd class="text-4xl font-black text-rose-700">{{ ledger.debit }}</dd>
                </div>
                <div class="theme-form-card p-10 bg-emerald-50 dark:bg-emerald-900/10 text-center border-emerald-100">
                    <dt class="text-xs font-black uppercase text-emerald-400 mb-2">Credit</dt>
                    <dd class="text-4xl font-black text-emerald-700">{{ ledger.credit }}</dd>
                </div>
                <div class="theme-form-card p-10 bg-indigo-50 dark:bg-indigo-900/10 text-center border-indigo-100">
                    <dt class="text-xs font-black uppercase text-indigo-400 mb-2">Running Balance</dt>
                    <dd class="text-4xl font-black text-indigo-700">{{ ledger.balance }}</dd>
                </div>
            </div>

            <div class="theme-form-card" v-if="ledger.remarks">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-4">Remarks</h3>
                    <div class="text-sm text-slate-600 bg-slate-50 p-6 rounded-xl border italic leading-relaxed">"{{ ledger.remarks }}"</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    customer: Object,
    ledgers: Object,
    profile: Object,
    filters: Object,
});

const dateFrom = ref(props.filters?.date_from ?? '');
const dateTo = ref(props.filters?.date_to ?? '');

const money = (v) => `$${Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
const formatTxn = (type) => type?.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) ?? '—';

const applyFilters = () => {
    router.get(route('customers.customer-ledger', props.customer.id), {
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    }, { preserveState: true, replace: true });
};

const clearFilters = () => {
    dateFrom.value = '';
    dateTo.value = '';
    applyFilters();
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${customer.customer_name} — ${$t('Customer Ledger')}`" />

        <div class="mb-8 flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">
            <div>
                <p class="text-[11px] font-black uppercase tracking-widest text-indigo-500 mb-1">{{ $t('Customer Ledger') }}</p>
                <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ customer.customer_name }}</h2>
                <p class="text-sm text-slate-500 font-medium">{{ customer.customer_code }} · {{ $t('Outstanding') }}: {{ money(profile.outstanding) }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Link :href="route('customer-ledgers.create', { customer_id: customer.id, mode: 'payment' })" class="theme-btn-primary px-4 py-2">{{ $t('Record Payment') }}</Link>
                <Link :href="route('customers.show', customer.id)" class="theme-form-back-link px-4 py-2">{{ $t('Back to Profile') }}</Link>
            </div>
        </div>

        <div class="theme-table-card mb-6">
            <form @submit.prevent="applyFilters" class="p-6 flex flex-col md:flex-row gap-4">
                <input v-model="dateFrom" type="date" class="theme-form-input md:w-48" />
                <input v-model="dateTo" type="date" class="theme-form-input md:w-48" />
                <div class="flex gap-2">
                    <button type="submit" class="theme-btn-primary px-6 py-2.5">{{ $t('Filter') }}</button>
                    <button v-if="filters?.date_from || filters?.date_to" type="button" @click="clearFilters" class="theme-form-back-link px-4 py-2.5">{{ $t('Clear') }}</button>
                </div>
            </form>
        </div>

        <div class="theme-table-card">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell">{{ $t('Date') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Reference No.') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Transaction Type') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Invoice Amount') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Payment Received') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Running Balance') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Remarks') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="entry in ledgers.data" :key="entry.id" class="theme-table-row">
                            <td class="px-6 py-3 text-sm">{{ entry.transaction_date }}</td>
                            <td class="px-6 py-3 text-sm font-bold text-indigo-600">{{ entry.reference_no || '—' }}</td>
                            <td class="px-6 py-3 text-sm">{{ formatTxn(entry.transaction_type) }}</td>
                            <td class="px-6 py-3 text-sm text-right">{{ Number(entry.debit) > 0 ? money(entry.debit) : '—' }}</td>
                            <td class="px-6 py-3 text-sm text-right text-emerald-600">{{ Number(entry.credit) > 0 ? money(entry.credit) : '—' }}</td>
                            <td class="px-6 py-3 text-sm text-right font-bold">{{ money(entry.balance) }}</td>
                            <td class="px-6 py-3 text-sm text-slate-500">{{ entry.remarks || '—' }}</td>
                        </tr>
                        <tr v-if="ledgers.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-medium">{{ $t('No ledger entries found.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="theme-table-footer flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">
                    {{ $t('Showing') }} {{ ledgers.from || 0 }} {{ $t('to') }} {{ ledgers.to || 0 }} {{ $t('of') }} {{ ledgers.total }} {{ $t('entries') }}
                </div>
                <div class="flex flex-wrap justify-center items-center gap-1.5 mt-4 sm:mt-0">
                    <template v-for="(link, k) in ledgers.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label"
                            class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border transition-all duration-200"
                            :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']" />
                        <span v-else v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold text-slate-300 bg-white border border-slate-100 rounded-lg cursor-not-allowed dark:text-slate-600 dark:bg-slate-800" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

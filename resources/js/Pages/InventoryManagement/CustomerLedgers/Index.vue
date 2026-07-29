<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    ledgers: Object,
    summary: Object,
    customers: Array,
    filters: Object,
    transactionTypes: Array,
});

const searchQuery = ref(props.filters?.search ?? '');
const customerId = ref(props.filters?.customer_id ?? '');
const transactionType = ref(props.filters?.transaction_type ?? '');
const isModalOpen = ref(false);
const selectedId = ref(null);

const money = (v) => `$${Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
const formatTxn = (type) => type?.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) ?? '—';

const overviewStats = computed(() => [
    { title: 'Total Entries', value: props.summary?.total_entries ?? 0, tone: 'text-indigo-700 dark:text-indigo-300', bg: 'bg-indigo-50 dark:bg-indigo-500/10' },
    { title: 'Total Debit', value: money(props.summary?.total_debit), tone: 'text-rose-700 dark:text-rose-300', bg: 'bg-rose-50 dark:bg-rose-500/10' },
    { title: 'Total Credit', value: money(props.summary?.total_credit), tone: 'text-emerald-700 dark:text-emerald-300', bg: 'bg-emerald-50 dark:bg-emerald-500/10' },
]);

const openDeleteModal = (id) => { selectedId.value = id; isModalOpen.value = true; };

const confirmDelete = () => {
    router.delete(route('customer-ledgers.destroy', selectedId.value), {
        onSuccess: () => { isModalOpen.value = false; },
    });
};

const applySearch = () => {
    router.get(route('customer-ledgers.index'), {
        search: searchQuery.value || undefined,
        customer_id: customerId.value || undefined,
        transaction_type: transactionType.value || undefined,
    }, { preserveState: true, replace: true });
};

const clearSearch = () => {
    searchQuery.value = '';
    customerId.value = '';
    transactionType.value = '';
    applySearch();
};

const hasFilters = () => Object.values(props.filters ?? {}).some((v) => v);
const isManual = (entry) => entry.reference_type === 'Manual';
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Customer Ledger')" />

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">{{ $t('Customer Ledger') }}</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">{{ $t('Complete transaction history for all customer accounts.') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <Link :href="route('customers.index')" class="theme-form-back-link px-4 py-2">{{ $t('Customers') }}</Link>
                <Link :href="route('customer-ledgers.create', { mode: 'payment' })" class="theme-form-back-link px-4 py-2">{{ $t('Record Payment') }}</Link>
                <Link :href="route('customer-ledgers.create')" class="theme-btn-primary inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5" /></svg>
                    {{ $t('New Entry') }}
                </Link>
            </div>
        </div>

        <div class="theme-table-card mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-r from-indigo-50 to-white dark:from-indigo-950/30 dark:to-slate-800">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">{{ $t('Ledger Overview') }}</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div v-for="stat in overviewStats" :key="stat.title" class="p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800/50">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $t(stat.title) }}</p>
                    <p class="text-xl font-black mt-1" :class="stat.tone">{{ stat.value }}</p>
                </div>
            </div>
        </div>

        <div class="theme-table-card mb-6">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-xs font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">{{ $t('Search & Filters') }}</h3>
            </div>
            <form @submit.prevent="applySearch" class="p-6 flex flex-col md:flex-row gap-4">
                <input v-model="searchQuery" type="text" class="theme-form-input flex-1" :placeholder="$t('Reference no. or customer...')" />
                <select v-model="customerId" class="theme-form-input md:w-56">
                    <option value="">{{ $t('All Customers') }}</option>
                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.customer_code }} — {{ c.customer_name }}</option>
                </select>
                <select v-model="transactionType" class="theme-form-input md:w-48">
                    <option value="">{{ $t('All Types') }}</option>
                    <option v-for="t in transactionTypes" :key="t" :value="t">{{ formatTxn(t) }}</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="theme-btn-primary px-6 py-2.5">{{ $t('Apply Filters') }}</button>
                    <button v-if="hasFilters()" type="button" @click="clearSearch" class="theme-form-back-link px-4 py-2.5">{{ $t('Clear') }}</button>
                </div>
            </form>
        </div>

        <div class="theme-table-card">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell">{{ $t('Date') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Customer') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Type') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Reference') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Invoice Amount') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Payment Received') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Running Balance') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="entry in ledgers.data" :key="entry.id" class="theme-table-row group">
                            <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400">{{ entry.transaction_date }}</td>
                            <td class="px-6 py-3">
                                <Link v-if="entry.customer" :href="route('customers.show', entry.customer.id)" class="font-bold text-indigo-600 hover:underline">{{ entry.customer.customer_name }}</Link>
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400">
                                    {{ formatTxn(entry.transaction_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm">
                                <div class="font-medium">{{ entry.reference_no || '—' }}</div>
                                <div class="text-[10px] text-slate-400">{{ entry.reference_type }}</div>
                            </td>
                            <td class="px-6 py-3 text-sm text-right font-bold text-rose-600">{{ Number(entry.debit) > 0 ? money(entry.debit) : '—' }}</td>
                            <td class="px-6 py-3 text-sm text-right font-bold text-emerald-600">{{ Number(entry.credit) > 0 ? money(entry.credit) : '—' }}</td>
                            <td class="px-6 py-3 text-sm text-right font-black text-slate-700 dark:text-slate-300">{{ money(entry.balance) }}</td>
                            <td class="px-6 py-3 text-right whitespace-nowrap">
                                <div class="theme-table-actions">
                                    <Link :href="route('customer-ledgers.show', entry.id)" class="theme-table-action-btn" :title="$t('View')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </Link>
                                    <Link v-if="isManual(entry)" :href="route('customer-ledgers.edit', entry.id)" class="theme-table-action-btn theme-table-action-edit" :title="$t('Edit')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </Link>
                                    <button v-if="isManual(entry)" @click="openDeleteModal(entry.id)" class="theme-table-action-btn theme-table-action-delete" :title="$t('Delete')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="ledgers.data.length === 0">
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400 font-medium">{{ $t('No ledger entries found.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="theme-table-footer flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">
                    {{ $t('Showing') }} {{ ledgers.from || 0 }} {{ $t('to') }} {{ ledgers.to || 0 }} {{ $t('of') }} {{ ledgers.total }} {{ $t('entries') }}
                </div>
                <div class="flex gap-1.5">
                    <template v-for="(link, k) in ledgers.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border transition-all" :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']" />
                    </template>
                </div>
            </div>
        </div>

        <ConfirmModal
            :show="isModalOpen"
            :title="$t('Delete Ledger Entry')"
            :message="$t('Remove this manual ledger entry?')"
            @close="isModalOpen = false"
            @confirm="confirmDelete"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    customers: Array,
    totalOutstanding: Number,
    filters: Object,
    customerTypes: Array,
});

const searchQuery = ref(props.filters?.search ?? '');
const customerType = ref(props.filters?.customer_type ?? '');
const onlyDue = ref(
    props.filters?.only_due === true ||
    props.filters?.only_due === '1' ||
    props.filters?.only_due === 1
        ? '1'
        : ''
);

const formatType = (type) => type?.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) ?? '—';
const formatMoney = (v) => Number(v || 0).toFixed(2);

const resultCount = computed(() => props.customers?.length || 0);

const applySearch = () => {
    router.get(route('customers.outstanding'), {
        search: searchQuery.value || undefined,
        customer_type: customerType.value || undefined,
        only_due: onlyDue.value || undefined,
    }, { preserveState: true, replace: true });
};

const clearSearch = () => {
    searchQuery.value = '';
    customerType.value = '';
    onlyDue.value = '';
    applySearch();
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Outstanding Balance" />

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">Outstanding Balance</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">
                    Customers with a non-zero ledger balance · Total receivable
                    <span class="font-bold text-indigo-600">${{ formatMoney(totalOutstanding) }}</span>
                </p>
            </div>
            <Link :href="route('customer-ledgers.create')" class="theme-btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 5v14m7-7H5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Debit / Credit Entry
            </Link>
        </div>

        <div class="theme-table-card">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <form @submit.prevent="applySearch" class="flex flex-col md:flex-row gap-3">
                    <input
                        v-model="searchQuery"
                        type="text"
                        class="theme-form-input flex-1"
                        placeholder="Search by code, name, company or phone..."
                    />
                    <select v-model="customerType" class="theme-form-input md:w-48">
                        <option value="">All Types</option>
                        <option v-for="t in customerTypes" :key="t" :value="t">{{ formatType(t) }}</option>
                    </select>
                    <select v-model="onlyDue" class="theme-form-input md:w-44">
                        <option value="">All Balances</option>
                        <option value="1">Due only (&gt; 0)</option>
                    </select>
                    <div class="flex gap-2">
                        <button type="submit" class="theme-btn-primary px-6 py-2.5">Search</button>
                        <button
                            v-if="filters?.search || filters?.customer_type || filters?.only_due"
                            type="button"
                            @click="clearSearch"
                            class="theme-form-back-link px-4 py-2.5"
                        >
                            Clear
                        </button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell">Code</th>
                            <th class="theme-table-header-cell">Customer</th>
                            <th class="theme-table-header-cell">Type</th>
                            <th class="theme-table-header-cell text-right">Credit Limit</th>
                            <th class="theme-table-header-cell text-right">Outstanding</th>
                            <th class="theme-table-header-cell text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="c in customers" :key="c.id" class="theme-table-row group">
                            <td class="px-6 py-3 font-bold text-indigo-600 dark:text-indigo-400">{{ c.customer_code }}</td>
                            <td class="px-6 py-3">
                                <div class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ c.customer_name }}</div>
                                <div class="text-[10px] text-slate-400 font-medium">{{ c.phone }}</div>
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-widest bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400">
                                    {{ formatType(c.customer_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-sm text-right text-slate-600 dark:text-slate-400">
                                {{ formatMoney(c.credit_limit) }}
                            </td>
                            <td
                                class="px-6 py-3 text-sm text-right font-bold"
                                :class="Number(c.outstanding) > 0
                                    ? 'text-amber-600 dark:text-amber-400'
                                    : 'text-emerald-600 dark:text-emerald-400'"
                            >
                                {{ formatMoney(c.outstanding) }}
                            </td>
                            <td class="px-6 py-3 text-right whitespace-nowrap">
                                <div class="theme-table-actions">
                                    <Link
                                        :href="route('customers.show', c.id)"
                                        class="theme-table-action-btn"
                                        title="Profile"
                                    >
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </Link>
                                    <Link
                                        :href="route('customer-ledgers.create', { customer_id: c.id })"
                                        class="theme-table-action-btn theme-table-action-edit"
                                        title="Debit / Credit Entry"
                                    >
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    </Link>
                                    <Link
                                        :href="route('customer-ledgers.index', { customer_id: c.id })"
                                        class="theme-table-action-btn"
                                        title="Ledger"
                                    >
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25H12" />
                                        </svg>
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="customers.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-medium dark:text-slate-500">
                                No outstanding balances found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="theme-table-footer flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest text-center sm:text-left">
                    Showing {{ resultCount }} {{ resultCount === 1 ? 'customer' : 'customers' }}
                    · Total receivable ${{ formatMoney(totalOutstanding) }}
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

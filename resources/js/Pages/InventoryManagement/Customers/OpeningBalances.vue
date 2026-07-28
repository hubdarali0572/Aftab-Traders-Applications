<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    customers: Object,
    filters: Object,
    customerTypes: Array,
});

const searchQuery = ref(props.filters?.search ?? '');
const customerType = ref(props.filters?.customer_type ?? '');

const formatType = (type) => type?.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) ?? '—';
const formatMoney = (v) => Number(v || 0).toFixed(2);

const applySearch = () => {
    router.get(route('customers.opening-balances'), {
        search: searchQuery.value || undefined,
        customer_type: customerType.value || undefined,
    }, { preserveState: true, replace: true });
};

const clearSearch = () => {
    searchQuery.value = '';
    customerType.value = '';
    applySearch();
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Opening Balances" />

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">Opening Balance</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">Customer opening balances posted to the ledger.</p>
            </div>
            <Link :href="route('customers.create')" class="theme-btn-primary">New Customer</Link>
        </div>

        <div class="theme-table-card">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <form @submit.prevent="applySearch" class="flex flex-col md:flex-row gap-3">
                    <input v-model="searchQuery" type="text" class="theme-form-input flex-1" placeholder="Search customers..." />
                    <select v-model="customerType" class="theme-form-input md:w-48">
                        <option value="">All Types</option>
                        <option v-for="t in customerTypes" :key="t" :value="t">{{ formatType(t) }}</option>
                    </select>
                    <div class="flex gap-2">
                        <button type="submit" class="theme-btn-primary px-6 py-2.5">Search</button>
                        <button v-if="filters?.search || filters?.customer_type" type="button" @click="clearSearch" class="theme-form-back-link px-4 py-2.5">Clear</button>
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
                            <th class="theme-table-header-cell">Side</th>
                            <th class="theme-table-header-cell text-right">Opening Balance</th>
                            <th class="theme-table-header-cell text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="c in customers.data" :key="c.id" class="theme-table-row">
                            <td class="px-6 py-3 font-bold text-indigo-600">{{ c.customer_code }}</td>
                            <td class="px-6 py-3">
                                <div class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ c.customer_name }}</div>
                                <div v-if="c.company_name" class="text-[10px] text-slate-400">{{ c.company_name }}</div>
                            </td>
                            <td class="px-6 py-3 text-xs font-black uppercase tracking-widest text-slate-500">{{ formatType(c.customer_type) }}</td>
                            <td class="px-6 py-3 text-sm font-bold uppercase">{{ c.opening_balance_type }}</td>
                            <td class="px-6 py-3 text-right font-black text-slate-800 dark:text-slate-100">
                                {{ c.opening_balance_type === 'credit' ? 'Cr' : 'Dr' }} {{ formatMoney(c.opening_balance) }}
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="theme-table-actions justify-end">
                                    <Link :href="route('customers.show', c.id)" class="theme-table-action-btn" title="Profile">View</Link>
                                    <Link :href="route('customers.edit', c.id)" class="theme-table-action-btn theme-table-action-edit" title="Edit">Edit</Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="customers.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-medium">No customers found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="theme-table-footer flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">
                    Showing {{ customers.from || 0 }} to {{ customers.to || 0 }} of {{ customers.total }} entries
                </div>
                <div class="flex flex-wrap justify-center items-center gap-1.5 mt-4 sm:mt-0">
                    <template v-for="(link, k) in customers.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border transition-all" :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']" />
                        <span v-else v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold text-slate-300 bg-white border border-slate-100 rounded-lg cursor-not-allowed" />
                    </template>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

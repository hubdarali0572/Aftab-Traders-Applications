<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ stocks: Object, filters: Object });
const searchQuery = ref(props.filters?.search ?? '');
const isModalOpen = ref(false);
const selectedId = ref(null);

const openDeleteModal = (id) => { selectedId.value = id; isModalOpen.value = true; };
const confirmDelete = () => {
    router.delete(route('opening-stocks.destroy', selectedId.value), {
        onSuccess: () => { isModalOpen.value = false; }
    });
};

const applySearch = () => {
    router.get(route('opening-stocks.index'), { search: searchQuery.value || undefined }, { 
        preserveState: true,
        replace: true 
    });
};

const clearSearch = () => {
    searchQuery.value = '';
    applySearch();
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Opening Stocks" />
        
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">Opening Stocks</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">Manage initial warehouse stock levels.</p>
            </div>
            <Link :href="route('opening-stocks.create')" class="theme-btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 5v14m7-7H5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Add Opening Stock
            </Link>
        </div>

        <div class="theme-table-card">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <form @submit.prevent="applySearch" class="flex flex-col sm:flex-row gap-3">
                    <input v-model="searchQuery" type="text" class="theme-form-input flex-1" placeholder="Search by reference or warehouse..." />
                    <div class="flex gap-2">
                        <button type="submit" class="theme-btn-primary px-6 py-2.5">Search</button>
                        <button v-if="filters?.search" type="button" @click="clearSearch" class="theme-form-back-link px-4 py-2.5">Clear</button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell">Ref #</th>
                            <th class="theme-table-header-cell">Date</th>
                            <th class="theme-table-header-cell">Warehouse</th>
                            <th class="theme-table-header-cell text-right">Total Qty</th>
                            <th class="theme-table-header-cell text-right">Total Amount</th>
                            <th class="theme-table-header-cell">Status</th>
                            <th class="theme-table-header-cell text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="stock in stocks.data" :key="stock.id" class="theme-table-row group">
                            <td class="px-6 py-3 font-bold text-slate-800 dark:text-slate-200">{{ stock.reference_no }}</td>
                            <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400">{{ stock.opening_date }}</td>
                            <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400">{{ stock.warehouse?.name }}</td>
                            <td class="px-6 py-3 text-sm text-right font-bold text-slate-700 dark:text-slate-300">{{ stock.total_quantity }}</td>
                            <td class="px-6 py-3 text-sm text-right font-bold text-slate-700 dark:text-slate-300">${{ stock.total_amount }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold" :class="stock.status ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400'">
                                    {{ stock.status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <div class="theme-table-actions">
                                    <Link :href="route('opening-stocks.show', stock.id)" class="theme-table-action-btn" title="View Detail">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </Link>
                                    <Link :href="route('opening-stocks.edit', stock.id)" class="theme-table-action-btn theme-table-action-edit">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    </Link>
                                    <button @click="openDeleteModal(stock.id)" class="theme-table-action-btn theme-table-action-delete">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Empty State Logic -->
                        <tr v-if="stocks.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-medium dark:text-slate-500">
                                No Opening stock records found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="theme-table-footer flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">
                    Showing {{ stocks.from || 0 }} to {{ stocks.to || 0 }} of {{ stocks.total }} entries
                </div>
                <div class="flex gap-1.5">
                    <template v-for="(link, k) in stocks.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border transition-all" :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']" />
                        <span v-else v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold text-slate-300 bg-white border border-slate-100 rounded-lg cursor-not-allowed dark:text-slate-600 dark:bg-slate-800" />
                    </template>
                </div>
            </div>
        </div>

        <ConfirmModal 
            :show="isModalOpen" 
            title="Delete Opening Stock" 
            message="Are you sure you want to permanently remove this opening stock record? This cannot be undone." 
            @close="isModalOpen = false" 
            @confirm="confirmDelete" 
        />
    </AuthenticatedLayout>
</template>
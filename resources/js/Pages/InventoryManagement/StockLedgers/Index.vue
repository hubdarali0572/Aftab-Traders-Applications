<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    ledgers: Object,
    filters: Object,
});

const page = usePage();
const showFlash = ref(false);
const searchQuery = ref(props.filters?.search ?? '');
let timer = null;

const startTimer = () => {
    showFlash.value = true;
    if (timer) clearTimeout(timer);
    timer = setTimeout(() => { showFlash.value = false; }, 5000);
};

watch(
    () => [page.props.flash.success, page.props.flash.danger],
    ([newSuccess, newDanger]) => { if (newSuccess || newDanger) startTimer(); },
    { immediate: true }
);

const isModalOpen = ref(false);
const selectedLedger = ref(null);

const openDeleteModal = (ledger) => {
    selectedLedger.value = ledger;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => { selectedLedger.value = null; }, 300);
};

const confirmDelete = () => {
    if (selectedLedger.value) {
        router.delete(route('stock-ledgers.destroy', selectedLedger.value.id), {
            onSuccess: () => closeModal(),
            onFinish: () => closeModal(),
        });
    }
};

const applySearch = () => {
    router.get(route('stock-ledgers.index'), { search: searchQuery.value || undefined }, {
        preserveState: true,
        replace: true,
    });
};

const clearSearch = () => {
    searchQuery.value = '';
    applySearch();
};

const formatType = (type) => {
    return type.replace('_', ' ').toUpperCase();
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Stock Ledger')" />

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">{{ $t('Stock Ledger') }}</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">{{ $t('Complete history of stock movements.') }}</p>
            </div>
            <Link :href="route('stock-ledgers.create')" class="theme-btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 5v14m7-7H5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                {{ $t('New Ledger Entry') }}
            </Link>
        </div>

        <div class="theme-table-card">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <form @submit.prevent="applySearch" class="flex flex-col sm:flex-row gap-3">
                    <input v-model="searchQuery" type="text" class="theme-form-input flex-1" placeholder="Search by Reference # or Product..." />
                    <div class="flex gap-2">
                        <button type="submit" class="theme-btn-primary px-6 py-2.5">{{ $t('Search') }}</button>
                        <button v-if="filters?.search" type="button" @click="clearSearch" class="theme-form-back-link px-4 py-2.5">{{ $t('Clear') }}</button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell">{{ $t('Date') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Type') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Ref #') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Warehouse') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Product') }}</th>
                            <th class="theme-table-header-cell">{{ $t('In') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Out') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Balance') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Total Cost') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="ledger in ledgers.data" :key="ledger.id" class="theme-table-row group">
                            <td class="px-6 py-2 whitespace-nowrap">
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-100">{{ ledger.transaction_date }}</div>
                            </td>
                            <td class="px-6 py-2">
                                <span class="text-[10px] px-2 py-0.5 rounded font-bold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                                    {{ formatType(ledger.transaction_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-2"><div class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ ledger.reference_no }}</div></td>
                            <td class="px-6 py-2"><div class="text-sm text-slate-600 dark:text-slate-300">{{ ledger.warehouse?.name }}</div></td>
                            <td class="px-6 py-2"><div class="text-sm text-slate-600 dark:text-slate-300">{{ ledger.product?.name }}</div></td>
                            <td class="px-6 py-2"><div class="text-sm font-bold text-emerald-600">+{{ ledger.quantity_in }}</div></td>
                            <td class="px-6 py-2"><div class="text-sm font-bold text-rose-600">-{{ ledger.quantity_out }}</div></td>
                            <td class="px-6 py-2"><div class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ ledger.balance_quantity }}</div></td>
                            <td class="px-6 py-2"><div class="text-sm text-slate-600 dark:text-slate-300">{{ ledger.total_cost }}</div></td>
                            <td class="px-6 py-2 whitespace-nowrap text-right">
                                <div class="theme-table-actions">
                                    <Link :href="route('stock-ledgers.show', ledger.id)" class="theme-table-action-btn" title="View Detail">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </Link>
                                     <Link :href="route('stock-ledgers.edit', ledger.id)" class="theme-table-action-btn theme-table-action-edit" title="Edit Entry">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </Link>
                                    <button @click="openDeleteModal(ledger)" class="theme-table-action-btn theme-table-action-delete">
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" /></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="ledgers.data.length === 0">
                            <td colspan="10" class="px-6 py-12 text-center text-slate-400 font-medium">{{ $t('No ledger entries found.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="theme-table-footer flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">
                    Showing {{ ledgers.from }} to {{ ledgers.to }} of {{ ledgers.total }} entries
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
            title="Delete Ledger Entry"
            message="This action will remove the record from history. It may cause balance discrepancies."
            confirm-label="Delete Entry"
            @close="closeModal"
            @confirm="confirmDelete"
        />
    </AuthenticatedLayout>
</template>
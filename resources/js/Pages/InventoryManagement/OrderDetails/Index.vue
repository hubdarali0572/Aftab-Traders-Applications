<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    details: Object,
    orders: Array,
    filters: Object,
});

const page = usePage();
const showFlash = ref(false);
const searchQuery = ref(props.filters?.search ?? '');
const orderId = ref(props.filters?.order_id ?? '');
const isModalOpen = ref(false);
const selectedId = ref(null);

watch(
    () => [page.props.flash?.success, page.props.flash?.error],
    ([s, e]) => { if (s || e) showFlash.value = true; },
    { immediate: true }
);

const openDeleteModal = (id) => { selectedId.value = id; isModalOpen.value = true; };

const confirmDelete = () => {
    router.delete(route('order-details.destroy', selectedId.value), {
        onSuccess: () => { isModalOpen.value = false; },
    });
};

const applySearch = () => {
    router.get(route('order-details.index'), {
        search: searchQuery.value || undefined,
        order_id: orderId.value || undefined,
    }, { preserveState: true, replace: true });
};

const clearSearch = () => {
    searchQuery.value = '';
    orderId.value = '';
    applySearch();
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Order Details')" />

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">{{ $t('Order Details') }}</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">{{ $t('Manage order line items and product quantities.') }}</p>
            </div>
            <Link :href="route('order-details.create')" class="theme-btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 5v14m7-7H5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                {{ $t('Add Order Item') }}
            </Link>
        </div>

        <div
            v-if="showFlash && (page.props.flash?.success || page.props.flash?.error)"
            class="mb-6 flex items-center p-4 border-l-4 rounded-r-xl shadow-sm"
            :class="page.props.flash?.success
                ? 'bg-indigo-50 border-indigo-500 text-indigo-800 dark:bg-indigo-500/10 dark:text-indigo-200'
                : 'bg-red-50 border-red-500 text-red-800 dark:bg-red-500/10 dark:text-red-200'"
        >
            <p class="ml-3 text-sm font-bold">{{ page.props.flash?.success || page.props.flash?.error }}</p>
        </div>

        <div class="theme-table-card">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50">
                <form @submit.prevent="applySearch" class="flex flex-col md:flex-row gap-4">
                    <input v-model="searchQuery" type="text" class="theme-form-input flex-1" placeholder="Search by product or order #..." />
                    <select v-model="orderId" class="theme-form-input md:w-64">
                        <option value="">{{ $t('All Orders') }}</option>
                        <option v-for="o in orders" :key="o.id" :value="o.id">{{ o.order_no }}</option>
                    </select>
                    <div class="flex gap-2">
                        <button type="submit" class="theme-btn-primary px-6 py-2.5">{{ $t('Filter') }}</button>
                        <button v-if="filters?.search || filters?.order_id" type="button" @click="clearSearch" class="theme-form-back-link px-4 py-2.5">{{ $t('Clear') }}</button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell">{{ $t('Order #') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Product') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Unit') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Qty') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Price') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Line Total') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="item in details.data" :key="item.id" class="theme-table-row group">
                            <td class="px-6 py-4 font-bold text-indigo-600 dark:text-indigo-400">{{ item.order?.order_no }}</td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ item.product?.name }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm uppercase text-slate-500">{{ item.unit?.name }}</td>
                            <td class="px-6 py-4 text-right text-slate-600 dark:text-slate-400">{{ item.quantity }}</td>
                            <td class="px-6 py-4 text-right text-slate-600 dark:text-slate-400">${{ item.unit_price }}</td>
                            <td class="px-6 py-4 text-right font-black text-slate-700 dark:text-slate-200">${{ item.line_total }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="theme-table-actions">
                                    <Link :href="route('order-details.show', item.id)" class="theme-table-action-btn" :title="$t('View')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    </Link>
                                    <Link :href="route('order-details.edit', item.id)" class="theme-table-action-btn theme-table-action-edit" :title="$t('Edit')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </Link>
                                    <button @click="openDeleteModal(item.id)" class="theme-table-action-btn theme-table-action-delete" :title="$t('Delete')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="details.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-medium">{{ $t('No order items found.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="theme-table-footer flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">
                    Showing {{ details.from || 0 }} to {{ details.to || 0 }} of {{ details.total }} entries
                </div>
                <div class="flex flex-wrap justify-center items-center gap-1.5 mt-4 sm:mt-0">
                    <template v-for="(link, k) in details.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border transition-all" :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']" />
                        <span v-else v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold text-slate-300 bg-white border border-slate-100 rounded-lg cursor-not-allowed dark:text-slate-600 dark:bg-slate-800" />
                    </template>
                </div>
            </div>
        </div>

        <ConfirmModal :show="isModalOpen" title="Remove Item" message="Remove this item from the order?" @close="isModalOpen = false" @confirm="confirmDelete" />
    </AuthenticatedLayout>
</template>

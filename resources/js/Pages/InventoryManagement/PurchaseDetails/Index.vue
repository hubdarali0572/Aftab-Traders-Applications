<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ details: Object, purchases: Array, filters: Object });

const searchQuery = ref(props.filters?.search ?? '');
const purchaseId = ref(props.filters?.purchase_id ?? '');
const isModalOpen = ref(false);
const selectedId = ref(null);

const openDeleteModal = (id) => { selectedId.value = id; isModalOpen.value = true; };
const confirmDelete = () => {
    router.delete(route('purchase-details.destroy', selectedId.value), { onSuccess: () => { isModalOpen.value = false; } });
};

const applySearch = () => {
    router.get(route('purchase-details.index'), {
        search: searchQuery.value || undefined,
        purchase_id: purchaseId.value || undefined,
    }, { preserveState: true, replace: true });
};

const clearSearch = () => { searchQuery.value = ''; purchaseId.value = ''; applySearch(); };
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Purchase Line Items" />

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">Purchase Details</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">Manage products on purchase orders.</p>
            </div>
            <Link :href="route('purchase-details.create')" class="theme-btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 5v14m7-7H5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Add Line Item
            </Link>
        </div>

        <div class="theme-table-card">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 bg-slate-50/50">
                <form @submit.prevent="applySearch" class="flex flex-col md:flex-row gap-4">
                    <input v-model="searchQuery" type="text" class="theme-form-input flex-1" placeholder="Search by product or PO #..." />
                    <select v-model="purchaseId" class="theme-form-input w-full md:w-64">
                        <option value="">All Purchases</option>
                        <option v-for="p in purchases" :key="p.id" :value="p.id">{{ p.purchase_no }}</option>
                    </select>
                    <div class="flex gap-2">
                        <button type="submit" class="theme-btn-primary px-6 py-2.5">Filter</button>
                        <button v-if="filters?.search || filters?.purchase_id" type="button" @click="clearSearch" class="theme-form-back-link px-4 py-2.5">Clear</button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell">PO #</th>
                            <th class="theme-table-header-cell">Product</th>
                            <th class="theme-table-header-cell text-right">Qty</th>
                            <th class="theme-table-header-cell text-right">Free</th>
                            <th class="theme-table-header-cell text-right">Unit Price</th>
                            <th class="theme-table-header-cell text-right">Line Total</th>
                            <th class="theme-table-header-cell text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="item in details.data" :key="item.id" class="theme-table-row group">
                            <td class="px-6 py-4 font-bold text-indigo-600">{{ item.purchase?.purchase_no }}</td>
                            <td class="px-6 py-4 font-bold text-slate-800">{{ item.product?.name }}</td>
                            <td class="px-6 py-4 text-right">{{ item.quantity }}</td>
                            <td class="px-6 py-4 text-right">{{ item.free_quantity }}</td>
                            <td class="px-6 py-4 text-right">${{ item.unit_price }}</td>
                            <td class="px-6 py-4 text-right font-black">${{ item.line_total }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="theme-table-actions">
                                    <Link :href="route('purchase-details.show', item.id)" class="theme-table-action-btn" title="View"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg></Link>
                                    <Link :href="route('purchase-details.edit', item.id)" class="theme-table-action-btn theme-table-action-edit" title="Edit"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" stroke-linecap="round" stroke-linejoin="round"/></svg></Link>
                                    <button @click="openDeleteModal(item.id)" class="theme-table-action-btn theme-table-action-delete" title="Delete"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="details.data.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400 font-medium">No purchase line items found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="theme-table-footer flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">Showing {{ details.from || 0 }} to {{ details.to || 0 }} of {{ details.total }} entries</div>
                <div class="flex gap-1.5">
                    <template v-for="(link, k) in details.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border transition-all" :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']" />
                    </template>
                </div>
            </div>
        </div>

        <ConfirmModal :show="isModalOpen" title="Remove Line Item" message="Remove this item from the purchase? Stock will be reversed." @close="isModalOpen = false" @confirm="confirmDelete" />
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({ returns: Object, summary: Object, filters: Object });

const page = usePage();
const searchQuery = ref(props.filters?.search ?? '');
const isModalOpen = ref(false);
const selectedId = ref(null);
const showFlash = ref(false);
let flashTimer = null;

watch(
    () => [page.props.flash.success, page.props.flash.danger, page.props.flash.error],
    ([s, d, e]) => { if (s || d || e) { showFlash.value = true; clearTimeout(flashTimer); flashTimer = setTimeout(() => { showFlash.value = false; }, 5000); } },
    { immediate: true }
);

const flashMessage = computed(() => page.props.flash.success || page.props.flash.danger || page.props.flash.error);
const flashIsSuccess = computed(() => Boolean(page.props.flash.success));
const money = (v) => `$${Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const overviewStats = computed(() => [
    { title: 'Total Returns', value: props.summary?.total_returns ?? 0, tone: 'text-indigo-700 dark:text-indigo-300' },
    { title: 'Total Return Amount', value: money(props.summary?.total_return_amount), tone: 'text-emerald-700 dark:text-emerald-300' },
    { title: 'Total Return Quantity', value: props.summary?.total_return_quantity ?? 0, tone: 'text-violet-700 dark:text-violet-300' },
]);

const openDeleteModal = (id) => { selectedId.value = id; isModalOpen.value = true; };
const closeModal = () => { isModalOpen.value = false; setTimeout(() => { selectedId.value = null; }, 300); };
const confirmDelete = () => {
    if (selectedId.value) {
        router.delete(route('sale-returns.destroy', selectedId.value), { onSuccess: () => closeModal(), onFinish: () => closeModal() });
    }
};

const applySearch = () => {
    router.get(route('sale-returns.index'), { search: searchQuery.value || undefined }, { preserveState: true, replace: true });
};

const clearSearch = () => { searchQuery.value = ''; applySearch(); };
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Sales Returns')" />

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">{{ $t('Sales Returns') }}</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">{{ $t('Process returns with line items, stock restoration, and customer credits in one workflow.') }}</p>
            </div>
            <Link :href="route('sale-returns.create')" class="theme-btn-primary shrink-0 inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m7-7H5" /></svg>
                {{ $t('New Return') }}
            </Link>
        </div>

        <div v-if="showFlash && flashMessage" :class="[flashIsSuccess ? 'bg-indigo-50 border-indigo-500 text-indigo-800 dark:bg-indigo-500/10 dark:text-indigo-200' : 'bg-rose-50 border-rose-500 text-rose-800 dark:bg-rose-500/10 dark:text-rose-200']" class="mb-6 flex items-center p-4 border-l-4 rounded-r-xl shadow-sm">
            <p class="ml-3 text-sm font-bold">{{ flashMessage }}</p>
        </div>

        <div class="theme-table-card mb-6 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">{{ $t('Returns Dashboard') }}</h3>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                <div v-for="stat in overviewStats" :key="stat.title" class="p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800/50">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $t(stat.title) }}</p>
                    <p class="text-lg font-black mt-1" :class="stat.tone">{{ stat.value }}</p>
                </div>
            </div>
        </div>

        <div class="theme-table-card">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <form @submit.prevent="applySearch" class="flex flex-col sm:flex-row gap-3">
                    <input v-model="searchQuery" type="text" class="theme-form-input flex-1" :placeholder="$t('Search by return ref, invoice, or customer...')" />
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
                            <th class="theme-table-header-cell">{{ $t('Ref #') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Return Date') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Invoice') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Customer') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Warehouse') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Total Qty') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Total Amount') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="r in returns.data" :key="r.id" class="theme-table-row group">
                            <td class="px-6 py-3 font-bold text-indigo-600">{{ r.reference_no }}</td>
                            <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400">{{ r.return_date }}</td>
                            <td class="px-6 py-3 text-sm font-bold text-slate-700 dark:text-slate-300">{{ r.sale?.invoice_no }}</td>
                            <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400">{{ r.customer?.customer_name || $t('Walk-in') }}</td>
                            <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400">{{ r.warehouse?.name }}</td>
                            <td class="px-6 py-3 text-sm text-right font-bold">{{ r.total_quantity }}</td>
                            <td class="px-6 py-3 text-sm text-right font-bold">{{ money(r.total_amount) }}</td>
                            <td class="px-6 py-3 text-right whitespace-nowrap">
                                <div class="theme-table-actions">
                                    <Link :href="route('sale-returns.show', r.id)" class="theme-table-action-btn" :title="$t('View')"><svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg></Link>
                                    <Link :href="route('sale-returns.edit', r.id)" class="theme-table-action-btn theme-table-action-edit" :title="$t('Edit')"><svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" stroke-linecap="round" stroke-linejoin="round" /></svg></Link>
                                    <button @click="openDeleteModal(r.id)" class="theme-table-action-btn theme-table-action-delete" :title="$t('Delete')"><svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" /></svg></button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="returns.data.length === 0">
                            <td colspan="8" class="px-6 py-12 text-center text-slate-400 font-medium">{{ $t('No sales return records found.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="theme-table-footer flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest">{{ $t('Showing') }} {{ returns.from || 0 }} {{ $t('to') }} {{ returns.to || 0 }} {{ $t('of') }} {{ returns.total }} {{ $t('entries') }}</div>
                <div class="flex flex-wrap justify-center items-center gap-1.5 mt-4 sm:mt-0">
                    <template v-for="(link, k) in returns.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border transition-all duration-200" :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']" />
                        <span v-else v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold text-slate-300 bg-white border border-slate-100 rounded-lg cursor-not-allowed dark:text-slate-600 dark:bg-slate-800" />
                    </template>
                </div>
            </div>
        </div>

        <ConfirmModal :show="isModalOpen" :title="$t('Delete Sales Return')" :message="$t('Are you sure? Stock and customer ledger effects will be reversed.')" @close="closeModal" @confirm="confirmDelete" />
    </AuthenticatedLayout>
</template>

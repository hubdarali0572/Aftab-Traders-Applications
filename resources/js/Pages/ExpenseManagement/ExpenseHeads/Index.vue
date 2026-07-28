<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    expenseHeads: Object,
    filters: Object,
});

const page = usePage();
const showFlash = ref(false);
const searchQuery = ref(props.filters?.search ?? '');
let timer = null;

const startTimer = () => {
    showFlash.value = true;
    if (timer) clearTimeout(timer);
    timer = setTimeout(() => {
        showFlash.value = false;
    }, 5000);
};

watch(
    () => [page.props.flash.success, page.props.flash.danger],
    ([newSuccess, newDanger]) => {
        if (newSuccess || newDanger) {
            startTimer();
        }
    },
    { immediate: true }
);

const isModalOpen = ref(false);
const selectedHead = ref(null);

const openDeleteModal = (head) => {
    selectedHead.value = head;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    setTimeout(() => { selectedHead.value = null; }, 300);
};

const confirmDelete = () => {
    if (selectedHead.value) {
        router.delete(route('expense-heads.destroy', selectedHead.value.id), {
            onSuccess: () => closeModal(),
            onFinish: () => closeModal(),
        });
    }
};

const applySearch = () => {
    router.get(route('expense-heads.index'), { search: searchQuery.value || undefined }, {
        preserveState: true,
        replace: true,
    });
};

const clearSearch = () => {
    searchQuery.value = '';
    applySearch();
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Expense Heads" />

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">Expense Heads</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">Manage expense categories and heads.</p>
            </div>

            <Link :href="route('expense-heads.create')" class="theme-btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 5v14m7-7H5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Create Expense Head
            </Link>
        </div>

        <transition name="fade">
            <div v-if="showFlash && ($page.props.flash.success || $page.props.flash.danger)"
                :class="[$page.props.flash.success ? 'bg-indigo-50 border-indigo-500 text-indigo-800 dark:bg-indigo-500/10 dark:text-indigo-200' : 'bg-slate-100 border-slate-400 text-slate-700 dark:bg-slate-700/80 dark:text-slate-200']"
                class="mb-6 flex items-center p-4 border-l-4 rounded-r-xl shadow-sm"
            >
                <div class="flex-shrink-0">
                    <svg v-if="$page.props.flash.success" class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                    <svg v-else class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"/></svg>
                </div>
                <p class="ml-3 text-sm font-bold">{{ $page.props.flash.success || $page.props.flash.danger }}</p>
                <button @click="showFlash = false" class="ml-auto opacity-50 hover:opacity-100 transition-opacity">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
        </transition>

        <div class="theme-table-card">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <form @submit.prevent="applySearch" class="flex flex-col sm:flex-row gap-3">
                    <input
                        v-model="searchQuery"
                        type="text"
                        class="theme-form-input flex-1"
                        placeholder="Search by code, name or description..."
                    />
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
                            <th class="theme-table-header-cell">Code</th>
                            <th class="theme-table-header-cell">Name</th>
                            <th class="theme-table-header-cell">Description</th>
                            <th class="theme-table-header-cell">Status</th>
                            <th class="theme-table-header-cell text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="head in expenseHeads.data" :key="head.id" class="theme-table-row group">
                            <td class="px-6 py-2">
                                <div class="text-sm font-bold text-indigo-600 tracking-tight">{{ head.head_code }}</div>
                            </td>
                            <td class="px-6 py-2">
                                <div class="text-sm font-bold text-slate-800 tracking-tight dark:text-slate-100">{{ head.name }}</div>
                            </td>
                            <td class="px-6 py-2">
                                <div class="text-sm text-slate-600 dark:text-slate-300">{{ head.description || '—' }}</div>
                            </td>
                            <td class="px-6 py-2">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                    :class="head.status
                                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400'
                                        : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400'"
                                >
                                    {{ head.status ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap text-right">
                                <div class="theme-table-actions">
                                    <Link
                                        :href="route('expense-heads.show', head.id)"
                                        class="theme-table-action-btn"
                                        title="View"
                                    >
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </Link>
                                    <Link
                                        :href="route('expense-heads.edit', head.id)"
                                        class="theme-table-action-btn theme-table-action-edit"
                                        title="Edit"
                                    >
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </Link>
                                    <button
                                        @click="openDeleteModal(head)"
                                        class="theme-table-action-btn theme-table-action-delete"
                                        title="Delete"
                                    >
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="expenseHeads.data.length === 0">
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium dark:text-slate-500">No expense heads found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="theme-table-footer flex flex-col space-y-4 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest text-center sm:text-left dark:text-slate-200">
                    Showing <span class="text-slate-900 dark:text-slate-200">{{ expenseHeads.from || 0 }}</span> to <span class="text-slate-900 dark:text-slate-200">{{ expenseHeads.to || 0 }}</span> of <span class="text-slate-900 dark:text-slate-200">{{ expenseHeads.total }}</span> entries
                </div>
                <div class="flex flex-wrap justify-center items-center gap-1.5">
                    <template v-for="(link, k) in expenseHeads.links" :key="k">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border transition-all duration-200"
                            :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']"
                        />
                        <span v-else v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold text-slate-300 bg-white border border-slate-100 rounded-lg cursor-not-allowed dark:text-slate-600 dark:bg-slate-800 dark:border-slate-700" />
                    </template>
                </div>
            </div>
        </div>

        <ConfirmModal
            :show="isModalOpen"
            title="Delete Expense Head"
            message="Are you sure you want to permanently remove this expense head from the system?"
            confirm-label="Yes, Delete"
            cancel-label="No, Keep"
            :badge="selectedHead?.name"
            :badge-initial="selectedHead?.name?.slice(0, 1)"
            @close="closeModal"
            @confirm="confirmDelete"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ units: Object, filters: Object });
const page = usePage();
const showFlash = ref(false);
const searchQuery = ref(props.filters?.search ?? '');
let timer = null;

const startTimer = () => {
    showFlash.value = true;
    if (timer) clearTimeout(timer);
    timer = setTimeout(() => { showFlash.value = false; }, 5000);
};

watch(() => [page.props.flash.success, page.props.flash.danger], ([s, d]) => { if (s || d) startTimer(); }, { immediate: true });

const isModalOpen = ref(false);
const selectedUnit = ref(null);

const openDeleteModal = (unit) => { selectedUnit.value = unit; isModalOpen.value = true; };
const closeModal = () => { isModalOpen.value = false; setTimeout(() => { selectedUnit.value = null; }, 300); };
const confirmDelete = () => {
    if (selectedUnit.value) {
        router.delete(route('units.destroy', selectedUnit.value.id), { onSuccess: () => closeModal(), onFinish: () => closeModal() });
    }
};

const applySearch = () => {
    router.get(route('units.index'), { search: searchQuery.value || undefined }, { preserveState: true, replace: true });
};
const clearSearch = () => { searchQuery.value = ''; applySearch(); };
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Unit Management')" />
        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">{{ $t('Unit Management') }}</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">{{ $t('Manage and monitor system access and roles.') }}</p>
            </div>
            <Link :href="route('units.create')" class="theme-btn-primary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 5v14m7-7H5" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ $t('Create New Unit') }}
            </Link>
        </div>

        <transition name="fade">
            <div v-if="showFlash && ($page.props.flash.success || $page.props.flash.danger)" :class="[$page.props.flash.success ? 'bg-indigo-50 border-indigo-500 text-indigo-800 dark:bg-indigo-500/10 dark:text-indigo-200' : 'bg-slate-100 border-slate-400 text-slate-700 dark:bg-slate-700/80 dark:text-slate-200']" class="mb-6 flex items-center p-4 border-l-4 rounded-r-xl shadow-sm">
                <p class="ml-3 text-sm font-bold">{{ $page.props.flash.success || $page.props.flash.danger }}</p>
                <button @click="showFlash = false" class="ml-auto opacity-50 hover:opacity-100 transition-opacity"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
            </div>
        </transition>

        <div class="theme-table-card">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700">
                <form @submit.prevent="applySearch" class="flex flex-col sm:flex-row gap-3">
                    <input v-model="searchQuery" type="text" class="theme-form-input flex-1" placeholder="Search units..." />
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
                            <th class="theme-table-header-cell">{{ $t('Name') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Short Name') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Base Value') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Description') }}</th>
                            <th class="theme-table-header-cell">{{ $t('Status') }}</th>
                            <th class="theme-table-header-cell text-right">{{ $t('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="unit in units.data" :key="unit.id" class="theme-table-row group">
                            <td class="px-6 py-2"><div class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ unit.name }}</div></td>
                            <td class="px-6 py-2"><div class="text-sm text-slate-600 dark:text-slate-300">{{ unit.short_name }}</div></td>
                            <td class="px-6 py-2"><div class="text-sm text-slate-600 dark:text-slate-300">{{ unit.base_value }}</div></td>
                            <td class="px-6 py-2"><div class="text-sm text-slate-600 dark:text-slate-300">{{ unit.description || '—' }}</div></td>
                            <td class="px-6 py-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold" :class="unit.status ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400'">{{ unit.status ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="px-6 py-2 whitespace-nowrap text-right">
                                <div class="theme-table-actions">
                                    <Link :href="route('units.edit', unit.id)" class="theme-table-action-btn theme-table-action-edit" :title="$t('Edit Unit')"><svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" stroke-linecap="round" stroke-linejoin="round" /></svg></Link>
                                    <button @click="openDeleteModal(unit)" class="theme-table-action-btn theme-table-action-delete" title="Delete Unit"><svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" /></svg></button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="units.data.length === 0"><td colspan="6" class="px-6 py-12 text-center text-slate-400 font-medium dark:text-slate-500">{{ $t('No units found.') }}</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="theme-table-footer flex flex-col space-y-4 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest text-center sm:text-left dark:text-slate-200">{{ $t('Showing') }} <span class="text-slate-900 dark:text-slate-200">{{ units.from || 0 }}</span> to <span class="text-slate-900 dark:text-slate-200">{{ units.to || 0 }}</span> of <span class="text-slate-900 dark:text-slate-200">{{ units.total }}</span> {{ $t('entries') }}</div>
                <div class="flex flex-wrap justify-center items-center gap-1.5">
                    <template v-for="(link, k) in units.links" :key="k">
                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border transition-all duration-200" :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']" />
                        <span v-else v-html="link.label" class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold text-slate-300 bg-white border border-slate-100 rounded-lg cursor-not-allowed dark:text-slate-600 dark:bg-slate-800 dark:border-slate-700" />
                    </template>
                </div>
            </div>
        </div>

        <ConfirmModal :show="isModalOpen" title="Delete Unit" message="Are you sure you want to permanently remove this unit from the system?" confirm-label="Yes, Delete Unit" cancel-label="No, Keep Unit" :badge="selectedUnit?.name" :badge-initial="selectedUnit?.name?.slice(0, 1)" @close="closeModal" @confirm="confirmDelete" />
    </AuthenticatedLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.4s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

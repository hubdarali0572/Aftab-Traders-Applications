<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ConfirmModal from '@/Components/ConfirmModal.vue';
import { Head, Link, router, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from 'vue';

defineProps({
    activities: Object,
});

const page = usePage();
const showFlash = ref(false);
watch(() => page.props.flash?.success, (val) => { if (val) showFlash.value = true; }, { immediate: true });

const isDeleteModalOpen = ref(false);
const isClearModalOpen = ref(false);
const selectedLogId = ref(null);

const openDeleteModal = (id) => {
    selectedLogId.value = id;
    isDeleteModalOpen.value = true;
};

const closeModals = () => {
    isDeleteModalOpen.value = false;
    isClearModalOpen.value = false;
    setTimeout(() => { selectedLogId.value = null; }, 300);
};

const confirmDelete = () => {
    router.delete(route('activity.destroy', selectedLogId.value), {
        onSuccess: () => closeModals(),
        onFinish: () => closeModals(),
    });
};

const confirmClearAll = () => {
    router.delete(route('activity.clear'), {
        onSuccess: () => closeModals(),
        onFinish: () => closeModals(),
    });
};

const isModalOpen = computed(() => isDeleteModalOpen.value || isClearModalOpen.value);

const modalTitle = computed(() =>
    isDeleteModalOpen.value ? 'Delete Activity Log' : 'Clear All Activity Logs'
);

const modalMessage = computed(() =>
    isDeleteModalOpen.value
        ? 'Are you sure you want to permanently remove this activity log entry?'
        : 'Are you sure you want to permanently delete all system activity logs?'
);

const modalConfirmLabel = computed(() =>
    isDeleteModalOpen.value ? 'Yes, Delete Log' : 'Yes, Clear All'
);

const modalCancelLabel = computed(() =>
    isDeleteModalOpen.value ? 'No, Keep Log' : 'No, Keep Logs'
);

const modalBadge = computed(() =>
    isDeleteModalOpen.value && selectedLogId.value ? `Log ID: #${selectedLogId.value}` : null
);

const modalIcon = computed(() => (isClearModalOpen.value ? 'warning' : 'delete'));

const handleModalConfirm = () => {
    if (isDeleteModalOpen.value) {
        confirmDelete();
    } else {
        confirmClearAll();
    }
};

const formatDate = (date) => {
    return new Date(date).toLocaleString("en-US", {
        month: "short", day: "numeric", year: "numeric", hour: "2-digit", minute: "2-digit",
    });
};

const getModelName = (type) => {
    if (!type) {
        return "System";
    }

    return type.split("\\").pop();
};

const formatFieldLabel = (key) => {
    return key
        .replace(/_/g, ' ')
        .replace(/\b\w/g, (char) => char.toUpperCase());
};

const normalizeProperties = (log) => {
    const raw = log.properties;

    if (!raw) {
        return { old: {}, attributes: {} };
    }

    if (typeof raw === 'string') {
        try {
            return JSON.parse(raw);
        } catch {
            return { old: {}, attributes: {} };
        }
    }

    return raw;
};

const formatValue = (value) => {
    if (value === null || value === undefined || value === '') {
        return '—';
    }

    if (typeof value === 'boolean') {
        return value ? 'Yes' : 'No';
    }

    if (typeof value === 'object') {
        return JSON.stringify(value);
    }

    return String(value);
};

const getChangeDetails = (log) => {
    const properties = normalizeProperties(log);
    const oldValues = properties.old || {};
    const newValues = properties.attributes || {};
    const event = log.description || log.event;
    const hiddenFields = new Set(['password', 'remember_token']);

    const isVisibleField = (field) => !hiddenFields.has(field);

    if (event === 'created') {
        return Object.entries(newValues)
            .filter(([field]) => isVisibleField(field))
            .map(([field, value]) => ({
            field,
            label: formatFieldLabel(field),
            type: 'created',
            new: value,
        }));
    }

    if (event === 'deleted') {
        return Object.entries(oldValues)
            .filter(([field]) => isVisibleField(field))
            .map(([field, value]) => ({
            field,
            label: formatFieldLabel(field),
            type: 'deleted',
            old: value,
        }));
    }

    const fields = new Set([
        ...Object.keys(oldValues),
        ...Object.keys(newValues),
    ]);

    return [...fields]
        .filter(isVisibleField)
        .map((field) => ({
        field,
        label: formatFieldLabel(field),
        type: 'updated',
        old: oldValues[field],
        new: newValues[field],
        changed: oldValues[field] !== newValues[field],
    }));
};

const hasChangeDetails = (log) => getChangeDetails(log).length > 0;

const actionBadgeClass = (event) => {
    if (event === 'created') {
        return 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-200';
    }

    if (event === 'deleted') {
        return 'bg-slate-200 text-slate-700 dark:bg-slate-600 dark:text-slate-200';
    }

    return 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-200';
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Activity Logs" />

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">System Activity Logs</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">Detailed tracking of all system changes.</p>
            </div>

            <button
                @click="isClearModalOpen = true"
                class="theme-btn-secondary"
            >
                <svg
                    class="w-4 h-4 mr-2"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2.5"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                    />
                </svg>
                Clear All System Logs
            </button>
        </div>

        <div v-if="showFlash && $page.props.flash.success" class="mb-6 p-4 bg-indigo-50 border-l-4 border-indigo-500 text-indigo-800 text-sm font-bold rounded-r-xl shadow-sm flex justify-between items-center dark:bg-indigo-500/10 dark:text-indigo-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                {{ $page.props.flash.success }}
            </div>
            <button @click="showFlash = false" class="text-indigo-400 hover:text-indigo-900 text-xl dark:hover:text-indigo-100">×</button>
        </div>

        <div class="theme-table-card">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[1000px]">
                    <thead>
                        <tr class="theme-table-header">
                            <th class="theme-table-header-cell w-[12%]">User</th>
                            <th class="theme-table-header-cell w-[8%] text-center">Action</th>
                            <th class="theme-table-header-cell w-[12%]">Resource</th>
                            <th class="theme-table-header-cell w-[42%]">Change Details</th>
                            <th class="theme-table-header-cell w-[14%] text-right">Timestamp</th>
                            <th class="theme-table-header-cell w-[7%] text-center">Delete</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <tr v-for="log in activities.data" :key="log.id" class="theme-table-row align-top">
                            <td class="px-4 py-3">
                                <div class="text-xs font-bold text-slate-800 dark:text-slate-100">
                                    {{ log.causer?.name || "System" }}
                                </div>
                                <div v-if="log.causer?.email" class="mt-0.5 text-[10px] text-slate-500 dark:text-slate-400">
                                    {{ log.causer.email }}
                                </div>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <span
                                    class="inline-flex px-2 py-1 rounded text-[10px] font-black uppercase tracking-wide"
                                    :class="actionBadgeClass(log.description)"
                                >
                                    {{ log.description }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <div class="text-[11px] font-black text-slate-700 uppercase dark:text-slate-200">
                                    {{ getModelName(log.subject_type) }}
                                </div>
                                <div class="text-[10px] text-slate-400 font-medium dark:text-slate-500">
                                    ID #{{ log.subject_id || '—' }}
                                </div>
                                <div v-if="log.log_name" class="mt-1 text-[9px] uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                    {{ log.log_name }}
                                </div>
                            </td>

                            <td class="px-4 py-3">
                                <div v-if="hasChangeDetails(log)" class="space-y-2">
                                    <div
                                        v-for="change in getChangeDetails(log)"
                                        :key="`${log.id}-${change.field}`"
                                        class="rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-2 dark:border-slate-600 dark:bg-slate-700/40"
                                    >
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                            {{ change.label }}
                                        </p>

                                        <div v-if="change.type === 'updated'" class="mt-1 flex flex-wrap items-center gap-2 text-[11px]">
                                            <span class="rounded bg-white px-2 py-0.5 font-medium text-slate-500 line-through dark:bg-slate-800 dark:text-slate-400">
                                                {{ formatValue(change.old) }}
                                            </span>
                                            <svg class="h-3 w-3 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.5 4.5L21 12m0 0l-8.5 8.5M21 12H3" />
                                            </svg>
                                            <span class="rounded bg-indigo-50 px-2 py-0.5 font-bold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-200">
                                                {{ formatValue(change.new) }}
                                            </span>
                                        </div>

                                        <div v-else-if="change.type === 'created'" class="mt-1">
                                            <span class="inline-flex rounded bg-indigo-50 px-2 py-0.5 text-[11px] font-bold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-200">
                                                {{ formatValue(change.new) }}
                                            </span>
                                        </div>

                                        <div v-else-if="change.type === 'deleted'" class="mt-1">
                                            <span class="inline-flex rounded bg-slate-200 px-2 py-0.5 text-[11px] font-medium text-slate-700 line-through dark:bg-slate-600 dark:text-slate-200">
                                                {{ formatValue(change.old) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <span v-else class="text-[11px] text-slate-400 dark:text-slate-500">No detail recorded</span>
                            </td>

                            <td class="px-4 py-3 text-right text-[11px] font-bold text-slate-700 dark:text-slate-300">
                                {{ formatDate(log.created_at) }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                <div class="theme-table-actions justify-center">
                                    <button
                                        @click="openDeleteModal(log.id)"
                                        class="theme-table-action-btn theme-table-action-delete"
                                        title="Delete Log"
                                        aria-label="Delete Log"
                                    >
                                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="activities.data.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400 font-medium dark:text-slate-500">
                                No activity logs found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="theme-table-footer flex flex-col space-y-4 sm:space-y-0 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-[11px] font-bold text-indigo-700 uppercase tracking-widest text-center sm:text-left dark:text-slate-400">
                    Showing <span class="text-slate-900 dark:text-slate-200">{{ activities.from || 0 }}</span> to <span class="text-slate-900 dark:text-slate-200">{{ activities.to || 0 }}</span> of <span class="text-slate-900 dark:text-slate-200">{{ activities.total }}</span> logs
                </div>

                <div class="flex flex-wrap justify-center items-center gap-1.5">
                    <template v-for="(link, k) in activities.links" :key="k">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            v-html="link.label"
                            class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold rounded-lg border transition-all duration-200"
                            :class="[link.active ? 'theme-pagination-active' : 'theme-pagination-inactive']"
                        />
                        <span
                            v-else
                            v-html="link.label"
                            class="min-w-[30px] h-6 px-2 flex items-center justify-center text-xs font-bold text-slate-300 bg-white border border-slate-100 rounded-lg cursor-not-allowed dark:text-slate-600 dark:bg-slate-800 dark:border-slate-700"
                        />
                    </template>
                </div>
            </div>
        </div>

        <ConfirmModal
            :show="isModalOpen"
            :title="modalTitle"
            :message="modalMessage"
            :confirm-label="modalConfirmLabel"
            :cancel-label="modalCancelLabel"
            :badge="modalBadge"
            :icon="modalIcon"
            @close="closeModals"
            @confirm="handleModalConfirm"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    warehouse: { type: Object, required: true },
});

const fmt = (value, fallback = '—') =>
    value === null || value === undefined || value === '' ? fallback : value;

const fmtDate = (value) => {
    if (!value) return '—';
    return new Date(value).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};
</script>

<template>
    <Head :title="`Warehouse · ${warehouse.name}`" />
    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-black text-xl shrink-0">
                    {{ (warehouse.name || '?').slice(0, 1).toUpperCase() }}
                </div>
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">{{ warehouse.name }}</h2>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">Code: {{ warehouse.code }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <Link :href="route('warehouses.edit', warehouse.id)" class="theme-btn-primary px-6 py-3 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95">
                    Edit Warehouse
                </Link>
                <Link :href="route('warehouses.index')" class="theme-form-back-link">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="text-slate-900">Back to Warehouse List</span>
                </Link>
            </div>
        </div>

        <div class="max-w-8xl mx-auto pb-24 space-y-6">
            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <div class="flex flex-wrap items-center gap-3 mb-8">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-widest" :class="warehouse.status ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300'">
                            <span class="h-1.5 w-1.5 rounded-full" :class="warehouse.status ? 'bg-emerald-500' : 'bg-slate-400'" />
                            {{ warehouse.status ? 'Active' : 'Inactive' }}
                        </span>
                        <span v-if="warehouse.is_default" class="px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-widest bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">Default Warehouse</span>
                    </div>

                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">Basic Information</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-6">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Code</dt>
                            <dd class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100">{{ fmt(warehouse.code) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Name</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(warehouse.name) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">Contact Information</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-6">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Contact Person</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(warehouse.contact_person) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Phone</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(warehouse.phone) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Email</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(warehouse.email) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">Location</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-6">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">City</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(warehouse.city) }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Address</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(warehouse.address) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">Settings</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-6">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Default Warehouse</dt>
                            <dd class="mt-1">
                                <span class="text-sm font-bold" :class="warehouse.is_default ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'">{{ warehouse.is_default ? 'Yes' : 'No' }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Status</dt>
                            <dd class="mt-1">
                                <span class="text-sm font-bold" :class="warehouse.status ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'">{{ warehouse.status ? 'Active' : 'Inactive' }}</span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">Record Info</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-6">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Created At</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmtDate(warehouse.created_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Last Updated</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmtDate(warehouse.updated_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">Warehouse ID</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">#{{ warehouse.id }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
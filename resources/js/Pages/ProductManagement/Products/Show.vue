<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    product: { type: Object, required: true },
});

const fmt = (value, fallback = '—') =>
    value === null || value === undefined || value === '' ? fallback : value;

const fmtDate = (value) => {
    if (!value) return '—';
    return new Date(value).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const fmtNumber = (value, suffix = '') => {
    if (value === null || value === undefined || value === '') return '—';
    return `${value}${suffix}`;
};
</script>

<template>
    <Head :title="`Product · ${product.name}`" />
    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="h-14 w-14 rounded-2xl overflow-hidden bg-slate-100 dark:bg-slate-700 flex items-center justify-center shrink-0">
                    <img v-if="product.image" :src="product.image" :alt="product.name" class="h-full w-full object-cover" />
                    <svg v-else class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.75c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.75c0 .621.504 1.125 1.125 1.125z" /></svg>
                </div>
                <div>
                    <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">{{ product.name }}</h2>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">SKU: {{ product.sku }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <Link :href="route('products.edit', product.id)" class="theme-btn-primary px-6 py-3 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95">
                    {{ $t('Edit Product') }}
                </Link>
                <Link :href="route('products.index')" class="theme-form-back-link">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="text-slate-900">{{ $t('Back to Product List') }}</span>
                </Link>
            </div>
        </div>

        <div class="max-w-8xl mx-auto pb-24 space-y-6">
            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <div class="flex flex-wrap items-center gap-3 mb-8">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-widest" :class="product.status ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300'">
                            <span class="h-1.5 w-1.5 rounded-full" :class="product.status ? 'bg-emerald-500' : 'bg-slate-400'" />
                            {{ product.status ? 'Active' : 'Inactive' }}
                        </span>
                        <span v-if="product.track_stock" class="px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-widest bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">{{ $t('Tracked') }}</span>
                        <span v-if="product.has_expiry" class="px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-widest bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">{{ $t('Has Expiry') }}</span>
                        <span v-if="product.is_serialized" class="px-3 py-1.5 rounded-full text-xs font-black uppercase tracking-widest bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">{{ $t('Serialized') }}</span>
                    </div>

                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Basic Information') }}</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-6">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Name') }}</dt>
                            <dd class="mt-1 text-sm font-bold text-slate-800 dark:text-slate-100">{{ fmt(product.name) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Slug') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(product.slug) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('SKU') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(product.sku) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Barcode') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(product.barcode) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Category') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(product.category?.name) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Brand') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(product.brand?.name) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Unit') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">
                                {{ fmt(product.unit?.name) }}<span v-if="product.unit?.short_name"> ({{ product.unit.short_name }})</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Tax') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmtNumber(product.tax, '%') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Product Details') }}</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-6">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Model Number') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(product.model_number) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Manufacturer') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(product.manufacturer) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Color') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(product.color) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Size') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(product.size) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Weight') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmtNumber(product.weight, ' kg') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('HSN Code') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(product.hsn_code) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Origin Country') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmt(product.origin_country) }}</dd>
                        </div>
                        <div class="md:col-span-2 lg:col-span-3">
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Description') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300 leading-relaxed">{{ fmt(product.description) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Stock Settings') }}</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-6">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Minimum Stock') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmtNumber(product.minimum_stock) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Maximum Stock') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmtNumber(product.maximum_stock) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Track Stock') }}</dt>
                            <dd class="mt-1">
                                <span class="text-sm font-bold" :class="product.track_stock ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'">{{ product.track_stock ? 'Enabled' : 'Disabled' }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Has Expiry') }}</dt>
                            <dd class="mt-1">
                                <span class="text-sm font-bold" :class="product.has_expiry ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'">{{ product.has_expiry ? 'Yes' : 'No' }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Is Serialized') }}</dt>
                            <dd class="mt-1">
                                <span class="text-sm font-bold" :class="product.is_serialized ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'">{{ product.is_serialized ? 'Yes' : 'No' }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Status') }}</dt>
                            <dd class="mt-1">
                                <span class="text-sm font-bold" :class="product.status ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400'">{{ product.status ? 'Active' : 'Inactive' }}</span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Record Info') }}</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-6">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Created At') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmtDate(product.created_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Last Updated') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">{{ fmtDate(product.updated_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Product ID') }}</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-600 dark:text-slate-300">#{{ product.id }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
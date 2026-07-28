<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    productSellingPrice: { type: Object, required: true },
});

const formatPrice = (value) => value === null || value === undefined ? '—' : Number(value).toFixed(2);
const formatDate = (value) => value ?? '—';

const priceTiers = [
    { label: 'Retail Price', key: 'retail_price', highlight: true },
    { label: 'Wholesale Price', key: 'wholesale_price' },
    { label: 'Dealer Price', key: 'dealer_price' },
    { label: 'Distributor Price', key: 'distributor_price' },
    { label: 'Online Price', key: 'online_price' },
];
</script>

<template>
    <Head title="View Selling Price" />
    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">{{ $t('Selling Price Details') }}</h2>
            </div>
            <Link :href="route('product-selling-prices.index')" class="theme-form-back-link">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="text-slate-900">{{ $t('Back to Price List') }}</span>
            </Link>
        </div>

        <div class="max-w-8xl mx-auto pb-24 space-y-6">

            <!-- Hero / Summary -->
            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-black text-xl shrink-0">
                                {{ (productSellingPrice.product?.name || '?').slice(0, 1).toUpperCase() }}
                            </div>
                            <div>
                                <div class="text-xl font-black text-slate-800 dark:text-slate-100">{{ productSellingPrice.product?.name || 'Unknown Product' }}</div>
                                <div class="text-sm font-medium text-slate-500 dark:text-slate-400 mt-0.5">SKU: {{ productSellingPrice.product?.sku || '—' }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold" :class="productSellingPrice.status ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400'">
                                <span class="w-1.5 h-1.5 rounded-full mr-1.5" :class="productSellingPrice.status ? 'bg-emerald-500' : 'bg-slate-400'" />
                                {{ productSellingPrice.status ? 'Active' : 'Inactive' }}
                            </span>
                            <span v-if="productSellingPrice.is_default" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300">
                                {{ $t('Default Price') }}
                            </span>
                            <Link :href="route('product-selling-prices.edit', productSellingPrice.id)" class="theme-btn-primary ml-2">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                    <path d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                {{ $t('Edit') }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Tiers -->
            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Selling Prices') }}</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
                        <div v-for="tier in priceTiers" :key="tier.key"
                             class="rounded-2xl p-5 border transition-colors"
                             :class="tier.highlight
                                ? 'bg-indigo-600 border-indigo-600'
                                : 'bg-slate-50 border-slate-100 dark:bg-slate-800/60 dark:border-slate-700'">
                            <div class="text-[11px] font-bold uppercase tracking-widest"
                                 :class="tier.highlight ? 'text-indigo-100' : 'text-slate-400 dark:text-slate-500'">
                                {{ tier.label }}
                            </div>
                            <div class="text-2xl font-black mt-2"
                                 :class="tier.highlight ? 'text-white' : 'text-slate-800 dark:text-slate-100'">
                                {{ formatPrice(productSellingPrice[tier.key]) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Costing -->
                <div class="theme-form-card">
                    <div class="p-8">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Costing') }}</h3>
                        <dl class="divide-y divide-slate-100 dark:divide-slate-700">
                            <div class="flex items-center justify-between py-3">
                                <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $t('Purchase Price') }}</dt>
                                <dd class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ formatPrice(productSellingPrice.purchase_price) }}</dd>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $t('Landing Cost') }}</dt>
                                <dd class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ formatPrice(productSellingPrice.landing_cost) }}</dd>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $t('Cost Price') }}</dt>
                                <dd class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ formatPrice(productSellingPrice.cost_price) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Discount / Margin -->
                <div class="theme-form-card">
                    <div class="p-8">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Discount / Margin') }}</h3>
                        <dl class="divide-y divide-slate-100 dark:divide-slate-700">
                            <div class="flex items-center justify-between py-3">
                                <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $t('Minimum Selling Price') }}</dt>
                                <dd class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ formatPrice(productSellingPrice.minimum_selling_price) }}</dd>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $t('Maximum Discount') }}</dt>
                                <dd class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ productSellingPrice.maximum_discount !== null ? `${productSellingPrice.maximum_discount}%` : '—' }}</dd>
                            </div>
                            <div class="flex items-center justify-between py-3">
                                <dt class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $t('Profit Margin') }}</dt>
                                <dd class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ productSellingPrice.profit_margin !== null ? `${productSellingPrice.profit_margin}%` : '—' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <!-- Validity -->
            <div class="theme-form-card">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Validity') }}</h3>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                        <div class="flex-1 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700 p-5">
                            <div class="text-[11px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ $t('Effective From') }}</div>
                            <div class="text-lg font-black text-slate-800 dark:text-slate-100 mt-1">{{ formatDate(productSellingPrice.effective_from) }}</div>
                        </div>
                        <div class="text-slate-300 dark:text-slate-600 hidden sm:block">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <div class="flex-1 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700 p-5">
                            <div class="text-[11px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ $t('Effective To') }}</div>
                            <div class="text-lg font-black text-slate-800 dark:text-slate-100 mt-1">{{ formatDate(productSellingPrice.effective_to) }}</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
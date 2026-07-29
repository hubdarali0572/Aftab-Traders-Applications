<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

const props = defineProps({
    order: Object,
    summary: Object,
});

const money = (v) => `$${Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const formatDateTime = (d) => {
    if (!d) return '—';
    return new Date(d).toLocaleString(undefined, { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const formatDate = (d) => {
    if (!d) return '—';
    return new Date(d).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const statusConfig = (status) => ({
    pending: { badge: 'bg-amber-100 text-amber-800 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-500/30', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
    confirmed: { badge: 'bg-blue-100 text-blue-800 ring-blue-200 dark:bg-blue-500/15 dark:text-blue-300 dark:ring-blue-500/30', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    completed: { badge: 'bg-emerald-100 text-emerald-800 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300 dark:ring-emerald-500/30', icon: 'M5 13l4 4L19 7' },
    cancelled: { badge: 'bg-rose-100 text-rose-800 ring-rose-200 dark:bg-rose-500/15 dark:text-rose-300 dark:ring-rose-500/30', icon: 'M6 18L18 6M6 6l12 12' },
}[status] ?? { badge: 'bg-slate-100 text-slate-600 ring-slate-200', icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' });

const paymentStatus = computed(() => {
    const due = parseFloat(props.order.due_amount) || 0;
    const paid = parseFloat(props.order.paid_amount) || 0;
    const grand = parseFloat(props.order.grand_total) || 0;
    if (grand <= 0) return { label: 'No Amount', badge: 'bg-slate-100 text-slate-600 ring-slate-200', icon: 'M20 12H4' };
    if (due <= 0) return { label: 'Paid', badge: 'bg-emerald-100 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-300', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' };
    if (paid > 0) return { label: 'Partial', badge: 'bg-amber-100 text-amber-700 ring-amber-200 dark:bg-amber-500/20 dark:text-amber-300', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' };
    return { label: 'Unpaid', badge: 'bg-rose-100 text-rose-700 ring-rose-200 dark:bg-rose-500/20 dark:text-rose-300', icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' };
});

const quickStats = computed(() => [
    { label: 'Line Items', value: props.summary?.total_items ?? props.order.details?.length ?? 0, icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', tone: 'text-indigo-600', bg: 'bg-indigo-50 dark:bg-indigo-500/10' },
    { label: 'Total Quantity', value: props.summary?.total_quantity ?? 0, icon: 'M7 7h.01M7 3h5c.512 0 .953.352 1.07.857l1.08 4.858A2 2 0 0113.08 11H6.92a2 2 0 01-1.994-1.515L3.848 4.857A1 1 0 014.928 4H9m0 0V3', tone: 'text-sky-600', bg: 'bg-sky-50 dark:bg-sky-500/10' },
    { label: 'Grand Total', value: money(props.order.grand_total), icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', tone: 'text-emerald-600', bg: 'bg-emerald-50 dark:bg-emerald-500/10' },
    { label: 'Due Amount', value: money(props.order.due_amount), icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', tone: parseFloat(props.order.due_amount) > 0 ? 'text-rose-600' : 'text-emerald-600', bg: parseFloat(props.order.due_amount) > 0 ? 'bg-rose-50 dark:bg-rose-500/10' : 'bg-emerald-50 dark:bg-emerald-500/10' },
]);

const orderSummary = computed(() => [
    { label: 'Total Quantity', value: props.summary?.total_quantity ?? 0, icon: 'M7 7h.01M7 3h5c.512 0 .953.352 1.07.857l1.08 4.858A2 2 0 0113.08 11H6.92a2 2 0 01-1.994-1.515L3.848 4.857A1 1 0 014.928 4H9m0 0V3' },
    { label: 'Subtotal', value: money(props.order.subtotal), icon: 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z' },
    { label: 'Line Discount', value: money(props.summary?.line_discount ?? 0), icon: 'M7 7h.01M7 3h5c.512 0 .953.352 1.07.857l1.08 4.858A2 2 0 0113.08 11H6.92a2 2 0 01-1.994-1.515L3.848 4.857A1 1 0 014.928 4H9m0 0V3', tone: 'text-amber-600' },
    { label: 'Header Discount', value: money(props.order.discount), icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', tone: 'text-amber-600' },
    { label: 'Line Tax', value: money(props.summary?.line_tax ?? 0), icon: 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z', tone: 'text-sky-600' },
    { label: 'Header Tax', value: money(props.order.tax), icon: 'M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z', tone: 'text-sky-600' },
    { label: 'Other Charges', value: money(props.order.other_charges), icon: 'M12 6v6m0 0v6m0-6h6m-6 0H6' },
    { label: 'Grand Total', value: money(props.order.grand_total), icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', bold: true, highlight: true },
    { label: 'Paid Amount', value: money(props.order.paid_amount), icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z', tone: 'text-emerald-600', bold: true },
    { label: 'Due Amount', value: money(props.order.due_amount), icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', tone: parseFloat(props.order.due_amount) > 0 ? 'text-rose-600' : 'text-emerald-600', bold: true },
]);

const customerAddress = computed(() => {
    const parts = [props.order.customer?.address, props.order.customer?.city].filter(Boolean);
    return parts.length ? parts.join(', ') : '—';
});

const unitLabel = (item) => item.unit?.name || item.unit?.short_name || '—';

const printPage = () => window.print();

onMounted(() => {
    if (new URLSearchParams(window.location.search).get('print') === '1') {
        setTimeout(() => window.print(), 400);
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${order.order_no} — ${$t('Order')}`" />

        <!-- Hero header -->
        <div class="theme-table-card overflow-hidden mb-6 print:shadow-none">
            <div class="relative bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-800 px-6 py-8 md:px-10 md:py-10 text-white overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 blur-2xl pointer-events-none" />
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-violet-400/10 rounded-full translate-y-1/2 -translate-x-1/4 pointer-events-none" />

                <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                    <div class="flex items-start gap-5">
                        <div class="hidden sm:flex w-16 h-16 rounded-2xl bg-white/15 backdrop-blur-sm items-center justify-center ring-1 ring-white/20 shrink-0">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.25em] text-indigo-200 mb-2">{{ $t('Customer Order') }}</p>
                            <h1 class="text-3xl md:text-4xl font-black tracking-tight">{{ order.order_no }}</h1>
                            <div class="flex flex-wrap items-center gap-2 mt-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase ring-1 ring-inset bg-white/15 text-white">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="statusConfig(order.order_status).icon" /></svg>
                                    {{ order.order_status }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase ring-1 ring-inset bg-white/15 text-white">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="paymentStatus.icon" /></svg>
                                    {{ paymentStatus.label }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-white/10 text-indigo-100">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    {{ formatDateTime(order.created_at || order.order_date) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 print:hidden shrink-0">
                        <button type="button" @click="printPage" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-indigo-700 text-sm font-bold shadow-lg shadow-indigo-900/20 hover:bg-indigo-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            {{ $t('Print Order') }}
                        </button>
                        <Link :href="route('orders.edit', order.id)" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white/15 text-white text-sm font-bold ring-1 ring-white/25 hover:bg-white/25 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            {{ $t('Edit') }}
                        </Link>
                        <Link :href="route('orders-history.index')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white/10 text-white/90 text-sm font-bold hover:bg-white/20 transition-colors">
                            {{ $t('History') }}
                        </Link>
                        <Link :href="route('orders.index')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white/10 text-white/90 text-sm font-bold hover:bg-white/20 transition-colors">
                            {{ $t('Back') }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Quick stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-slate-100 dark:divide-slate-700 bg-white dark:bg-slate-800/50">
                <div v-for="stat in quickStats" :key="stat.label" class="flex items-center gap-4 px-5 py-5">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" :class="stat.bg">
                        <svg class="w-5 h-5" :class="stat.tone" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" :d="stat.icon" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 truncate">{{ $t(stat.label) }}</p>
                        <p class="text-lg font-black truncate" :class="stat.tone">{{ stat.value }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
            <div class="xl:col-span-2 space-y-6">
                <!-- Party & logistics cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Customer -->
                    <div class="theme-table-card p-5 md:col-span-1">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ $t('Customer') }}</h3>
                        </div>
                        <p class="text-base font-black text-slate-900 dark:text-white leading-tight">{{ order.customer?.customer_name || '—' }}</p>
                        <p v-if="order.customer?.customer_code" class="text-xs font-bold text-indigo-600 mt-1">{{ order.customer.customer_code }}</p>
                        <ul class="mt-4 space-y-2.5 text-sm">
                            <li v-if="order.customer?.company_name" class="flex items-start gap-2 text-slate-600 dark:text-slate-300">
                                <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                {{ order.customer.company_name }}
                            </li>
                            <li class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                                {{ order.customer?.phone || '—' }}
                            </li>
                            <li v-if="order.customer?.email" class="flex items-center gap-2 text-slate-600 dark:text-slate-300 truncate">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                <span class="truncate">{{ order.customer.email }}</span>
                            </li>
                            <li class="flex items-start gap-2 text-slate-500 text-xs">
                                <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                {{ customerAddress }}
                            </li>
                        </ul>
                    </div>

                    <!-- Warehouse -->
                    <div class="theme-table-card p-5">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                            <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ $t('Warehouse') }}</h3>
                        </div>
                        <p class="text-base font-black text-slate-900 dark:text-white">{{ order.warehouse?.name || '—' }}</p>
                        <p class="text-xs text-slate-500 mt-2 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                            {{ $t('Stock deducted from this warehouse when completed') }}
                        </p>
                    </div>

                    <!-- Processed by -->
                    <div class="theme-table-card p-5">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-violet-50 dark:bg-violet-500/10 flex items-center justify-center">
                                <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </div>
                            <h3 class="text-xs font-black uppercase tracking-widest text-slate-500">{{ $t('Processed By') }}</h3>
                        </div>
                        <p class="text-base font-black text-slate-900 dark:text-white">{{ order.user?.name || '—' }}</p>
                        <p v-if="order.user?.email" class="text-xs text-slate-500 mt-2 truncate">{{ order.user.email }}</p>
                        <div class="mt-4 pt-4 border-t border-slate-100 dark:border-slate-700 space-y-2 text-xs text-slate-500">
                            <p class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                {{ $t('Created') }}: {{ formatDateTime(order.created_at) }}
                            </p>
                            <p class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                {{ $t('Updated') }}: {{ formatDateTime(order.updated_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Status strip -->
                <div class="theme-table-card p-4 flex flex-wrap items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase text-slate-400">{{ $t('Order Status') }}</span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-black uppercase ring-1 ring-inset capitalize" :class="statusConfig(order.order_status).badge">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="statusConfig(order.order_status).icon" /></svg>
                            {{ order.order_status }}
                        </span>
                    </div>
                    <div class="w-px h-8 bg-slate-200 dark:bg-slate-700 hidden sm:block" />
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase text-slate-400">{{ $t('Payment Status') }}</span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-black uppercase ring-1 ring-inset" :class="paymentStatus.badge">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="paymentStatus.icon" /></svg>
                            {{ paymentStatus.label }}
                        </span>
                    </div>
                    <div class="w-px h-8 bg-slate-200 dark:bg-slate-700 hidden sm:block" />
                    <div class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span class="font-bold text-slate-700 dark:text-slate-200">{{ formatDate(order.order_date) }}</span>
                    </div>
                    <div v-if="order.remarks" class="w-full sm:w-auto sm:ml-auto flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300 italic max-w-md">
                        <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>
                        {{ order.remarks }}
                    </div>
                </div>

                <!-- Product table -->
                <div class="theme-table-card overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between gap-3 bg-slate-50/80 dark:bg-slate-800/50">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-indigo-100 dark:bg-indigo-500/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            </div>
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">{{ $t('Product Details') }}</h3>
                                <p class="text-[10px] text-slate-400">{{ summary?.total_items ?? 0 }} {{ $t('items') }} · {{ summary?.total_quantity ?? 0 }} {{ $t('units') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm min-w-[820px]">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell w-10">#</th>
                                    <th class="theme-table-header-cell">{{ $t('Product Name') }}</th>
                                    <th class="theme-table-header-cell">{{ $t('SKU') }}</th>
                                    <th class="theme-table-header-cell">{{ $t('Unit') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Qty') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Unit Price') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Discount') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Tax') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Line Total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="(item, i) in order.details" :key="item.id" class="theme-table-row hover:bg-indigo-50/30 dark:hover:bg-indigo-950/20 transition-colors">
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex w-7 h-7 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-700 text-xs font-black text-slate-500">{{ i + 1 }}</span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <p class="font-bold text-slate-800 dark:text-slate-100">{{ item.product?.name || '—' }}</p>
                                        <p v-if="item.remarks" class="text-[10px] text-slate-400 mt-0.5 italic">{{ item.remarks }}</p>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-700 font-mono text-[11px] text-slate-600 dark:text-slate-300">{{ item.product?.sku || '—' }}</span>
                                    </td>
                                    <td class="px-4 py-3.5 text-slate-600 font-medium">{{ unitLabel(item) }}</td>
                                    <td class="px-4 py-3.5 text-right font-black text-sky-700 dark:text-sky-300">{{ item.quantity }}</td>
                                    <td class="px-4 py-3.5 text-right font-medium">{{ money(item.unit_price) }}</td>
                                    <td class="px-4 py-3.5 text-right text-amber-600">{{ Number(item.discount) > 0 ? money(item.discount) : '—' }}</td>
                                    <td class="px-4 py-3.5 text-right text-sky-600">{{ Number(item.tax) > 0 ? money(item.tax) : '—' }}</td>
                                    <td class="px-4 py-3.5 text-right font-black text-indigo-600">{{ money(item.line_total) }}</td>
                                </tr>
                                <tr v-if="!order.details?.length">
                                    <td colspan="9" class="px-6 py-16 text-center">
                                        <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                        <p class="text-slate-400 font-medium">{{ $t('No products on this order.') }}</p>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot v-if="order.details?.length" class="bg-slate-50/80 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-700">
                                <tr>
                                    <td colspan="4" class="px-4 py-3 text-right text-xs font-black uppercase text-slate-400">{{ $t('Totals') }}</td>
                                    <td class="px-4 py-3 text-right font-black text-sky-700">{{ summary?.total_quantity ?? 0 }}</td>
                                    <td colspan="3" class="px-4 py-3" />
                                    <td class="px-4 py-3 text-right font-black text-indigo-600 text-base">{{ money(order.subtotal) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Linked returns -->
                <div v-if="order.order_returns?.length" class="theme-table-card overflow-hidden print:hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center gap-3 bg-violet-50/50 dark:bg-violet-950/20">
                        <div class="w-9 h-9 rounded-lg bg-violet-100 dark:bg-violet-500/20 flex items-center justify-center">
                            <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                        </div>
                        <h3 class="text-xs font-black uppercase tracking-widest text-violet-700 dark:text-violet-300">{{ $t('Linked Returns') }} ({{ order.order_returns.length }})</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell">{{ $t('Reference') }}</th>
                                    <th class="theme-table-header-cell">{{ $t('Date') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Qty') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Amount') }}</th>
                                    <th class="theme-table-header-cell">{{ $t('Status') }}</th>
                                    <th class="theme-table-header-cell text-right">{{ $t('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="ret in order.order_returns" :key="ret.id" class="theme-table-row">
                                    <td class="px-4 py-3 font-bold text-violet-600">{{ ret.reference_no }}</td>
                                    <td class="px-4 py-3 text-slate-600">{{ formatDate(ret.return_date) }}</td>
                                    <td class="px-4 py-3 text-right font-bold">{{ ret.total_quantity }}</td>
                                    <td class="px-4 py-3 text-right font-black text-emerald-600">{{ money(ret.total_amount) }}</td>
                                    <td class="px-4 py-3 capitalize text-xs font-bold text-slate-500">{{ ret.return_status }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <Link :href="route('order-returns.show', ret.id)" class="inline-flex items-center gap-1 text-xs font-bold text-indigo-600 hover:underline">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                            {{ $t('View') }}
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Summary sidebar -->
            <div class="xl:col-span-1">
                <div class="theme-table-card overflow-hidden xl:sticky xl:top-4 shadow-xl shadow-indigo-100/40 dark:shadow-none border-indigo-100 dark:border-indigo-900/40">
                    <div class="px-6 py-5 bg-gradient-to-br from-indigo-600 to-violet-700 text-white">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                            </div>
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-widest opacity-90">{{ $t('Order Summary') }}</h3>
                                <p class="text-2xl font-black mt-0.5">{{ money(order.grand_total) }}</p>
                            </div>
                        </div>
                    </div>
                    <dl class="p-4 space-y-1">
                        <div
                            v-for="line in orderSummary"
                            :key="line.label"
                            class="flex items-center justify-between gap-3 py-2.5 px-3 rounded-xl transition-colors"
                            :class="line.highlight ? 'bg-indigo-50 dark:bg-indigo-950/30 ring-1 ring-indigo-100 dark:ring-indigo-900/50' : 'hover:bg-slate-50 dark:hover:bg-slate-800/50'"
                        >
                            <dt class="flex items-center gap-2 text-xs font-bold text-slate-500 min-w-0">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" :d="line.icon" /></svg>
                                <span class="truncate">{{ $t(line.label) }}</span>
                            </dt>
                            <dd class="text-sm font-black text-right shrink-0" :class="[line.tone, line.bold ? 'text-base' : '']">{{ line.value }}</dd>
                        </div>
                    </dl>
                    <div class="p-4 pt-0 space-y-2 print:hidden border-t border-slate-100 dark:border-slate-700 mt-2">
                        <Link
                            v-if="order.order_status === 'completed'"
                            :href="route('order-returns.create', { order_id: order.id })"
                            class="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                            {{ $t('Create Return') }}
                        </Link>
                        <Link :href="route('orders.edit', order.id)" class="flex items-center justify-center gap-2 w-full py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-sm font-bold text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-950/30 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            {{ $t('Edit Order') }}
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@media print {
    :deep(.print\:hidden) { display: none !important; }
    :deep(.print\:shadow-none) { box-shadow: none !important; }
}
</style>

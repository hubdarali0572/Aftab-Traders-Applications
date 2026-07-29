<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

const props = defineProps({
    expense: Object,
    summary: Object,
});

const money = (v) => `$${Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const formatDate = (value) => {
    if (!value) return '—';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return value;
    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const formatDateTime = (value) => {
    if (!value) return '—';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return value;
    return d.toLocaleString(undefined, { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
};

const statusConfig = (status) => ({
    draft: { badge: 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-500/15 dark:text-slate-300', icon: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' },
    approved: { badge: 'bg-sky-100 text-sky-800 ring-sky-200 dark:bg-sky-500/15 dark:text-sky-300', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    paid: { badge: 'bg-emerald-100 text-emerald-800 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-300', icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' },
    cancelled: { badge: 'bg-rose-100 text-rose-800 ring-rose-200 dark:bg-rose-500/15 dark:text-rose-300', icon: 'M6 18L18 6M6 6l12 12' },
}[status] ?? { badge: 'bg-slate-100 text-slate-600 ring-slate-200', icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' });

const paymentConfig = (method) => ({
    cash: { badge: 'bg-emerald-50 text-emerald-700 ring-emerald-200', icon: 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z' },
    bank: { badge: 'bg-indigo-50 text-indigo-700 ring-indigo-200', icon: 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z' },
    cheque: { badge: 'bg-amber-50 text-amber-700 ring-amber-200', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    online: { badge: 'bg-violet-50 text-violet-700 ring-violet-200', icon: 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9' },
}[method] ?? { badge: 'bg-slate-50 text-slate-600 ring-slate-200', icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z' });

const quickStats = computed(() => [
    { label: 'Amount', value: money(props.expense.amount), icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', tone: 'text-indigo-600', bg: 'bg-indigo-50 dark:bg-indigo-500/10' },
    { label: 'Expense Name', value: props.expense.expense_name || '—', icon: 'M7 7h.01M7 3h5c.512 0 .953.352 1.07.857l1.08 4.858A2 2 0 0113.08 11H6.92a2 2 0 01-1.994-1.515L3.848 4.857A1 1 0 014.928 4H9m0 0V3', tone: 'text-indigo-600', bg: 'bg-indigo-50 dark:bg-indigo-500/10' },
    { label: 'Payment', value: props.expense.payment_method, icon: paymentConfig(props.expense.payment_method).icon, tone: 'text-sky-600', bg: 'bg-sky-50 dark:bg-sky-500/10' },
    { label: 'Financial Impact', value: props.summary?.counts_toward_financials ? 'Included in P&L' : 'Not in P&L', icon: props.summary?.counts_toward_financials ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', tone: props.summary?.counts_toward_financials ? 'text-emerald-600' : 'text-amber-600', bg: props.summary?.counts_toward_financials ? 'bg-emerald-50 dark:bg-emerald-500/10' : 'bg-amber-50 dark:bg-amber-500/10' },
]);

const detailCards = computed(() => [
    { label: 'Warehouse', value: props.expense.warehouse?.name || 'Company-wide', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', tone: 'text-violet-600', bg: 'bg-violet-50 dark:bg-violet-500/10' },
    { label: 'Recorded By', value: props.expense.user?.name || '—', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', tone: 'text-sky-600', bg: 'bg-sky-50 dark:bg-sky-500/10' },
]);

const printPage = () => window.print();

onMounted(() => {
    if (new URLSearchParams(window.location.search).get('print') === '1') {
        setTimeout(() => window.print(), 400);
    }
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${expense.expense_no} — ${$t('Expense')}`" />

        <!-- Hero header -->
        <div class="theme-table-card overflow-hidden mb-6 print:shadow-none">
            <div class="relative bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-800 px-6 py-8 md:px-10 md:py-10 text-white overflow-hidden">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3 blur-2xl pointer-events-none" />
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-violet-400/10 rounded-full translate-y-1/2 -translate-x-1/4 pointer-events-none" />

                <div class="relative flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                    <div class="flex items-start gap-5">
                        <div class="hidden sm:flex w-16 h-16 rounded-2xl bg-white/15 backdrop-blur-sm items-center justify-center ring-1 ring-white/20 shrink-0">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-[11px] font-black uppercase tracking-[0.25em] text-indigo-200 mb-2">{{ $t('Operating Expense') }}</p>
                            <h1 class="text-3xl md:text-4xl font-black tracking-tight">{{ expense.expense_no }}</h1>
                            <p class="text-indigo-100/80 mt-2 font-medium">{{ expense.expense_name }}</p>
                            <div class="flex flex-wrap items-center gap-2 mt-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase ring-1 ring-inset bg-white/15 text-white capitalize">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="statusConfig(expense.status).icon" /></svg>
                                    {{ expense.status }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-black uppercase ring-1 ring-inset bg-white/15 text-white capitalize">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="paymentConfig(expense.payment_method).icon" /></svg>
                                    {{ expense.payment_method }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold bg-white/10 text-indigo-100">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    {{ formatDate(expense.expense_date) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2 print:hidden shrink-0">
                        <button type="button" @click="printPage" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-indigo-700 text-sm font-bold shadow-lg shadow-indigo-900/20 hover:bg-indigo-50 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                            {{ $t('Print') }}
                        </button>
                        <Link :href="route('expenses.edit', expense.id)" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white/15 text-white text-sm font-bold ring-1 ring-white/25 hover:bg-white/25 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                            {{ $t('Edit') }}
                        </Link>
                        <Link :href="route('expense-history.index')" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white/10 text-white text-sm font-bold ring-1 ring-white/20 hover:bg-white/20 transition-colors">
                            {{ $t('Expense History') }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Quick stats -->
            <div class="grid grid-cols-2 lg:grid-cols-4 divide-x divide-y lg:divide-y-0 divide-slate-100 dark:divide-slate-700 bg-white dark:bg-slate-800/50">
                <div v-for="stat in quickStats" :key="stat.label" class="p-5 flex items-center gap-4">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" :class="stat.bg">
                        <svg class="w-5 h-5" :class="stat.tone" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="stat.icon" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ $t(stat.label) }}</p>
                        <p class="text-sm font-black text-slate-800 dark:text-slate-100 truncate capitalize">{{ stat.value }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <div class="xl:col-span-2 space-y-6">
                <!-- Party cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div v-for="card in detailCards" :key="card.label" class="theme-form-card p-6">
                        <div class="flex items-start gap-4">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0" :class="card.bg">
                                <svg class="w-5 h-5" :class="card.tone" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon" /></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-black uppercase tracking-wider text-slate-400">{{ $t(card.label) }}</p>
                                <p class="text-sm font-black text-slate-800 dark:text-slate-100 mt-1">{{ card.value }}</p>
                                <p v-if="card.sub" class="text-xs text-slate-400 mt-0.5">{{ card.sub }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Details grid -->
                <div class="theme-form-card p-8">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-6 flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ $t('Expense Details') }}
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6">
                        <div>
                            <dt class="text-[10px] font-bold text-slate-400 uppercase">{{ $t('Employee') }}</dt>
                            <dd class="font-bold text-slate-700 dark:text-slate-200 mt-1">{{ expense.employee_name || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold text-slate-400 uppercase">{{ $t('Payee') }}</dt>
                            <dd class="font-bold text-slate-700 dark:text-slate-200 mt-1">{{ expense.payee_name || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold text-slate-400 uppercase">{{ $t('Reference No') }}</dt>
                            <dd class="font-bold text-slate-700 dark:text-slate-200 mt-1">{{ expense.reference_no || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold text-slate-400 uppercase">{{ $t('Invoice No') }}</dt>
                            <dd class="font-bold text-slate-700 dark:text-slate-200 mt-1">{{ expense.invoice_no || '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold text-slate-400 uppercase">{{ $t('Created At') }}</dt>
                            <dd class="font-bold text-slate-700 dark:text-slate-200 mt-1">{{ formatDateTime(expense.created_at) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[10px] font-bold text-slate-400 uppercase">{{ $t('Status') }}</dt>
                            <dd class="mt-1">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold capitalize ring-1 ring-inset" :class="statusConfig(expense.status).badge">
                                    {{ expense.status }}
                                </span>
                            </dd>
                        </div>
                    </div>
                </div>

                <div v-if="expense.description" class="theme-form-card p-8">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-4">{{ $t('Description') }}</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">{{ expense.description }}</p>
                </div>

                <div v-if="expense.remarks" class="theme-form-card p-8">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-4">{{ $t('Remarks') }}</h3>
                    <p class="text-sm text-slate-600 italic dark:text-slate-300 leading-relaxed">"{{ expense.remarks }}"</p>
                </div>
            </div>

            <!-- Summary sidebar -->
            <div class="space-y-6">
                <div class="theme-form-card p-8 sticky top-6">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-6">{{ $t('Amount Summary') }}</h3>
                    <div class="text-center py-6 rounded-2xl bg-gradient-to-br from-indigo-50 to-violet-50 dark:from-indigo-900/20 dark:to-violet-900/10 border border-indigo-100 dark:border-indigo-800/30 mb-6">
                        <p class="text-[10px] font-black uppercase tracking-widest text-indigo-500 mb-2">{{ $t('Total Amount') }}</p>
                        <p class="text-4xl font-black text-indigo-700 dark:text-indigo-300">{{ money(expense.amount) }}</p>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-700">
                            <span class="text-sm text-slate-500">{{ $t('Payment Method') }}</span>
                            <span class="text-sm font-bold capitalize text-slate-800 dark:text-slate-100">{{ expense.payment_method }}</span>
                        </div>
                        <div class="flex items-center justify-between py-3 border-b border-slate-100 dark:border-slate-700">
                            <span class="text-sm text-slate-500">{{ $t('Expense Date') }}</span>
                            <span class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ formatDate(expense.expense_date) }}</span>
                        </div>
                        <div class="flex items-center justify-between py-3">
                            <span class="text-sm text-slate-500">{{ $t('P&L Impact') }}</span>
                            <span class="text-xs font-black uppercase px-2 py-1 rounded-full" :class="summary?.counts_toward_financials ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                                {{ summary?.counts_toward_financials ? $t('Included') : $t('Excluded') }}
                            </span>
                        </div>
                    </div>
                    <p class="text-[10px] text-slate-400 mt-6 leading-relaxed">{{ $t('Only approved and paid expenses are included in profit & loss and dashboard calculations.') }}</p>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

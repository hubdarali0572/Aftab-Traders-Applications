<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ expense: Object });

const money = (v) => Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const formatDate = (value) => {
    if (!value) return '—';
    const d = new Date(value);
    if (Number.isNaN(d.getTime())) return value;
    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
};

const statusClass = (status) => ({
    draft: 'bg-slate-100 text-slate-600',
    approved: 'bg-sky-100 text-sky-700',
    paid: 'bg-emerald-100 text-emerald-700',
    cancelled: 'bg-rose-100 text-rose-700',
}[status] || 'bg-slate-100 text-slate-500');
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Expense Details" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ expense.expense_no }}</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium">{{ expense.expense_head?.name }}</p>
            </div>
            <div class="flex gap-3">
                <Link :href="route('expenses.edit', expense.id)" class="theme-btn-primary px-6 py-2">Edit</Link>
                <Link :href="route('expenses.index')" class="theme-form-back-link">Back</Link>
            </div>
        </div>

        <div class="space-y-6">
            <div class="theme-form-card p-10 bg-emerald-50 dark:bg-emerald-900/10 text-center border-emerald-100">
                <dt class="text-xs font-black uppercase text-emerald-400 mb-2">Amount</dt>
                <dd class="text-4xl font-black text-emerald-700">${{ money(expense.amount) }}</dd>
            </div>

            <div class="theme-form-card p-10">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">Expense No</dt>
                        <dd class="font-bold text-indigo-600 mt-1">{{ expense.expense_no }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">Date</dt>
                        <dd class="font-bold text-slate-700 dark:text-slate-200 mt-1">{{ formatDate(expense.expense_date) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold capitalize" :class="statusClass(expense.status)">
                                {{ expense.status }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">Expense Head</dt>
                        <dd class="font-bold text-slate-700 dark:text-slate-200 mt-1">{{ expense.expense_head?.name || '—' }}</dd>
                        <dd class="text-xs text-slate-400">{{ expense.expense_head?.head_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">Warehouse</dt>
                        <dd class="font-bold text-slate-700 dark:text-slate-200 mt-1">{{ expense.warehouse?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">Payment Method</dt>
                        <dd class="font-bold text-slate-700 dark:text-slate-200 mt-1 capitalize">{{ expense.payment_method }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">Employee</dt>
                        <dd class="font-bold text-slate-700 dark:text-slate-200 mt-1">{{ expense.employee_name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">Payee</dt>
                        <dd class="font-bold text-slate-700 dark:text-slate-200 mt-1">{{ expense.payee_name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">Recorded By</dt>
                        <dd class="font-bold text-slate-700 dark:text-slate-200 mt-1">{{ expense.user?.name || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">Reference No</dt>
                        <dd class="font-bold text-slate-700 dark:text-slate-200 mt-1">{{ expense.reference_no || '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">Invoice No</dt>
                        <dd class="font-bold text-slate-700 dark:text-slate-200 mt-1">{{ expense.invoice_no || '—' }}</dd>
                    </div>
                </div>
            </div>

            <div class="theme-form-card p-8" v-if="expense.description">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-4">Description</h3>
                <p class="text-sm text-slate-600 dark:text-slate-300">{{ expense.description }}</p>
            </div>

            <div class="theme-form-card p-8" v-if="expense.remarks">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-4">Remarks</h3>
                <p class="text-sm text-slate-600 italic dark:text-slate-300">"{{ expense.remarks }}"</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ expense: Object });
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Purchase Expense" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">{{ expense.expense_type }}</h2>
            <div class="flex gap-3">
                <Link :href="route('purchase-expenses.edit', expense.id)" class="theme-btn-primary px-6 py-2">{{ $t('Edit') }}</Link>
                <Link :href="route('purchase-expenses.index')" class="theme-form-back-link">{{ $t('Back') }}</Link>
            </div>
        </div>

        <div class="space-y-6">
            <div class="theme-form-card p-10">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Purchase') }}</dt><dd class="font-bold text-indigo-600">{{ expense.purchase?.purchase_no }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Expense Type') }}</dt><dd class="font-bold text-slate-700">{{ expense.expense_type }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Recorded By') }}</dt><dd class="font-bold text-slate-700">{{ expense.user?.name || '—' }}</dd></div>
                </div>
            </div>

            <div class="theme-form-card p-10 bg-emerald-50 dark:bg-emerald-900/10 text-center border-emerald-100">
                <dt class="text-xs font-black uppercase text-emerald-400 mb-2">{{ $t('Amount') }}</dt>
                <dd class="text-4xl font-black text-emerald-700">${{ expense.amount }}</dd>
            </div>

            <div class="theme-form-card p-8" v-if="expense.remarks">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-4">{{ $t('Remarks') }}</h3>
                <p class="text-sm text-slate-600 italic">"{{ expense.remarks }}"</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

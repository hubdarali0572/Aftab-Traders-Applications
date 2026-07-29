<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    expenseHead: Object,
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Expense Head')" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ expenseHead.name }}</h2>
                <p class="text-sm text-slate-500 mt-1 font-medium">{{ expenseHead.head_code }}</p>
            </div>
            <div class="flex gap-3">
                <Link :href="route('expense-heads.edit', expenseHead.id)" class="theme-btn-primary px-6 py-2">{{ $t('Edit') }}</Link>
                <Link :href="route('expenses.index')" class="theme-form-back-link">{{ $t('Back to Expenses') }}</Link>
            </div>
        </div>

        <div class="space-y-6">
            <div class="theme-form-card p-10">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Head Code') }}</dt>
                        <dd class="font-bold text-indigo-600 mt-1">{{ expenseHead.head_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Name') }}</dt>
                        <dd class="font-bold text-slate-700 dark:text-slate-200 mt-1">{{ expenseHead.name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Status') }}</dt>
                        <dd class="mt-1">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold"
                                :class="expenseHead.status ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
                            >
                                {{ expenseHead.status ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Linked Expenses') }}</dt>
                        <dd class="font-bold text-slate-700 dark:text-slate-200 mt-1">{{ expenseHead.expenses_count ?? 0 }}</dd>
                    </div>
                </div>
            </div>

            <div class="theme-form-card p-8" v-if="expenseHead.description">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-4">{{ $t('Description') }}</h3>
                <p class="text-sm text-slate-600 dark:text-slate-300">{{ expenseHead.description }}</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

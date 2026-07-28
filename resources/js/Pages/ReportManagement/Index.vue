<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    groups: { type: Array, default: () => [] },
});
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Report Management')" />

        <div class="mb-8">
            <h2 class="text-2xl font-black text-slate-700 tracking-tight dark:text-slate-100">{{ $t('Report Management') }}</h2>
            <p class="text-sm text-slate-500 mt-1 font-medium dark:text-slate-400">
                {{ $t('Sales, inventory, customer, and financial reports for Decoration Lights wholesale & retail.') }}
            </p>
        </div>

        <div class="space-y-8">
            <section v-for="group in groups" :key="group.title" class="space-y-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider dark:text-slate-100">{{ group.title }}</h3>
                    <p class="mt-1 text-xs text-slate-400">{{ group.description }}</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    <Link
                        v-for="item in group.items"
                        :key="item.route"
                        :href="route(item.route)"
                        class="theme-form-card p-6 hover:-translate-y-0.5 hover:shadow-md transition-all border border-slate-100 dark:border-slate-700 hover:border-indigo-200 dark:hover:border-indigo-500/40"
                    >
                        <h4 class="text-sm font-black text-slate-800 dark:text-slate-100">{{ item.name }}</h4>
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{{ item.desc }}</p>
                        <span class="mt-4 inline-flex text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                            Open report →
                        </span>
                    </Link>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

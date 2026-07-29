<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    featured: { type: Array, default: () => [] },
    moreGroups: { type: Array, default: () => [] },
    historyLinks: { type: Array, default: () => [] },
});

const showMore = ref(false);
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Reports')" />

        <div class="max-w-8xl mx-auto space-y-8">
            <!-- Hero -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-900 via-indigo-950 to-violet-900 p-8 lg:p-10 text-white shadow-xl">
                <div class="relative z-10">
                    <p class="text-[11px] font-black uppercase tracking-widest text-indigo-300">
                        {{ $t('Report Management') }}
                    </p>
                    <h1 class="mt-2 text-2xl lg:text-3xl font-black tracking-tight">
                        {{ $t('Essential Business Reports') }}
                    </h1>
                    <p class="mt-3 text-sm text-white/70 max-w-xl leading-relaxed">
                        {{ $t('Five key reports for daily decisions — sales, stock, receivables and profit. More reports available below when needed.') }}
                    </p>
                </div>
                <div class="absolute -right-16 -top-16 h-64 w-64 rounded-full bg-indigo-500/20 blur-3xl"></div>
            </div>

            <!-- Featured reports — 5 essential -->
            <section class="space-y-4">
                <div>
                    <h2 class="text-sm font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">
                        {{ $t('Key Reports') }}
                    </h2>
                    <p class="mt-1 text-xs text-slate-400">{{ $t('Most used reports for your daily operations') }}</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Link
                        v-for="report in featured"
                        :key="report.route"
                        :href="route(report.route)"
                        class="group relative overflow-hidden rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg hover:border-indigo-200 dark:border-slate-700 dark:bg-slate-800 dark:hover:border-indigo-500/40"
                    >
                        <div class="flex items-start gap-4">
                            <div
                                class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-md transition-transform group-hover:scale-105"
                                :class="report.tone"
                            >
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="report.icon" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <span class="inline-block rounded-full bg-indigo-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                                    {{ report.badge }}
                                </span>
                                <h3 class="mt-2 text-base font-black text-slate-800 group-hover:text-indigo-700 dark:text-slate-100 dark:group-hover:text-indigo-300">
                                    {{ report.name }}
                                </h3>
                                <p class="mt-1.5 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                                    {{ report.desc }}
                                </p>
                            </div>
                        </div>
                        <div class="mt-4 flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider text-indigo-600 opacity-0 transition-opacity group-hover:opacity-100 dark:text-indigo-400">
                            {{ $t('Open report') }}
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </Link>
                </div>
            </section>

            <!-- More reports — collapsed -->
            <section class="theme-table-card overflow-hidden">
                <button
                    type="button"
                    class="w-full flex items-center justify-between gap-4 px-6 py-4 text-left bg-gradient-to-r from-slate-50 to-white dark:from-slate-800 dark:to-slate-800/80 hover:from-indigo-50/50 dark:hover:from-indigo-950/20 transition-colors"
                    @click="showMore = !showMore"
                >
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-widest text-slate-600 dark:text-slate-300">
                            {{ $t('More Reports') }}
                        </h2>
                        <p class="mt-0.5 text-xs text-slate-400">{{ $t('Monthly sales, product-wise, stock movement and ledger') }}</p>
                    </div>
                    <svg
                        class="h-5 w-5 shrink-0 text-slate-400 transition-transform"
                        :class="showMore ? 'rotate-180' : ''"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div v-show="showMore" class="border-t border-slate-100 dark:border-slate-700 divide-y divide-slate-100 dark:divide-slate-700">
                    <div v-for="group in moreGroups" :key="group.title" class="p-6">
                        <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-3">{{ group.title }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            <Link
                                v-for="item in group.items"
                                :key="item.route"
                                :href="route(item.route)"
                                class="group flex flex-col rounded-xl border border-slate-100 p-4 transition-all hover:border-indigo-200 hover:bg-indigo-50/30 dark:border-slate-700 dark:hover:border-indigo-500/30 dark:hover:bg-indigo-500/5"
                            >
                                <span class="text-sm font-bold text-slate-800 group-hover:text-indigo-700 dark:text-slate-100">{{ item.name }}</span>
                                <span class="mt-1 text-xs text-slate-400">{{ item.desc }}</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- History links -->
            <section class="space-y-3">
                <h2 class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $t('Transaction History') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <Link
                        v-for="link in historyLinks"
                        :key="link.route"
                        :href="route(link.route)"
                        class="group flex items-center gap-3 rounded-xl border border-slate-100 bg-white px-5 py-4 transition-all hover:border-indigo-200 hover:shadow-sm dark:border-slate-700 dark:bg-slate-800"
                    >
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="link.icon" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ link.name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ link.desc }}</p>
                        </div>
                    </Link>
                </div>
            </section>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ adjustment: Object });
const fmtDate = (v) => v ? new Date(v).toLocaleDateString() : '—';
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Adjustment Details" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">{{ adjustment.reference_no }}</h2>
            <div class="flex gap-3">
                <Link :href="route('stock-adjustments.edit', adjustment.id)" class="theme-btn-primary px-6 py-2 rounded-full">Edit</Link>
                <Link :href="route('stock-adjustments.index')" class="theme-form-back-link">Back</Link>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Information Card -->
            <div class="theme-form-card p-10">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Warehouse</dt><dd class="font-bold">{{ adjustment.warehouse?.name }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Adjustment Date</dt><dd class="font-bold text-slate-700">{{ adjustment.adjustment_date }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Type</dt><dd class="font-bold uppercase text-indigo-600">{{ adjustment.adjustment_type }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Created By</dt><dd class="font-bold text-slate-700">{{ adjustment.user?.name }}</dd></div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="theme-form-card p-10 bg-indigo-50 dark:bg-indigo-900/10 text-center border-indigo-100 dark:border-indigo-800">
                    <dt class="text-xs font-black uppercase text-indigo-400 mb-2">Total Quantity</dt>
                    <dd class="text-4xl font-black text-indigo-700 dark:text-indigo-300">{{ adjustment.total_quantity }}</dd>
                </div>
                <div class="theme-form-card p-10 bg-emerald-50 dark:bg-emerald-900/10 text-center border-emerald-100 dark:border-emerald-800">
                    <dt class="text-xs font-black uppercase text-emerald-400 mb-2">Total Amount</dt>
                    <dd class="text-4xl font-black text-emerald-700 dark:text-emerald-300">${{ adjustment.total_amount }}</dd>
                </div>
            </div>

            <!-- Added Remarks Display Card -->
            <div class="theme-form-card" v-if="adjustment.remarks">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4">Remarks</h3>
                    <div class="text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl border border-slate-100 dark:border-slate-700 italic leading-relaxed">
                        "{{ adjustment.remarks }}"
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
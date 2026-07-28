<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ detail: Object });
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Order Return Item Details" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">Return Item Details</h2>
            <div class="flex gap-3">
                <Link :href="route('order-return-details.edit', detail.id)" class="theme-btn-primary px-6 py-2 rounded-full">Edit</Link>
                <Link :href="route('order-return-details.index')" class="theme-form-back-link">Back</Link>
            </div>
        </div>

        <div class="space-y-6">
            <div class="theme-form-card p-10">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Return Ref</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ detail.order_return?.reference_no }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Order #</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ detail.order_return?.order?.order_no }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Product</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ detail.product?.name }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Unit</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ detail.unit?.name }}</dd></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="theme-form-card p-8 bg-indigo-50 dark:bg-indigo-900/10 text-center border-indigo-100 dark:border-indigo-800">
                    <dt class="text-xs font-black uppercase text-indigo-400 mb-2">Quantity</dt>
                    <dd class="text-3xl font-black text-indigo-700 dark:text-indigo-300">{{ detail.quantity }}</dd>
                </div>
                <div class="theme-form-card p-8 bg-slate-50 dark:bg-slate-700/30 text-center">
                    <dt class="text-xs font-black uppercase text-slate-400 mb-2">Unit Price</dt>
                    <dd class="text-3xl font-black text-slate-700 dark:text-slate-300">${{ detail.unit_price }}</dd>
                </div>
                <div class="theme-form-card p-8 bg-emerald-50 dark:bg-emerald-900/10 text-center border-emerald-100 dark:border-emerald-800">
                    <dt class="text-xs font-black uppercase text-emerald-400 mb-2">Line Total</dt>
                    <dd class="text-3xl font-black text-emerald-700 dark:text-emerald-300">${{ detail.line_total }}</dd>
                </div>
            </div>

            <div class="theme-form-card" v-if="detail.reason || detail.remarks">
                <div class="p-8 md:p-10 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div v-if="detail.reason">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3">Reason</h3>
                        <div class="text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50 p-5 rounded-xl border border-slate-100 dark:border-slate-700">
                            {{ detail.reason }}
                        </div>
                    </div>
                    <div v-if="detail.remarks">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-3">Remarks</h3>
                        <div class="text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50 p-5 rounded-xl border border-slate-100 dark:border-slate-700">
                            {{ detail.remarks }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ detail: Object });
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Line Item Details" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">{{ detail.product?.name }}</h2>
            <div class="flex gap-3">
                <Link :href="route('stock-adjustment-details.edit', detail.id)" class="theme-btn-primary px-6 py-2">Edit</Link>
                <Link :href="route('stock-adjustment-details.index')" class="theme-form-back-link">Back</Link>
            </div>
        </div>

        <div class="theme-form-card p-10 mb-6">
            <h3 class="text-xs font-black uppercase text-slate-400 mb-6 tracking-widest">Quantity Analysis</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-6 bg-slate-50 rounded-2xl">
                    <dt class="text-[10px] font-black uppercase text-slate-400">System Qty</dt>
                    <dd class="text-2xl font-black text-slate-700">{{ detail.system_quantity }}</dd>
                </div>
                <div class="text-center p-6 bg-slate-50 rounded-2xl">
                    <dt class="text-[10px] font-black uppercase text-slate-400">Physical Qty</dt>
                    <dd class="text-2xl font-black text-slate-700">{{ detail.physical_quantity }}</dd>
                </div>
                <div class="text-center p-6 rounded-2xl" :class="detail.adjustment_quantity >= 0 ? 'bg-emerald-50' : 'bg-rose-50'">
                    <dt class="text-[10px] font-black uppercase" :class="detail.adjustment_quantity >= 0 ? 'text-emerald-400' : 'text-rose-400'">Adjustment</dt>
                    <dd class="text-2xl font-black" :class="detail.adjustment_quantity >= 0 ? 'text-emerald-700' : 'text-rose-700'">{{ detail.adjustment_quantity }}</dd>
                </div>
            </div>
        </div>

        <div class="theme-form-card p-10 mb-6">
            <h3 class="text-xs font-black uppercase text-slate-400 mb-6 tracking-widest">Financials & Notes</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div><dt class="text-xs font-bold text-slate-400 uppercase">Reason</dt><dd class="font-bold">{{ detail.reason || 'Not specified' }}</dd></div>
                <div><dt class="text-xs font-bold text-slate-400 uppercase">Total Cost</dt><dd class="font-bold text-indigo-600">${{ detail.total_cost }}</dd></div>
                <div class="col-span-2" v-if="detail.remarks"><dt class="text-xs font-bold text-slate-400 uppercase">Remarks</dt><dd class="italic text-slate-600 mt-2">"{{ detail.remarks }}"</dd></div>
            </dl>
        </div>
    </AuthenticatedLayout>
</template>
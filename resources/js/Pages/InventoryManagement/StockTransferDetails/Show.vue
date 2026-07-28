<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ detail: Object });
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Transfer Item Details" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">{{ detail.product?.name }}</h2>
            <div class="flex gap-3">
                <Link :href="route('stock-transfer-details.edit', detail.id)" class="theme-btn-primary px-6 py-2">Edit</Link>
                <Link :href="route('stock-transfer-details.index')" class="theme-form-back-link">Back</Link>
            </div>
        </div>

        <div class="theme-form-card p-10 mb-6">
            <h3 class="text-xs font-black uppercase text-slate-400 mb-6 tracking-widest">Transfer Information</h3>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div><dt class="text-xs font-bold text-slate-400 uppercase">Transfer Ref</dt><dd class="font-bold text-indigo-600">{{ detail.stock_transfer?.reference_no }}</dd></div>
                <div><dt class="text-xs font-bold text-slate-400 uppercase">Product</dt><dd class="font-bold">{{ detail.product?.name }}</dd></div>
                <div><dt class="text-xs font-bold text-slate-400 uppercase">Quantity</dt><dd class="font-bold text-slate-700">{{ detail.quantity }}</dd></div>
            </dl>
        </div>

        <div class="theme-form-card p-10 mb-6">
            <h3 class="text-xs font-black uppercase text-slate-400 mb-6 tracking-widest">Financials & Tracking</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div><dt class="text-xs font-bold text-slate-400 uppercase">Unit Cost</dt><dd class="font-bold">${{ detail.unit_cost }}</dd></div>
                <div><dt class="text-xs font-bold text-slate-400 uppercase">Total Cost</dt><dd class="font-bold text-indigo-600">${{ detail.total_cost }}</dd></div>
                <div v-if="detail.batch_no"><dt class="text-xs font-bold text-slate-400 uppercase">Batch No</dt><dd class="font-bold">{{ detail.batch_no }}</dd></div>
                <div v-if="detail.serial_no"><dt class="text-xs font-bold text-slate-400 uppercase">Serial No</dt><dd class="font-bold">{{ detail.serial_no }}</dd></div>
                <div v-if="detail.expiry_date"><dt class="text-xs font-bold text-slate-400 uppercase">Expiry Date</dt><dd class="font-bold">{{ detail.expiry_date }}</dd></div>
                <div class="col-span-2" v-if="detail.remarks"><dt class="text-xs font-bold text-slate-400 uppercase">Remarks</dt><dd class="italic text-slate-600 mt-2">"{{ detail.remarks }}"</dd></div>
            </dl>
        </div>
    </AuthenticatedLayout>
</template>

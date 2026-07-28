<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ detail: Object });
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Purchase Line Item" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">{{ detail.product?.name }}</h2>
            <div class="flex gap-3">
                <Link :href="route('purchase-details.edit', detail.id)" class="theme-btn-primary px-6 py-2">Edit</Link>
                <Link :href="route('purchase-details.index')" class="theme-form-back-link">Back</Link>
            </div>
        </div>

        <div class="theme-form-card p-10 mb-6">
            <h3 class="text-xs font-black uppercase text-slate-400 mb-6 tracking-widest">Purchase & Product</h3>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div><dt class="text-xs font-bold text-slate-400 uppercase">PO #</dt><dd class="font-bold text-indigo-600">{{ detail.purchase?.purchase_no }}</dd></div>
                <div><dt class="text-xs font-bold text-slate-400 uppercase">Product</dt><dd class="font-bold">{{ detail.product?.name }}</dd></div>
                <div><dt class="text-xs font-bold text-slate-400 uppercase">Added By</dt><dd class="font-bold">{{ detail.user?.name || '—' }}</dd></div>
            </dl>
        </div>

        <div class="theme-form-card p-10 mb-6">
            <h3 class="text-xs font-black uppercase text-slate-400 mb-6 tracking-widest">Quantities & Pricing</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="text-center p-6 bg-slate-50 rounded-2xl">
                    <dt class="text-[10px] font-black uppercase text-slate-400">Quantity</dt>
                    <dd class="text-2xl font-black text-slate-700">{{ detail.quantity }}</dd>
                </div>
                <div class="text-center p-6 bg-slate-50 rounded-2xl">
                    <dt class="text-[10px] font-black uppercase text-slate-400">Free Qty</dt>
                    <dd class="text-2xl font-black text-slate-700">{{ detail.free_quantity }}</dd>
                </div>
                <div class="text-center p-6 bg-indigo-50 rounded-2xl">
                    <dt class="text-[10px] font-black uppercase text-indigo-400">Unit Price</dt>
                    <dd class="text-2xl font-black text-indigo-700">${{ detail.unit_price }}</dd>
                </div>
                <div class="text-center p-6 bg-emerald-50 rounded-2xl">
                    <dt class="text-[10px] font-black uppercase text-emerald-400">Line Total</dt>
                    <dd class="text-2xl font-black text-emerald-700">${{ detail.line_total }}</dd>
                </div>
            </div>
        </div>

        <div class="theme-form-card p-10" v-if="detail.batch_no || detail.serial_no || detail.remarks">
            <h3 class="text-xs font-black uppercase text-slate-400 mb-6 tracking-widest">Tracking & Notes</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div v-if="detail.batch_no"><dt class="text-xs font-bold text-slate-400 uppercase">Batch No</dt><dd class="font-bold">{{ detail.batch_no }}</dd></div>
                <div v-if="detail.serial_no"><dt class="text-xs font-bold text-slate-400 uppercase">Serial No</dt><dd class="font-bold">{{ detail.serial_no }}</dd></div>
                <div v-if="detail.manufacturing_date"><dt class="text-xs font-bold text-slate-400 uppercase">Mfg Date</dt><dd class="font-bold">{{ detail.manufacturing_date }}</dd></div>
                <div v-if="detail.expiry_date"><dt class="text-xs font-bold text-slate-400 uppercase">Expiry</dt><dd class="font-bold">{{ detail.expiry_date }}</dd></div>
                <div class="col-span-2" v-if="detail.remarks"><dt class="text-xs font-bold text-slate-400 uppercase">Remarks</dt><dd class="italic text-slate-600 mt-2">"{{ detail.remarks }}"</dd></div>
            </dl>
        </div>
    </AuthenticatedLayout>
</template>

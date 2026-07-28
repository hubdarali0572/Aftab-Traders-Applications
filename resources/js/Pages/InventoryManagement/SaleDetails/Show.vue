<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ detail: Object });
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Sale Item Details" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">{{ detail.product?.name }}</h2>
            <div class="flex gap-3">
                <Link :href="route('sale-details.edit', detail.id)" class="theme-btn-primary px-6 py-2">Edit</Link>
                <Link :href="route('sale-details.index')" class="theme-form-back-link">Back</Link>
            </div>
        </div>

        <div class="theme-form-card p-10 mb-6">
            <h3 class="text-xs font-black uppercase text-slate-400 mb-6 tracking-widest">Sale Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <div><dt class="text-xs font-bold text-slate-400 uppercase">Invoice</dt><dd class="font-bold text-indigo-600">{{ detail.sale?.invoice_no }}</dd></div>
                <div><dt class="text-xs font-bold text-slate-400 uppercase">Customer</dt><dd class="font-bold">{{ detail.sale?.customer?.customer_name || 'Walk-in' }}</dd></div>
                <div><dt class="text-xs font-bold text-slate-400 uppercase">Warehouse</dt><dd class="font-bold">{{ detail.sale?.warehouse?.name }}</dd></div>
            </div>

            <h3 class="text-xs font-black uppercase text-slate-400 mb-6 tracking-widest">Line Item Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-6 bg-slate-50 rounded-2xl">
                    <dt class="text-[10px] font-black uppercase text-slate-400">Quantity</dt>
                    <dd class="text-2xl font-black text-slate-700">{{ detail.quantity }} {{ detail.selling_unit }}</dd>
                </div>
                <div class="text-center p-6 bg-slate-50 rounded-2xl">
                    <dt class="text-[10px] font-black uppercase text-slate-400">Unit Price</dt>
                    <dd class="text-2xl font-black text-slate-700">${{ detail.unit_price }}</dd>
                </div>
                <div class="text-center p-6 bg-indigo-50 rounded-2xl">
                    <dt class="text-[10px] font-black uppercase text-indigo-400">Line Total</dt>
                    <dd class="text-2xl font-black text-indigo-700">${{ detail.line_total }}</dd>
                </div>
            </div>
        </div>

        <div class="theme-form-card p-10 mb-6">
            <h3 class="text-xs font-black uppercase text-slate-400 mb-6 tracking-widest">Adjustments</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div><dt class="text-xs font-bold text-slate-400 uppercase">Discount</dt><dd class="font-bold text-rose-600">${{ detail.discount }}</dd></div>
                <div><dt class="text-xs font-bold text-slate-400 uppercase">Tax</dt><dd class="font-bold text-emerald-600">${{ detail.tax }}</dd></div>
                <div class="col-span-2" v-if="detail.remarks"><dt class="text-xs font-bold text-slate-400 uppercase">Remarks</dt><dd class="italic text-slate-600 mt-2">"{{ detail.remarks }}"</dd></div>
            </dl>
        </div>
    </AuthenticatedLayout>
</template>

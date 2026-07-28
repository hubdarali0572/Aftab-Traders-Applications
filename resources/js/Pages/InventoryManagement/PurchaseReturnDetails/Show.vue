<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({ detail: Object });
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Return Line Item" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">{{ detail.product?.name }}</h2>
            <div class="flex gap-3">
                <Link :href="route('purchase-return-details.edit', detail.id)" class="theme-btn-primary px-6 py-2">{{ $t('Edit') }}</Link>
                <Link :href="route('purchase-return-details.index')" class="theme-form-back-link">{{ $t('Back') }}</Link>
            </div>
        </div>

        <div class="theme-form-card p-10 mb-6">
            <h3 class="text-xs font-black uppercase text-slate-400 mb-6 tracking-widest">{{ $t('Return & Product') }}</h3>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Return Ref') }}</dt><dd class="font-bold text-indigo-600">{{ detail.purchase_return?.reference_no }}</dd></div>
                <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Product') }}</dt><dd class="font-bold">{{ detail.product?.name }}</dd></div>
                <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Added By') }}</dt><dd class="font-bold">{{ detail.user?.name || '—' }}</dd></div>
            </dl>
        </div>

        <div class="theme-form-card p-10 mb-6">
            <h3 class="text-xs font-black uppercase text-slate-400 mb-6 tracking-widest">{{ $t('Quantities & Pricing') }}</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center p-6 bg-slate-50 rounded-2xl">
                    <dt class="text-[10px] font-black uppercase text-slate-400">{{ $t('Quantity') }}</dt>
                    <dd class="text-2xl font-black text-slate-700">{{ detail.quantity }}</dd>
                </div>
                <div class="text-center p-6 bg-indigo-50 rounded-2xl">
                    <dt class="text-[10px] font-black uppercase text-indigo-400">{{ $t('Unit Price') }}</dt>
                    <dd class="text-2xl font-black text-indigo-700">${{ detail.unit_price }}</dd>
                </div>
                <div class="text-center p-6 bg-emerald-50 rounded-2xl">
                    <dt class="text-[10px] font-black uppercase text-emerald-400">{{ $t('Total Price') }}</dt>
                    <dd class="text-2xl font-black text-emerald-700">${{ detail.total_price }}</dd>
                </div>
            </div>
        </div>

        <div class="theme-form-card p-10" v-if="detail.reason || detail.remarks">
            <h3 class="text-xs font-black uppercase text-slate-400 mb-6 tracking-widest">{{ $t('Notes') }}</h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div v-if="detail.reason"><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Reason') }}</dt><dd class="font-bold">{{ detail.reason }}</dd></div>
                <div class="col-span-2" v-if="detail.remarks"><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Remarks') }}</dt><dd class="italic text-slate-600 mt-2">"{{ detail.remarks }}"</dd></div>
            </dl>
        </div>
    </AuthenticatedLayout>
</template>

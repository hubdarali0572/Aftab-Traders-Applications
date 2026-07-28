<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({ orderReturn: Object });
const page = usePage();

const canConvert = () =>
    props.orderReturn.return_status === 'approved'
    && !props.orderReturn.converted_sale_return_id
    && props.orderReturn.details?.length > 0;

const convertToSaleReturn = () => {
    router.post(route('order-returns.convert-to-sale-return', props.orderReturn.id));
};

const printPage = () => window.print();
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Order Return Details')" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center print:mb-4">
            <h2 class="text-2xl font-black text-slate-900">{{ orderReturn.reference_no }}</h2>
            <div class="flex flex-wrap gap-3 print:hidden">
                <button type="button" @click="printPage" class="theme-form-back-link px-4 py-2">{{ $t('Print') }}</button>
                <Link :href="route('order-returns.edit', orderReturn.id)" class="theme-btn-primary px-6 py-2 rounded-full">{{ $t('Edit') }}</Link>
                <Link :href="route('order-return-details.index', { order_return_id: orderReturn.id })" class="theme-btn-primary px-6 py-2 rounded-full bg-emerald-600 hover:bg-emerald-700">{{ $t('Line Items') }}</Link>
                <button
                    v-if="canConvert()"
                    type="button"
                    @click="convertToSaleReturn"
                    class="theme-btn-primary px-6 py-2 rounded-full bg-violet-600 hover:bg-violet-700"
                >
                    {{ $t('Convert to Sales Return') }}
                </button>
                <Link :href="route('order-returns.index')" class="theme-form-back-link">{{ $t('Back') }}</Link>
            </div>
        </div>

        <div
            v-if="page.props.flash?.error || page.props.flash?.success"
            class="mb-6 flex items-center p-4 border-l-4 rounded-r-xl shadow-sm print:hidden"
            :class="page.props.flash?.success
                ? 'bg-indigo-50 border-indigo-500 text-indigo-800'
                : 'bg-red-50 border-red-500 text-red-800'"
        >
            <p class="text-sm font-bold">{{ page.props.flash?.success || page.props.flash?.error }}</p>
        </div>

        <div class="space-y-6">
            <div class="theme-form-card p-10">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Order #') }}</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ orderReturn.order?.order_no }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Customer') }}</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ orderReturn.customer?.customer_name }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Warehouse') }}</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ orderReturn.warehouse?.name }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Return Date') }}</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ orderReturn.return_date }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Return Status') }}</dt><dd class="font-bold capitalize">{{ orderReturn.return_status }}</dd></div>
                    <div v-if="orderReturn.converted_sale_return_id">
                        <dt class="text-xs font-bold text-slate-400 uppercase">{{ $t('Converted Sales Return') }}</dt>
                        <dd class="font-bold">
                            <Link :href="route('sale-returns.show', orderReturn.converted_sale_return_id)" class="text-indigo-600 hover:underline">
                                {{ orderReturn.converted_sale_return?.reference_no || 'View Sales Return' }}
                            </Link>
                        </dd>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="theme-form-card p-10 bg-indigo-50 dark:bg-indigo-900/10 text-center border-indigo-100 dark:border-indigo-800">
                    <dt class="text-xs font-black uppercase text-indigo-400 mb-2">{{ $t('Total Quantity') }}</dt>
                    <dd class="text-4xl font-black text-indigo-700 dark:text-indigo-300">{{ orderReturn.total_quantity }}</dd>
                </div>
                <div class="theme-form-card p-10 bg-emerald-50 dark:bg-emerald-900/10 text-center border-emerald-100 dark:border-emerald-800">
                    <dt class="text-xs font-black uppercase text-emerald-400 mb-2">{{ $t('Total Amount') }}</dt>
                    <dd class="text-4xl font-black text-emerald-700 dark:text-emerald-300">${{ orderReturn.total_amount }}</dd>
                </div>
            </div>

            <div class="theme-table-card">
                <div class="theme-form-section-header">
                    <h3 class="theme-form-section-title">{{ $t('Returned Items') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="theme-table-header">
                                <th class="theme-table-header-cell">{{ $t('Product') }}</th>
                                <th class="theme-table-header-cell">{{ $t('Unit') }}</th>
                                <th class="theme-table-header-cell text-right">{{ $t('Qty') }}</th>
                                <th class="theme-table-header-cell text-right">{{ $t('Rate') }}</th>
                                <th class="theme-table-header-cell text-right">{{ $t('Total') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <tr v-for="detail in orderReturn.details" :key="detail.id" class="theme-table-row">
                                <td class="px-6 py-3 font-bold text-slate-700 dark:text-slate-300">{{ detail.product?.name }}</td>
                                <td class="px-6 py-3 text-sm text-slate-600 dark:text-slate-400">{{ detail.unit?.name }}</td>
                                <td class="px-6 py-3 text-right text-sm font-bold text-slate-700 dark:text-slate-300">{{ detail.quantity }}</td>
                                <td class="px-6 py-3 text-right text-sm text-slate-600 dark:text-slate-400">${{ detail.unit_price }}</td>
                                <td class="px-6 py-3 text-right text-sm font-black text-slate-700 dark:text-slate-300">${{ detail.line_total }}</td>
                            </tr>
                            <tr v-if="!orderReturn.details?.length">
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 font-medium dark:text-slate-500">{{ $t('No return line items found.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="theme-form-card" v-if="orderReturn.remarks">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-4">{{ $t('Remarks') }}</h3>
                    <div class="text-sm text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/50 p-6 rounded-xl border border-slate-100 dark:border-slate-700 italic leading-relaxed">
                        "{{ orderReturn.remarks }}"
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

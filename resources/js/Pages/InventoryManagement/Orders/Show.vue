<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';

const props = defineProps({ order: Object });
const page = usePage();

const statusClass = (status) => ({
    pending: 'text-amber-600',
    confirmed: 'text-blue-600',
    processing: 'text-indigo-600',
    completed: 'text-emerald-600',
    cancelled: 'text-rose-600',
}[status] ?? 'text-slate-600');

const canConvert = () =>
    !props.order.converted_sale_id
    && props.order.order_status !== 'cancelled'
    && props.order.details?.length > 0;

const convertToSale = () => {
    router.post(route('orders.convert-to-sale', props.order.id));
};

const printPage = () => window.print();
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="order.order_no" />
        <div class="max-w-8xl mx-auto mb-8 flex justify-between items-center print:mb-4">
            <div>
                <h2 class="text-2xl font-black text-slate-900 dark:text-slate-100">{{ order.order_no }}</h2>
                <p class="text-sm text-slate-500 font-medium uppercase tracking-widest" :class="statusClass(order.order_status)">{{ order.order_status }}</p>
            </div>
            <div class="flex flex-wrap gap-3 print:hidden">
                <button type="button" @click="printPage" class="theme-form-back-link px-4 py-2">Print</button>
                <Link :href="route('orders.edit', order.id)" class="theme-btn-primary px-6 py-2 rounded-full">Edit</Link>
                <Link :href="route('order-details.index', { order_id: order.id })" class="theme-btn-primary px-6 py-2 rounded-full bg-emerald-600 hover:bg-emerald-700">Line Items</Link>
                <button
                    v-if="canConvert()"
                    type="button"
                    @click="convertToSale"
                    class="theme-btn-primary px-6 py-2 rounded-full bg-violet-600 hover:bg-violet-700"
                >
                    Convert to Sale
                </button>
                <Link :href="route('orders.index')" class="theme-form-back-link">Back</Link>
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
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Customer</dt><dd class="font-bold">{{ order.customer?.customer_name }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Warehouse</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ order.warehouse?.name }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Order Date</dt><dd class="font-bold text-slate-700 dark:text-slate-300">{{ order.order_date }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Order Type</dt><dd class="font-bold capitalize">{{ order.order_type }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-400 uppercase">Created By</dt><dd class="font-bold text-slate-700">{{ order.user?.name || '—' }}</dd></div>
                    <div v-if="order.converted_sale_id">
                        <dt class="text-xs font-bold text-slate-400 uppercase">Converted Sale</dt>
                        <dd class="font-bold">
                            <Link :href="route('sales.show', order.converted_sale_id)" class="text-indigo-600 hover:underline">
                                {{ order.converted_sale?.invoice_no || 'View Sale' }}
                            </Link>
                        </dd>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="theme-form-card p-8 text-center">
                    <dt class="text-xs font-black uppercase text-slate-400 mb-2">Subtotal</dt>
                    <dd class="text-2xl font-black text-slate-700">${{ order.subtotal }}</dd>
                </div>
                <div class="theme-form-card p-8 text-center">
                    <dt class="text-xs font-black uppercase text-slate-400 mb-2">Discount</dt>
                    <dd class="text-2xl font-black text-rose-600">${{ order.discount }}</dd>
                </div>
                <div class="theme-form-card p-8 text-center">
                    <dt class="text-xs font-black uppercase text-slate-400 mb-2">Tax + Other</dt>
                    <dd class="text-2xl font-black text-slate-700">${{ (parseFloat(order.tax || 0) + parseFloat(order.other_charges || 0)).toFixed(2) }}</dd>
                </div>
                <div class="theme-form-card p-8 text-center bg-indigo-50 dark:bg-indigo-900/10">
                    <dt class="text-xs font-black uppercase text-indigo-400 mb-2">Grand Total</dt>
                    <dd class="text-3xl font-black text-indigo-700">${{ order.grand_total }}</dd>
                </div>
            </div>

            <div class="theme-form-card" v-if="order.details?.length">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-6">Line Items</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="theme-table-header">
                                    <th class="theme-table-header-cell">Product</th>
                                    <th class="theme-table-header-cell">Unit</th>
                                    <th class="theme-table-header-cell text-right">Qty</th>
                                    <th class="theme-table-header-cell text-right">Price</th>
                                    <th class="theme-table-header-cell text-right">Discount</th>
                                    <th class="theme-table-header-cell text-right">Tax</th>
                                    <th class="theme-table-header-cell text-right">Line Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                                <tr v-for="item in order.details" :key="item.id" class="theme-table-row">
                                    <td class="px-4 py-3 font-bold">{{ item.product?.name }}</td>
                                    <td class="px-4 py-3 text-sm uppercase">{{ item.unit?.name }}</td>
                                    <td class="px-4 py-3 text-sm text-right">{{ item.quantity }}</td>
                                    <td class="px-4 py-3 text-sm text-right">${{ item.unit_price }}</td>
                                    <td class="px-4 py-3 text-sm text-right">${{ item.discount }}</td>
                                    <td class="px-4 py-3 text-sm text-right">${{ item.tax }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-bold">${{ item.line_total }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="theme-form-card" v-if="order.remarks">
                <div class="p-8 md:p-10">
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 mb-4">Remarks</h3>
                    <div class="text-sm text-slate-600 bg-slate-50 p-6 rounded-xl border italic leading-relaxed">"{{ order.remarks }}"</div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

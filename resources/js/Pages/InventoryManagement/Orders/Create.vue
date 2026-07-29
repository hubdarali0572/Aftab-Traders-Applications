<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OrderItemsEditor from '@/Components/Inventory/OrderItemsEditor.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    customers: Array,
    warehouses: Array,
    products: Array,
    units: Array,
    users: Array,
    warehouseStocks: Array,
    orderStatuses: Array,
    generatedOrderNo: String,
});

const page = usePage();
const defaultUnitId = props.units?.[0]?.id ?? '';

const form = useForm({
    order_no: props.generatedOrderNo || ('ORD-' + Date.now()),
    order_date: new Date().toISOString().split('T')[0],
    customer_id: '',
    warehouse_id: '',
    processed_by_id: '',
    discount: 0,
    tax: 0,
    other_charges: 0,
    paid_amount: 0,
    order_status: 'pending',
    remarks: '',
    status: true,
    items: [{ product_id: '', unit_id: defaultUnitId, quantity: '', unit_price: 0, discount: 0, tax: 0, remarks: '' }],
});

const itemsSubtotal = computed(() =>
    form.items.reduce((sum, row) => {
        const qty = parseFloat(row.quantity) || 0;
        const price = parseFloat(row.unit_price) || 0;
        const discount = parseFloat(row.discount) || 0;
        const tax = parseFloat(row.tax) || 0;
        return sum + (qty * price - discount + tax);
    }, 0)
);

const grandTotal = computed(() =>
    itemsSubtotal.value
    - (parseFloat(form.discount) || 0)
    + (parseFloat(form.tax) || 0)
    + (parseFloat(form.other_charges) || 0)
);

const dueAmount = computed(() => Math.max(0, grandTotal.value - (parseFloat(form.paid_amount) || 0)));

const paymentStatus = computed(() => {
    if (grandTotal.value <= 0) return 'unpaid';
    if (dueAmount.value <= 0) return 'paid';
    if (parseFloat(form.paid_amount) > 0) return 'partial';
    return 'unpaid';
});

watch(grandTotal, (val) => {
    if (form.order_status === 'completed' && paymentStatus.value === 'paid') {
        form.paid_amount = val.toFixed(2);
    }
});

const submit = () => form.post(route('orders.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('New Order')" />
        <div class="max-w-8xl mx-auto mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $t('New Order') }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $t('Create an order with line items, payments, and warehouse stock updates in one form.') }}</p>
            </div>
            <Link :href="route('orders.index')" class="theme-form-back-link text-sm font-bold shrink-0">{{ $t('Back to List') }}</Link>
        </div>

        <div v-if="page.props.flash?.error" class="mb-6 p-4 border-l-4 border-rose-500 bg-rose-50 text-rose-800 rounded-r-xl text-sm font-bold">{{ page.props.flash.error }}</div>

        <form @submit.prevent="submit" class="max-w-8xl mx-auto theme-form-card p-8 md:p-10 space-y-8">
            <div>
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Order Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-x-8 gap-y-6">
                    <div>
                        <InputLabel :value="$t('Order No.')" class="theme-form-label ml-1" />
                        <TextInput v-model="form.order_no" class="w-full theme-form-input bg-slate-50 dark:bg-slate-800" readonly />
                        <InputError :message="form.errors.order_no" class="mt-2 ml-1" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Order Date')" class="theme-form-label ml-1" />
                        <TextInput type="date" v-model="form.order_date" class="w-full theme-form-input" required />
                        <InputError :message="form.errors.order_date" class="mt-2 ml-1" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Customer')" class="theme-form-label ml-1" />
                        <select v-model="form.customer_id" class="theme-form-input w-full" required>
                            <option value="" disabled>{{ $t('Select Customer') }}</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.customer_name }} ({{ c.customer_code }})</option>
                        </select>
                        <InputError :message="form.errors.customer_id" class="mt-2 ml-1" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Warehouse')" class="theme-form-label ml-1" />
                        <select v-model="form.warehouse_id" class="theme-form-input w-full" required>
                            <option value="" disabled>{{ $t('Select Warehouse') }}</option>
                            <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                        </select>
                        <InputError :message="form.errors.warehouse_id" class="mt-2 ml-1" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Processed By (Optional)')" class="theme-form-label ml-1" />
                        <select v-model="form.processed_by_id" class="theme-form-input w-full">
                            <option value="">{{ $t('Default (Current User)') }}</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel :value="$t('Order Status')" class="theme-form-label ml-1" />
                        <select v-model="form.order_status" class="theme-form-input w-full">
                            <option v-for="s in orderStatuses" :key="s" :value="s">{{ s.charAt(0).toUpperCase() + s.slice(1) }}</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel :value="$t('Payment Status')" class="theme-form-label ml-1" />
                        <div class="theme-form-input bg-slate-50 dark:bg-slate-800 font-bold capitalize">{{ paymentStatus }}</div>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-700 pt-8">
                <OrderItemsEditor
                    v-model="form.items"
                    :products="products"
                    :units="units"
                    :warehouse-stocks="warehouseStocks"
                    :warehouse-id="form.warehouse_id"
                    :order-status="form.order_status"
                    :errors="form.errors"
                />
            </div>

            <div>
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Order Summary') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-x-8 gap-y-6">
                    <div>
                        <InputLabel :value="$t('Subtotal')" class="theme-form-label ml-1" />
                        <div class="theme-form-input bg-slate-50 dark:bg-slate-800 font-bold text-indigo-600">${{ itemsSubtotal.toFixed(2) }}</div>
                    </div>
                    <div>
                        <InputLabel :value="$t('Total Discount')" class="theme-form-label ml-1" />
                        <TextInput type="number" step="0.01" v-model="form.discount" class="w-full theme-form-input" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Total Tax')" class="theme-form-label ml-1" />
                        <TextInput type="number" step="0.01" v-model="form.tax" class="w-full theme-form-input" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Other Charges')" class="theme-form-label ml-1" />
                        <TextInput type="number" step="0.01" v-model="form.other_charges" class="w-full theme-form-input" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Grand Total')" class="theme-form-label ml-1" />
                        <div class="theme-form-input bg-slate-50 dark:bg-slate-800 font-bold text-indigo-600">${{ grandTotal.toFixed(2) }}</div>
                    </div>
                    <div>
                        <InputLabel :value="$t('Paid Amount')" class="theme-form-label ml-1" />
                        <TextInput type="number" step="0.01" v-model="form.paid_amount" class="w-full theme-form-input" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Due Amount')" class="theme-form-label ml-1" />
                        <div class="theme-form-input bg-slate-50 dark:bg-slate-800 font-bold" :class="dueAmount > 0 ? 'text-rose-600' : 'text-emerald-600'">${{ dueAmount.toFixed(2) }}</div>
                    </div>
                </div>
            </div>

            <div>
                <InputLabel :value="$t('Remarks')" class="theme-form-label ml-1" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24 mt-1"></textarea>
            </div>

            <div class="flex justify-center pt-2">
                <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest" :disabled="form.processing">{{ $t('Save Order') }}</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

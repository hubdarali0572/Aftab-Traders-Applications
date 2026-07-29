<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SaleItemsEditor from '@/Components/Inventory/SaleItemsEditor.vue';
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
    users: Array,
    warehouseStocks: Array,
    sellingUnits: Array,
    saleTypes: Array,
    saleStatuses: Array,
    generatedInvoiceNo: String,
});

const page = usePage();

const form = useForm({
    invoice_no: props.generatedInvoiceNo || ('INV-' + Date.now()),
    sale_date: new Date().toISOString().split('T')[0],
    sale_type: 'retail',
    customer_id: '',
    warehouse_id: '',
    sales_person_id: '',
    payment_method: 'cash',
    discount: 0,
    tax: 0,
    other_charges: 0,
    paid_amount: 0,
    sale_status: 'draft',
    remarks: '',
    status: true,
    items: [{ product_id: '', selling_unit: 'piece', quantity: '', unit_price: 0, discount: 0, tax: 0, remarks: '' }],
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

const paymentType = computed({
    get: () => (form.payment_method === 'cash' ? 'cash' : 'credit'),
    set: (val) => {
        form.payment_method = val === 'cash' ? 'cash' : 'bank';
    },
});

watch(paymentType, (val) => {
    if (val === 'cash' && form.sale_status === 'completed') {
        form.paid_amount = grandTotal.value.toFixed(2);
    }
});

watch(grandTotal, (val) => {
    if (paymentType.value === 'cash' && form.sale_status === 'completed') {
        form.paid_amount = val.toFixed(2);
    }
});

const submit = () => form.post(route('sales.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('New Sale')" />
        <div class="max-w-8xl mx-auto mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $t('New Sale') }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $t('Create a sale invoice with line items, payments, and stock updates in one form.') }}</p>
            </div>
            <Link :href="route('sales.index')" class="theme-form-back-link text-sm font-bold shrink-0">{{ $t('Back to List') }}</Link>
        </div>

        <div v-if="page.props.flash?.error" class="mb-6 p-4 border-l-4 border-rose-500 bg-rose-50 text-rose-800 rounded-r-xl text-sm font-bold">{{ page.props.flash.error }}</div>

        <form @submit.prevent="submit" class="max-w-8xl mx-auto theme-form-card p-8 md:p-10 space-y-8">
            <div>
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Sale Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-x-8 gap-y-6">
                    <div>
                        <InputLabel :value="$t('Sale Invoice No.')" class="theme-form-label ml-1" />
                        <TextInput v-model="form.invoice_no" class="w-full theme-form-input bg-slate-50 dark:bg-slate-800" readonly />
                        <InputError :message="form.errors.invoice_no" class="mt-2 ml-1" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Sale Date')" class="theme-form-label ml-1" />
                        <TextInput type="date" v-model="form.sale_date" class="w-full theme-form-input" required />
                        <InputError :message="form.errors.sale_date" class="mt-2 ml-1" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Customer')" class="theme-form-label ml-1" />
                        <select v-model="form.customer_id" class="theme-form-input w-full">
                            <option value="">{{ $t('Walk-in / No Customer') }}</option>
                            <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.customer_name }} ({{ c.customer_code }})</option>
                        </select>
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
                        <InputLabel :value="$t('Sales Person (Optional)')" class="theme-form-label ml-1" />
                        <select v-model="form.sales_person_id" class="theme-form-input w-full">
                            <option value="">{{ $t('Default (Current User)') }}</option>
                            <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel :value="$t('Payment Type')" class="theme-form-label ml-1" />
                        <select v-model="paymentType" class="theme-form-input w-full">
                            <option value="cash">{{ $t('Cash') }}</option>
                            <option value="credit">{{ $t('Credit') }}</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel :value="$t('Status')" class="theme-form-label ml-1" />
                        <select v-model="form.sale_status" class="theme-form-input w-full">
                            <option v-for="s in saleStatuses" :key="s" :value="s">{{ s.charAt(0).toUpperCase() + s.slice(1) }}</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel :value="$t('Sale Type')" class="theme-form-label ml-1" />
                        <select v-model="form.sale_type" class="theme-form-input w-full">
                            <option v-for="t in saleTypes" :key="t" :value="t">{{ t.replace(/_/g, ' ') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-700 pt-8">
                <SaleItemsEditor
                    v-model="form.items"
                    :products="products"
                    :warehouse-stocks="warehouseStocks"
                    :selling-units="sellingUnits"
                    :warehouse-id="form.warehouse_id"
                    :sale-status="form.sale_status"
                    :errors="form.errors"
                />
            </div>

            <div>
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Totals & Payment') }}</h3>
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
                        <InputLabel :value="$t('Remaining Balance')" class="theme-form-label ml-1" />
                        <div class="theme-form-input bg-slate-50 dark:bg-slate-800 font-bold" :class="dueAmount > 0 ? 'text-rose-600' : 'text-emerald-600'">${{ dueAmount.toFixed(2) }}</div>
                    </div>
                </div>
            </div>

            <div>
                <InputLabel :value="$t('Remarks')" class="theme-form-label ml-1" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24 mt-1"></textarea>
            </div>

            <div class="flex justify-center pt-2">
                <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest" :disabled="form.processing">{{ $t('Save Sale') }}</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

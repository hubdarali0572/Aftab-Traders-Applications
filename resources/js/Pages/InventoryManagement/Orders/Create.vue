<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    customers: Array,
    warehouses: Array,
    orderTypes: Array,
    orderStatuses: Array,
});

const form = useForm({
    order_no: 'ORD-' + Date.now(),
    order_date: new Date().toISOString().split('T')[0],
    order_type: 'retail',
    customer_id: '',
    warehouse_id: '',
    subtotal: 0,
    discount: 0,
    tax: 0,
    other_charges: 0,
    grand_total: 0,
    order_status: 'pending',
    remarks: '',
    status: true,
});

const computedGrandTotal = computed(() => {
    return Math.max(0, parseFloat(form.subtotal || 0) - parseFloat(form.discount || 0) + parseFloat(form.tax || 0) + parseFloat(form.other_charges || 0));
});

watch(computedGrandTotal, (val) => { form.grand_total = val.toFixed(2); });

const submit = () => {
    form.grand_total = computedGrandTotal.value;
    form.post(route('orders.store'));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Order" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100">New Order</h2>
            <Link :href="route('orders.index')" class="theme-form-back-link">Back to List</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="rounded-lg border border-indigo-100 bg-indigo-50/70 px-4 py-3 text-sm text-indigo-800 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-200">
                <p class="font-bold">How amounts work</p>
                <p class="mt-1">
                    <strong>Subtotal</strong> is calculated automatically from Order Details (products). It stays <strong>$0.00</strong> until you add line items.
                    <strong>converted_sale_id</strong> stays empty until you use <em>Convert to Sale</em> on the order — it is a system link, not a form field.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Order #" />
                    <TextInput v-model="form.order_no" class="w-full" required />
                    <InputError :message="form.errors.order_no" />
                </div>
                <div>
                    <InputLabel value="Order Date" />
                    <TextInput type="date" v-model="form.order_date" class="w-full" required />
                    <InputError :message="form.errors.order_date" />
                </div>
                <div>
                    <InputLabel value="Order Type" />
                    <select v-model="form.order_type" class="theme-form-input w-full">
                        <option v-for="t in orderTypes" :key="t" :value="t">{{ t.charAt(0).toUpperCase() + t.slice(1) }}</option>
                    </select>
                    <InputError :message="form.errors.order_type" />
                </div>
                <div>
                    <InputLabel value="Customer" />
                    <select v-model="form.customer_id" class="theme-form-input w-full" required>
                        <option value="" disabled>Select Customer</option>
                        <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.customer_name }} ({{ c.customer_code }})</option>
                    </select>
                    <InputError :message="form.errors.customer_id" />
                </div>
                <div>
                    <InputLabel value="Warehouse" />
                    <select v-model="form.warehouse_id" class="theme-form-input w-full" required>
                        <option value="" disabled>Select Warehouse</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                    <InputError :message="form.errors.warehouse_id" />
                </div>
                <div>
                    <InputLabel value="Subtotal (from line items)" />
                    <div class="theme-form-input bg-slate-50 font-bold text-slate-600 dark:bg-slate-700/50">
                        ${{ parseFloat(form.subtotal || 0).toFixed(2) }}
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Updates after you add products under Order Details.</p>
                </div>
                <div>
                    <InputLabel value="Discount" />
                    <TextInput type="number" step="0.01" v-model="form.discount" class="w-full" />
                    <InputError :message="form.errors.discount" />
                </div>
                <div>
                    <InputLabel value="Tax" />
                    <TextInput type="number" step="0.01" v-model="form.tax" class="w-full" />
                    <InputError :message="form.errors.tax" />
                </div>
                <div>
                    <InputLabel value="Other Charges" />
                    <TextInput type="number" step="0.01" v-model="form.other_charges" class="w-full" />
                    <InputError :message="form.errors.other_charges" />
                </div>
                <div>
                    <InputLabel value="Grand Total (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold text-indigo-600 dark:bg-slate-700/50">${{ computedGrandTotal.toFixed(2) }}</div>
                </div>
                <div>
                    <InputLabel value="Order Status" />
                    <select v-model="form.order_status" class="theme-form-input w-full">
                        <option v-for="s in orderStatuses" :key="s" :value="s">{{ s.charAt(0).toUpperCase() + s.slice(1) }}</option>
                    </select>
                    <InputError :message="form.errors.order_status" />
                </div>
                <div>
                    <InputLabel value="Status" />
                    <button type="button" @click="form.status = !form.status" class="mt-2 relative inline-flex h-6 w-11 items-center rounded-full" :class="form.status ? 'bg-indigo-600' : 'bg-slate-300'">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition" :class="form.status ? 'translate-x-6' : 'translate-x-1'" />
                    </button>
                </div>
            </div>
            <div>
                <InputLabel value="Remarks" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24"></textarea>
                <InputError :message="form.errors.remarks" />
            </div>
            <div class="flex justify-center"><PrimaryButton :disabled="form.processing">Save Order &amp; Add Items</PrimaryButton></div>
        </form>
    </AuthenticatedLayout>
</template>

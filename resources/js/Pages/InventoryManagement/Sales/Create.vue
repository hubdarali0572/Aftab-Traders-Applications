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
    saleTypes: Array,
    paymentMethods: Array,
    saleStatuses: Array,
});

const form = useForm({
    invoice_no: 'INV-' + Date.now(),
    sale_date: new Date().toISOString().split('T')[0],
    sale_type: 'retail',
    customer_id: '',
    warehouse_id: '',
    payment_method: 'cash',
    subtotal: 0,
    discount: 0,
    tax: 0,
    other_charges: 0,
    grand_total: 0,
    paid_amount: 0,
    due_amount: 0,
    sale_status: 'draft',
    remarks: '',
    status: true,
});

const computedGrandTotal = computed(() => {
    return Math.max(0, parseFloat(form.subtotal || 0) - parseFloat(form.discount || 0) + parseFloat(form.tax || 0) + parseFloat(form.other_charges || 0));
});

const computedDue = computed(() => Math.max(0, computedGrandTotal.value - parseFloat(form.paid_amount || 0)));

watch(computedGrandTotal, (val) => { form.grand_total = val.toFixed(2); });
watch(computedDue, (val) => { form.due_amount = val.toFixed(2); });

const submit = () => {
    form.grand_total = computedGrandTotal.value;
    form.due_amount = computedDue.value;
    form.post(route('sales.store'));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Sale" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ $t('New Sale') }}</h2>
            <Link :href="route('sales.index')" class="theme-form-back-link">{{ $t('Back to List') }}</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel :value="$t('Invoice #')" />
                    <TextInput v-model="form.invoice_no" class="w-full" required />
                    <InputError :message="form.errors.invoice_no" />
                </div>
                <div>
                    <InputLabel :value="$t('Sale Date')" />
                    <TextInput type="date" v-model="form.sale_date" class="w-full" required />
                </div>
                <div>
                    <InputLabel :value="$t('Sale Type')" />
                    <select v-model="form.sale_type" class="theme-form-input w-full">
                        <option v-for="t in saleTypes" :key="t" :value="t">{{ t.replace(/_/g, ' ') }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Customer (Optional)" />
                    <select v-model="form.customer_id" class="theme-form-input w-full">
                        <option value="">{{ $t('Walk-in / No Customer') }}</option>
                        <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.customer_name }} ({{ c.customer_code }})</option>
                    </select>
                </div>
                <div>
                    <InputLabel :value="$t('Warehouse')" />
                    <select v-model="form.warehouse_id" class="theme-form-input w-full" required>
                        <option value="" disabled>{{ $t('Select Warehouse') }}</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                    <InputError :message="form.errors.warehouse_id" />
                </div>
                <div>
                    <InputLabel :value="$t('Payment Method')" />
                    <select v-model="form.payment_method" class="theme-form-input w-full">
                        <option v-for="m in paymentMethods" :key="m" :value="m">{{ m.charAt(0).toUpperCase() + m.slice(1) }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel :value="$t('Subtotal')" />
                    <TextInput type="number" step="0.01" v-model="form.subtotal" class="w-full" />
                </div>
                <div>
                    <InputLabel :value="$t('Discount')" />
                    <TextInput type="number" step="0.01" v-model="form.discount" class="w-full" />
                </div>
                <div>
                    <InputLabel :value="$t('Tax')" />
                    <TextInput type="number" step="0.01" v-model="form.tax" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Other Charges" />
                    <TextInput type="number" step="0.01" v-model="form.other_charges" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Grand Total (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold text-indigo-600">${{ computedGrandTotal.toFixed(2) }}</div>
                </div>
                <div>
                    <InputLabel :value="$t('Paid Amount')" />
                    <TextInput type="number" step="0.01" v-model="form.paid_amount" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Due Amount (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold" :class="computedDue > 0 ? 'text-rose-600' : 'text-emerald-600'">${{ computedDue.toFixed(2) }}</div>
                </div>
                <div>
                    <InputLabel value="Sale Status" />
                    <select v-model="form.sale_status" class="theme-form-input w-full">
                        <option v-for="s in saleStatuses" :key="s" :value="s">{{ s.charAt(0).toUpperCase() + s.slice(1) }}</option>
                    </select>
                </div>
            </div>
            <div>
                <InputLabel :value="$t('Remarks')" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24"></textarea>
            </div>
            <div class="flex justify-center"><PrimaryButton :disabled="form.processing">{{ $t('Save Sale') }}</PrimaryButton></div>
        </form>
    </AuthenticatedLayout>
</template>

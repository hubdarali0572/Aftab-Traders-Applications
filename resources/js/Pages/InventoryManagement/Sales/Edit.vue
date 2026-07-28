<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    sale: Object,
    customers: Array,
    warehouses: Array,
    saleTypes: Array,
    paymentMethods: Array,
    saleStatuses: Array,
});

const form = useForm({
    invoice_no: props.sale.invoice_no,
    sale_date: props.sale.sale_date ? props.sale.sale_date.substring(0, 10) : '',
    sale_type: props.sale.sale_type,
    customer_id: props.sale.customer_id ?? '',
    warehouse_id: props.sale.warehouse_id,
    payment_method: props.sale.payment_method,
    subtotal: props.sale.subtotal,
    discount: props.sale.discount,
    tax: props.sale.tax,
    other_charges: props.sale.other_charges,
    grand_total: props.sale.grand_total,
    paid_amount: props.sale.paid_amount,
    due_amount: props.sale.due_amount,
    sale_status: props.sale.sale_status,
    remarks: props.sale.remarks ?? '',
    status: Boolean(props.sale.status),
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
    form.put(route('sales.update', props.sale.id));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Edit Sale')" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ $t('Edit Sale') }}</h2>
            <Link :href="route('sales.index')" class="theme-form-back-link font-bold">{{ $t('Back to List') }}</Link>
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
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
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
            <div class="flex justify-center pt-4">
                <PrimaryButton :disabled="form.processing">{{ $t('Update Sale') }}</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

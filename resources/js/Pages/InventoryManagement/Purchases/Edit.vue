<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ purchase: Object, warehouses: Array });

const form = useForm({
    purchase_no: props.purchase.purchase_no,
    supplier_invoice_no: props.purchase.supplier_invoice_no ?? '',
    supplier_name: props.purchase.supplier_name ?? '',
    purchase_date: props.purchase.purchase_date ? props.purchase.purchase_date.substring(0, 10) : '',
    warehouse_id: props.purchase.warehouse_id,
    discount: props.purchase.discount,
    tax: props.purchase.tax,
    shipping_cost: props.purchase.shipping_cost,
    other_charges: props.purchase.other_charges,
    paid_amount: props.purchase.paid_amount,
    purchase_status: props.purchase.purchase_status,
    remarks: props.purchase.remarks ?? '',
    status: Boolean(props.purchase.status),
});

const grandTotal = computed(() => {
    const sub = parseFloat(props.purchase.subtotal) || 0;
    const g = sub - (parseFloat(form.discount) || 0) + (parseFloat(form.tax) || 0)
        + (parseFloat(form.shipping_cost) || 0) + (parseFloat(form.other_charges) || 0);
    return g.toFixed(2);
});

const dueAmount = computed(() => Math.max(0, parseFloat(grandTotal.value) - (parseFloat(form.paid_amount) || 0)).toFixed(2));

const submit = () => form.put(route('purchases.update', props.purchase.id));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Purchase" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-800">Edit Purchase</h2>
            <Link :href="route('purchases.index')" class="theme-form-back-link font-bold">Back to List</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Purchase No (PO #)" />
                    <TextInput v-model="form.purchase_no" class="w-full" required />
                    <InputError :message="form.errors.purchase_no" />
                </div>
                <div>
                    <InputLabel value="Purchase Date" />
                    <TextInput type="date" v-model="form.purchase_date" class="w-full" required />
                </div>
                <div>
                    <InputLabel value="Warehouse" />
                    <select v-model="form.warehouse_id" class="theme-form-input w-full" required>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Supplier Name" />
                    <TextInput v-model="form.supplier_name" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Supplier Invoice / Reference" />
                    <TextInput v-model="form.supplier_invoice_no" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Purchase Status" />
                    <select v-model="form.purchase_status" class="theme-form-input w-full">
                        <option value="draft">Draft</option>
                        <option value="received">Received</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Subtotal (from lines)" />
                    <div class="theme-form-input bg-slate-50 font-bold">${{ purchase.subtotal }}</div>
                </div>
                <div>
                    <InputLabel value="Discount" />
                    <TextInput type="number" step="0.01" v-model="form.discount" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Tax" />
                    <TextInput type="number" step="0.01" v-model="form.tax" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Shipping Cost" />
                    <TextInput type="number" step="0.01" v-model="form.shipping_cost" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Other Charges" />
                    <TextInput type="number" step="0.01" v-model="form.other_charges" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Paid Amount" />
                    <TextInput type="number" step="0.01" v-model="form.paid_amount" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Grand Total (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold text-indigo-600">${{ grandTotal }}</div>
                </div>
                <div>
                    <InputLabel value="Due Amount (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold text-rose-600">${{ dueAmount }}</div>
                </div>
            </div>
            <div>
                <InputLabel value="Remarks" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24"></textarea>
            </div>
            <div class="flex justify-center pt-4">
                <PrimaryButton :disabled="form.processing">Update Purchase</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ purchases: Array, products: Array });

const form = useForm({
    purchase_id: '',
    product_id: '',
    quantity: 0,
    free_quantity: 0,
    unit_price: 0,
    discount: 0,
    tax: 0,
    batch_no: '',
    serial_no: '',
    manufacturing_date: '',
    expiry_date: '',
    remarks: '',
    status: true,
});

const lineTotal = computed(() => {
    const t = (parseFloat(form.quantity) || 0) * (parseFloat(form.unit_price) || 0)
        - (parseFloat(form.discount) || 0) + (parseFloat(form.tax) || 0);
    return t.toFixed(2);
});

const submit = () => form.post(route('purchase-details.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Add Purchase Item" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">Add Purchase Line Item</h2>
            <Link :href="route('purchase-details.index')" class="theme-form-back-link">Back</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Purchase Order" />
                    <select v-model="form.purchase_id" class="theme-form-input w-full" required>
                        <option value="" disabled>Select Purchase</option>
                        <option v-for="p in purchases" :key="p.id" :value="p.id">{{ p.purchase_no }}</option>
                    </select>
                    <InputError :message="form.errors.purchase_id" />
                </div>
                <div>
                    <InputLabel value="Product" />
                    <select v-model="form.product_id" class="theme-form-input w-full" required>
                        <option value="" disabled>Select Product</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <InputError :message="form.errors.product_id" />
                </div>
                <div>
                    <InputLabel value="Quantity" />
                    <TextInput type="number" step="0.01" v-model="form.quantity" class="w-full" required />
                </div>
                <div>
                    <InputLabel value="Free Quantity" />
                    <TextInput type="number" step="0.01" v-model="form.free_quantity" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Unit Price" />
                    <TextInput type="number" step="0.01" v-model="form.unit_price" class="w-full" required />
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
                    <InputLabel value="Line Total (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold text-indigo-600">${{ lineTotal }}</div>
                </div>
                <div>
                    <InputLabel value="Batch No" />
                    <TextInput v-model="form.batch_no" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Serial No" />
                    <TextInput v-model="form.serial_no" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Manufacturing Date" />
                    <TextInput type="date" v-model="form.manufacturing_date" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Expiry Date" />
                    <TextInput type="date" v-model="form.expiry_date" class="w-full" />
                </div>
            </div>
            <div>
                <InputLabel value="Remarks" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24"></textarea>
            </div>
            <div class="flex justify-center"><PrimaryButton :disabled="form.processing">Add Item</PrimaryButton></div>
        </form>
    </AuthenticatedLayout>
</template>

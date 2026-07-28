<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

const props = defineProps({ detail: Object, saleReturns: Array, products: Array, units: Array });

const form = useForm({
    sale_return_id: props.detail.sale_return_id,
    product_id: props.detail.product_id,
    unit_id: props.detail.unit_id,
    quantity: props.detail.quantity,
    reason: props.detail.reason || '',
    remarks: props.detail.remarks || '',
});

const selectedReturn = computed(() => props.saleReturns.find((item) => item.id == form.sale_return_id));
const saleDetails = computed(() => selectedReturn.value?.sale?.details || []);
const availableProducts = computed(() => props.products.filter((product) => saleDetails.value.some((detail) => detail.product_id === product.id)));
const originalDetail = computed(() => saleDetails.value.find((detail) => detail.product_id == form.product_id));
const selectedProduct = computed(() => props.products.find((product) => product.id == form.product_id));
const derivedUnitPrice = computed(() => Number(originalDetail.value?.unit_price || 0).toFixed(2));
const derivedDiscount = computed(() => {
    const qty = Number(form.quantity || 0);
    const soldQty = Number(originalDetail.value?.quantity || 0);
    const totalDiscount = Number(originalDetail.value?.discount || 0);
    return soldQty > 0 ? ((totalDiscount / soldQty) * qty).toFixed(2) : '0.00';
});
const derivedTax = computed(() => {
    const qty = Number(form.quantity || 0);
    const soldQty = Number(originalDetail.value?.quantity || 0);
    const totalTax = Number(originalDetail.value?.tax || 0);
    return soldQty > 0 ? ((totalTax / soldQty) * qty).toFixed(2) : '0.00';
});
const lineTotal = computed(() => {
    const qty = Number(form.quantity || 0);
    const price = Number(originalDetail.value?.unit_price || 0);
    return ((qty * price) - Number(derivedDiscount.value) + Number(derivedTax.value)).toFixed(2);
});

const syncUnit = () => {
    form.unit_id = selectedProduct.value?.unit_id || form.unit_id;
};

onMounted(() => syncUnit());

const submit = () => form.put(route('sale-return-details.update', props.detail.id));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Sales Return Item" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">Edit Sales Return Line Item</h2>
            <Link :href="route('sale-return-details.index')" class="theme-form-back-link">Back</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Sales Return" />
                    <select v-model="form.sale_return_id" class="theme-form-input w-full" required>
                        <option value="" disabled>Select Return</option>
                        <option v-for="item in saleReturns" :key="item.id" :value="item.id">{{ item.reference_no }} - {{ item.sale?.invoice_no }}</option>
                    </select>
                    <InputError :message="form.errors.sale_return_id" />
                </div>
                <div>
                    <InputLabel value="Product" />
                    <select v-model="form.product_id" class="theme-form-input w-full" required @change="syncUnit">
                        <option value="" disabled>Select Product</option>
                        <option v-for="product in availableProducts" :key="product.id" :value="product.id">{{ product.name }}</option>
                    </select>
                    <InputError :message="form.errors.product_id" />
                </div>
                <div>
                    <InputLabel value="Unit" />
                    <select v-model="form.unit_id" class="theme-form-input w-full" required>
                        <option value="" disabled>Select Unit</option>
                        <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                    </select>
                    <InputError :message="form.errors.unit_id" />
                </div>
                <div>
                    <InputLabel value="Return Quantity" />
                    <TextInput type="number" step="0.01" v-model="form.quantity" class="w-full" required />
                    <InputError :message="form.errors.quantity" />
                </div>
                <div>
                    <InputLabel value="Invoice Unit Price" />
                    <div class="theme-form-input bg-slate-50 font-bold text-indigo-600 dark:bg-slate-700/50">${{ derivedUnitPrice }}</div>
                </div>
                <div>
                    <InputLabel value="Line Total (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold text-indigo-600 dark:bg-slate-700/50">${{ lineTotal }}</div>
                </div>
                <div>
                    <InputLabel value="Discount (Auto)" />
                    <div class="theme-form-input bg-slate-50 dark:bg-slate-700/50">${{ derivedDiscount }}</div>
                </div>
                <div>
                    <InputLabel value="Tax (Auto)" />
                    <div class="theme-form-input bg-slate-50 dark:bg-slate-700/50">${{ derivedTax }}</div>
                </div>
                <div>
                    <InputLabel value="Reason" />
                    <TextInput v-model="form.reason" class="w-full" placeholder="e.g. Damaged, wrong size" />
                    <InputError :message="form.errors.reason" />
                </div>
            </div>
            <div>
                <InputLabel value="Remarks" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24"></textarea>
                <InputError :message="form.errors.remarks" />
            </div>
            <div class="flex justify-center"><PrimaryButton :disabled="form.processing">Update Item</PrimaryButton></div>
        </form>
    </AuthenticatedLayout>
</template>

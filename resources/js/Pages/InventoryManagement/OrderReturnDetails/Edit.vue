<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, onMounted } from 'vue';

const props = defineProps({ detail: Object, orderReturns: Array, products: Array, units: Array });
const page = usePage();

const form = useForm({
    order_return_id: props.detail.order_return_id,
    product_id: props.detail.product_id,
    unit_id: props.detail.unit_id,
    quantity: props.detail.quantity,
    reason: props.detail.reason || '',
    remarks: props.detail.remarks || '',
    status: true,
});

const selectedReturn = computed(() => props.orderReturns.find((item) => item.id == form.order_return_id));
const orderDetails = computed(() => selectedReturn.value?.order?.details || []);
const availableProducts = computed(() =>
    props.products.filter((product) =>
        orderDetails.value.some((detail) => detail.product_id == product.id)
    )
);
const originalDetail = computed(() =>
    orderDetails.value.find((detail) => detail.product_id == form.product_id)
);
const derivedUnitPrice = computed(() => Number(originalDetail.value?.unit_price || 0).toFixed(2));
const maxQty = computed(() => Number(originalDetail.value?.quantity || 0));
const lineTotal = computed(() => {
    const qty = Number(form.quantity || 0);
    const price = Number(originalDetail.value?.unit_price || 0);
    const soldQty = Number(originalDetail.value?.quantity || 0);
    const totalDiscount = Number(originalDetail.value?.discount || 0);
    const totalTax = Number(originalDetail.value?.tax || 0);
    const discount = soldQty > 0 ? (totalDiscount / soldQty) * qty : 0;
    const tax = soldQty > 0 ? (totalTax / soldQty) * qty : 0;
    return ((qty * price) - discount + tax).toFixed(2);
});

const syncUnit = () => {
    form.unit_id = originalDetail.value?.unit_id || selectedProduct.value?.unit_id || form.unit_id;
};

const selectedProduct = computed(() => props.products.find((product) => product.id == form.product_id));

onMounted(() => syncUnit());

const submit = () => {
    if (Number(form.quantity) < 0.01) {
        form.setError('quantity', 'Return quantity must be greater than zero.');
        return;
    }
    form.put(route('order-return-details.update', props.detail.id));
};
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Order Return Item" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">{{ $t('Edit Order Return Line Item') }}</h2>
            <Link :href="route('order-return-details.index')" class="theme-form-back-link">{{ $t('Back') }}</Link>
        </div>

        <div
            v-if="page.props.flash?.error || page.props.flash?.success"
            class="mb-6 flex items-center p-4 border-l-4 rounded-r-xl shadow-sm"
            :class="page.props.flash?.success
                ? 'bg-indigo-50 border-indigo-500 text-indigo-800'
                : 'bg-red-50 border-red-500 text-red-800'"
        >
            <p class="text-sm font-bold">{{ page.props.flash?.success || page.props.flash?.error }}</p>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Order Return" />
                    <select v-model="form.order_return_id" class="theme-form-input w-full" required>
                        <option v-for="item in orderReturns" :key="item.id" :value="item.id">{{ item.reference_no }} - {{ item.order?.order_no }}</option>
                    </select>
                    <InputError :message="form.errors.order_return_id" />
                </div>
                <div>
                    <InputLabel :value="$t('Product')" />
                    <select v-model="form.product_id" class="theme-form-input w-full" required @change="syncUnit">
                        <option v-for="product in availableProducts" :key="product.id" :value="product.id">{{ product.name }}</option>
                    </select>
                    <InputError :message="form.errors.product_id" />
                </div>
                <div>
                    <InputLabel :value="$t('Unit')" />
                    <select v-model="form.unit_id" class="theme-form-input w-full" required>
                        <option v-for="unit in units" :key="unit.id" :value="unit.id">{{ unit.name }}</option>
                    </select>
                    <InputError :message="form.errors.unit_id" />
                </div>
                <div>
                    <InputLabel value="Return Quantity" />
                    <TextInput type="number" step="0.01" min="0.01" v-model="form.quantity" class="w-full" required />
                    <p v-if="originalDetail" class="mt-1 text-xs text-slate-500">Ordered qty: {{ maxQty }}</p>
                    <InputError :message="form.errors.quantity" />
                </div>
                <div>
                    <InputLabel value="Order Unit Price" />
                    <div class="theme-form-input bg-slate-50 font-bold text-indigo-600 dark:bg-slate-700/50">${{ derivedUnitPrice }}</div>
                </div>
                <div>
                    <InputLabel value="Line Total (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold text-indigo-600 dark:bg-slate-700/50">${{ lineTotal }}</div>
                </div>
                <div>
                    <InputLabel :value="$t('Reason')" />
                    <TextInput v-model="form.reason" class="w-full" placeholder="e.g. Damaged, wrong size" />
                    <InputError :message="form.errors.reason" />
                </div>
            </div>
            <div>
                <InputLabel :value="$t('Remarks')" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24"></textarea>
                <InputError :message="form.errors.remarks" />
            </div>
            <div class="flex justify-center">
                <PrimaryButton type="submit" :disabled="form.processing">{{ $t('Update Item') }}</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

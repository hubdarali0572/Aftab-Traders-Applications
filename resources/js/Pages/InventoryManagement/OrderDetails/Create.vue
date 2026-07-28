<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    orders: Array,
    products: Array,
    units: Array,
    defaultOrderId: [String, Number],
});

const page = usePage();

const form = useForm({
    order_id: props.defaultOrderId ? Number(props.defaultOrderId) : '',
    product_id: '',
    unit_id: '',
    quantity: 1,
    unit_price: 0,
    discount: 0,
    tax: 0,
    remarks: '',
    status: true,
});

const selectedProduct = computed(() => props.products.find((p) => p.id == form.product_id));

const syncUnit = () => {
    form.unit_id = selectedProduct.value?.unit_id || '';
};

const lineTotal = computed(() => {
    const sub = parseFloat(form.quantity || 0) * parseFloat(form.unit_price || 0);
    return (sub - parseFloat(form.discount || 0) + parseFloat(form.tax || 0)).toFixed(2);
});

const submit = () => form.post(route('order-details.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Add Order Item" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">Add Order Line Item</h2>
            <Link :href="route('order-details.index')" class="theme-form-back-link">Back</Link>
        </div>

        <div
            v-if="page.props.flash?.error || page.props.flash?.success"
            class="mb-6 flex items-center p-4 border-l-4 rounded-r-xl shadow-sm"
            :class="page.props.flash?.success
                ? 'bg-indigo-50 border-indigo-500 text-indigo-800 dark:bg-indigo-500/10 dark:text-indigo-200'
                : 'bg-red-50 border-red-500 text-red-800 dark:bg-red-500/10 dark:text-red-200'"
        >
            <p class="text-sm font-bold">{{ page.props.flash?.success || page.props.flash?.error }}</p>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Order" />
                    <select v-model="form.order_id" class="theme-form-input w-full" required>
                        <option value="" disabled>Select Order</option>
                        <option v-for="o in orders" :key="o.id" :value="o.id">
                            {{ o.order_no }} ({{ o.order_status }})
                        </option>
                    </select>
                    <InputError :message="form.errors.order_id" />
                </div>
                <div>
                    <InputLabel value="Product" />
                    <select v-model="form.product_id" class="theme-form-input w-full" required @change="syncUnit">
                        <option value="" disabled>Select Product</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <InputError :message="form.errors.product_id" />
                </div>
                <div>
                    <InputLabel value="Unit" />
                    <select v-model="form.unit_id" class="theme-form-input w-full" required>
                        <option value="" disabled>Select Unit</option>
                        <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                    <InputError :message="form.errors.unit_id" />
                </div>
                <div>
                    <InputLabel value="Quantity" />
                    <TextInput type="number" step="0.01" v-model="form.quantity" class="w-full" required />
                    <InputError :message="form.errors.quantity" />
                </div>
                <div>
                    <InputLabel value="Unit Price" />
                    <TextInput type="number" step="0.01" v-model="form.unit_price" class="w-full" required />
                    <InputError :message="form.errors.unit_price" />
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
                    <InputLabel value="Line Total (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold text-indigo-600">${{ lineTotal }}</div>
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
            <div class="flex justify-center">
                <PrimaryButton type="submit" :disabled="form.processing">Add Item</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    detail: Object,
    orders: Array,
    products: Array,
    units: Array,
});

const page = usePage();

const form = useForm({
    order_id: props.detail.order_id,
    product_id: props.detail.product_id,
    unit_id: props.detail.unit_id,
    quantity: props.detail.quantity,
    unit_price: props.detail.unit_price,
    discount: props.detail.discount,
    tax: props.detail.tax,
    remarks: props.detail.remarks ?? '',
    status: Boolean(props.detail.status),
});

const selectedProduct = computed(() => props.products.find((p) => p.id == form.product_id));

const syncUnit = () => {
    form.unit_id = selectedProduct.value?.unit_id || form.unit_id;
};

const lineTotal = computed(() => {
    const sub = parseFloat(form.quantity || 0) * parseFloat(form.unit_price || 0);
    return (sub - parseFloat(form.discount || 0) + parseFloat(form.tax || 0)).toFixed(2);
});

const submit = () => form.put(route('order-details.update', props.detail.id));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Edit Order Item')" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black text-slate-900">{{ $t('Edit Order Item') }}</h2>
                <p class="text-sm text-slate-500 font-medium">Record ID: #{{ detail.id }}</p>
            </div>
            <Link :href="route('order-details.index')" class="theme-form-back-link">{{ $t('Back to List') }}</Link>
        </div>

        <div
            v-if="page.props.flash?.error"
            class="mb-6 flex items-center p-4 border-l-4 border-red-500 bg-red-50 text-red-800 rounded-r-xl shadow-sm dark:bg-red-500/10 dark:text-red-200"
        >
            <p class="text-sm font-bold">{{ page.props.flash.error }}</p>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Order" />
                    <select v-model="form.order_id" class="theme-form-input w-full" required>
                        <option v-for="o in orders" :key="o.id" :value="o.id">
                            {{ o.order_no }} ({{ o.order_status }})
                        </option>
                    </select>
                    <InputError :message="form.errors.order_id" />
                </div>
                <div>
                    <InputLabel :value="$t('Product')" />
                    <select v-model="form.product_id" class="theme-form-input w-full" required @change="syncUnit">
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <InputError :message="form.errors.product_id" />
                </div>
                <div>
                    <InputLabel :value="$t('Unit')" />
                    <select v-model="form.unit_id" class="theme-form-input w-full" required>
                        <option v-for="u in units" :key="u.id" :value="u.id">{{ u.name }}</option>
                    </select>
                    <InputError :message="form.errors.unit_id" />
                </div>
                <div>
                    <InputLabel :value="$t('Quantity')" />
                    <TextInput type="number" step="0.01" v-model="form.quantity" class="w-full" required />
                    <InputError :message="form.errors.quantity" />
                </div>
                <div>
                    <InputLabel :value="$t('Unit Price')" />
                    <TextInput type="number" step="0.01" v-model="form.unit_price" class="w-full" required />
                    <InputError :message="form.errors.unit_price" />
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
                    <InputLabel value="Line Total (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold text-indigo-600">${{ lineTotal }}</div>
                </div>
                <div>
                    <InputLabel :value="$t('Status')" />
                    <button type="button" @click="form.status = !form.status" class="mt-2 relative inline-flex h-6 w-11 items-center rounded-full" :class="form.status ? 'bg-indigo-600' : 'bg-slate-300'">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition" :class="form.status ? 'translate-x-6' : 'translate-x-1'" />
                    </button>
                </div>
            </div>
            <div>
                <InputLabel :value="$t('Remarks')" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24"></textarea>
            </div>
            <div class="flex justify-center pt-4">
                <PrimaryButton type="submit" :disabled="form.processing">{{ $t('Update Item') }}</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ detail: Object, sales: Array, products: Array, sellingUnits: Array });

const form = useForm({
    sale_id: props.detail.sale_id,
    product_id: props.detail.product_id,
    selling_unit: props.detail.selling_unit,
    quantity: props.detail.quantity,
    unit_price: props.detail.unit_price,
    discount: props.detail.discount,
    tax: props.detail.tax,
    remarks: props.detail.remarks ?? '',
    status: Boolean(props.detail.status),
});

const lineTotal = computed(() => {
    const sub = parseFloat(form.quantity || 0) * parseFloat(form.unit_price || 0);
    return (sub - parseFloat(form.discount || 0) + parseFloat(form.tax || 0)).toFixed(2);
});

const submit = () => form.put(route('sale-details.update', props.detail.id));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Sale Item" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black text-slate-900">Edit Sale Item</h2>
                <p class="text-sm text-slate-500 font-medium">Record ID: #{{ detail.id }}</p>
            </div>
            <Link :href="route('sale-details.index')" class="theme-form-back-link">Back to List</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Sale Invoice" />
                    <select v-model="form.sale_id" class="theme-form-input w-full" required>
                        <option v-for="s in sales" :key="s.id" :value="s.id">{{ s.invoice_no }}</option>
                    </select>
                    <InputError :message="form.errors.sale_id" />
                </div>
                <div>
                    <InputLabel value="Product" />
                    <select v-model="form.product_id" class="theme-form-input w-full" required>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <InputError :message="form.errors.product_id" />
                </div>
                <div>
                    <InputLabel value="Selling Unit" />
                    <select v-model="form.selling_unit" class="theme-form-input w-full">
                        <option v-for="u in sellingUnits" :key="u" :value="u">{{ u }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Quantity" />
                    <TextInput type="number" step="0.01" v-model="form.quantity" class="w-full" required />
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
                    <InputLabel value="Status" />
                    <button type="button" @click="form.status = !form.status" class="mt-2 relative inline-flex h-6 w-11 items-center rounded-full" :class="form.status ? 'bg-indigo-600' : 'bg-slate-300'">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition" :class="form.status ? 'translate-x-6' : 'translate-x-1'" />
                    </button>
                </div>
            </div>
            <div>
                <InputLabel value="Remarks" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24"></textarea>
            </div>
            <div class="flex justify-center pt-4">
                <PrimaryButton :disabled="form.processing">Update Item</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

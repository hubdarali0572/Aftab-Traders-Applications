<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ purchaseReturns: Array, products: Array });

const form = useForm({
    purchase_return_id: '',
    product_id: '',
    quantity: 0,
    unit_price: 0,
    reason: '',
    remarks: '',
    status: true,
});

const totalPrice = computed(() => ((parseFloat(form.quantity) || 0) * (parseFloat(form.unit_price) || 0)).toFixed(2));

const submit = () => form.post(route('purchase-return-details.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Add Return Item" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">Add Return Line Item</h2>
            <Link :href="route('purchase-return-details.index')" class="theme-form-back-link">Back</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Purchase Return" />
                    <select v-model="form.purchase_return_id" class="theme-form-input w-full" required>
                        <option value="" disabled>Select Return</option>
                        <option v-for="r in purchaseReturns" :key="r.id" :value="r.id">{{ r.reference_no }}</option>
                    </select>
                    <InputError :message="form.errors.purchase_return_id" />
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
                    <InputLabel value="Unit Price" />
                    <TextInput type="number" step="0.01" v-model="form.unit_price" class="w-full" required />
                </div>
                <div>
                    <InputLabel value="Total Price (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold text-indigo-600">${{ totalPrice }}</div>
                </div>
                <div>
                    <InputLabel value="Reason" />
                    <TextInput v-model="form.reason" class="w-full" placeholder="e.g. Defective, Wrong item" />
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

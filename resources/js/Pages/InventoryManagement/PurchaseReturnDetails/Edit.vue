<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ detail: Object, purchaseReturns: Array, products: Array });

const form = useForm({
    purchase_return_id: props.detail.purchase_return_id,
    product_id: props.detail.product_id,
    quantity: props.detail.quantity,
    unit_price: props.detail.unit_price,
    reason: props.detail.reason ?? '',
    remarks: props.detail.remarks ?? '',
    status: Boolean(props.detail.status),
});

const totalPrice = computed(() => ((parseFloat(form.quantity) || 0) * (parseFloat(form.unit_price) || 0)).toFixed(2));

const submit = () => form.put(route('purchase-return-details.update', props.detail.id));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Return Item" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black text-slate-900">{{ $t('Edit Return Line Item') }}</h2>
                <p class="text-sm text-slate-500 font-medium">Record ID: #{{ detail.id }}</p>
            </div>
            <Link :href="route('purchase-return-details.index')" class="theme-form-back-link">{{ $t('Back to List') }}</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Purchase Return" />
                    <select v-model="form.purchase_return_id" class="theme-form-input w-full" required>
                        <option v-for="r in purchaseReturns" :key="r.id" :value="r.id">{{ r.reference_no }}</option>
                    </select>
                    <InputError :message="form.errors.purchase_return_id" />
                </div>
                <div>
                    <InputLabel :value="$t('Product')" />
                    <select v-model="form.product_id" class="theme-form-input w-full" required>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <InputError :message="form.errors.product_id" />
                </div>
                <div>
                    <InputLabel :value="$t('Quantity')" />
                    <TextInput type="number" step="0.01" v-model="form.quantity" class="w-full" required />
                </div>
                <div>
                    <InputLabel :value="$t('Unit Price')" />
                    <TextInput type="number" step="0.01" v-model="form.unit_price" class="w-full" required />
                </div>
                <div>
                    <InputLabel value="Total Price (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold text-indigo-600">${{ totalPrice }}</div>
                </div>
                <div>
                    <InputLabel :value="$t('Reason')" />
                    <TextInput v-model="form.reason" class="w-full" />
                </div>
            </div>
            <div>
                <InputLabel :value="$t('Remarks')" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24"></textarea>
            </div>
            <div class="flex justify-center pt-4">
                <PrimaryButton :disabled="form.processing">{{ $t('Update Item') }}</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

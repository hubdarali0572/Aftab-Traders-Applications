<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ adjustments: Array, products: Array });
const form = useForm({
    stock_adjustment_id: '',
    product_id: '',
    system_quantity: 0,
    physical_quantity: 0,
    unit_cost: 0,
    reason: '',
    remarks: '',
    status: true,
});

const diffQty = computed(() => (form.physical_quantity - form.system_quantity).toFixed(2));
const totalCost = computed(() => (Math.abs(diffQty.value) * form.unit_cost).toFixed(2));

const submit = () => form.post(route('stock-adjustment-details.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Add Adjustment Item" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">Add Stock Adjustment Detail</h2>
            <Link :href="route('stock-adjustment-details.index')" class="theme-form-back-link">Back to Stock List</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Stock Adjustment Ref" />
                    <select v-model="form.stock_adjustment_id" class="theme-form-input w-full" required>
                        <option value="" disabled>Select Adjustment</option>
                        <option v-for="a in adjustments" :key="a.id" :value="a.id">{{ a.reference_no }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Product" />
                    <select v-model="form.product_id" class="theme-form-input w-full" required>
                        <option value="" disabled>Select Product</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Reason" />
                    <TextInput v-model="form.reason" class="w-full" placeholder="e.g. Damage, Miscount" />
                </div>
                <div>
                    <InputLabel value="System Quantity" />
                    <TextInput type="number" step="0.01" v-model="form.system_quantity" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Physical Quantity" />
                    <TextInput type="number" step="0.01" v-model="form.physical_quantity" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Adjustment Qty (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold" :class="diffQty >= 0 ? 'text-emerald-600' : 'text-rose-600'">{{ diffQty }}</div>
                </div>
                <div>
                    <InputLabel value="Unit Cost" />
                    <TextInput type="number" step="0.01" v-model="form.unit_cost" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Total Cost (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold text-indigo-600">${{ totalCost }}</div>
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
            <div class="flex justify-center"><PrimaryButton :disabled="form.processing">Add Item</PrimaryButton></div>
        </form>
    </AuthenticatedLayout>
</template>
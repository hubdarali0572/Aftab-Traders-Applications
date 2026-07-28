<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ damaged_stocks: Array, products: Array });

const form = useForm({
    damaged_stock_id: '',
    product_id: '',
    quantity: 0,
    unit_cost: 0,
    damage_reason: '',
    batch_no: '',
    serial_no: '',
    expiry_date: '',
    remarks: '',
    status: true,
});

const totalCost = computed(() => (parseFloat(form.quantity || 0) * parseFloat(form.unit_cost || 0)).toFixed(2));

const submit = () => form.post(route('damaged-stock-details.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Add Damaged Item" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">Add Damaged Line Item</h2>
            <Link :href="route('damaged-stock-details.index')" class="theme-form-back-link">Back</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Damage Record Ref" />
                    <select v-model="form.damaged_stock_id" class="theme-form-input w-full" required>
                        <option value="" disabled>Select Damage Record</option>
                        <option v-for="s in damaged_stocks" :key="s.id" :value="s.id">{{ s.reference_no }}</option>
                    </select>
                    <InputError :message="form.errors.damaged_stock_id" />
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
                    <InputLabel value="Damage Reason" />
                    <TextInput v-model="form.damage_reason" class="w-full" required placeholder="e.g. Breakage, Expired, Water damage" />
                    <InputError :message="form.errors.damage_reason" />
                </div>
                <div>
                    <InputLabel value="Quantity" />
                    <TextInput type="number" step="0.01" v-model="form.quantity" class="w-full" required />
                    <InputError :message="form.errors.quantity" />
                </div>
                <div>
                    <InputLabel value="Unit Cost" />
                    <TextInput type="number" step="0.01" v-model="form.unit_cost" class="w-full" required />
                    <InputError :message="form.errors.unit_cost" />
                </div>
                <div>
                    <InputLabel value="Total Cost (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold text-indigo-600">${{ totalCost }}</div>
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
                    <InputLabel value="Expiry Date" />
                    <TextInput type="date" v-model="form.expiry_date" class="w-full" />
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

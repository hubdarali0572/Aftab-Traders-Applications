<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ purchases: Array, warehouses: Array });

const form = useForm({
    reference_no: 'PR-' + Date.now(),
    purchase_id: '',
    warehouse_id: '',
    return_date: new Date().toISOString().split('T')[0],
    total_quantity: 0,
    total_amount: 0,
    remarks: '',
    status: true,
});

const submit = () => form.post(route('purchase-returns.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Purchase Return" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-800">New Purchase Return</h2>
            <Link :href="route('purchase-returns.index')" class="theme-form-back-link">Back to List</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Reference No" />
                    <TextInput v-model="form.reference_no" class="w-full" required />
                    <InputError :message="form.errors.reference_no" />
                </div>
                <div>
                    <InputLabel value="Return Date" />
                    <TextInput type="date" v-model="form.return_date" class="w-full" required />
                </div>
                <div>
                    <InputLabel value="Purchase Order" />
                    <select v-model="form.purchase_id" class="theme-form-input w-full" required>
                        <option value="" disabled>Select Purchase</option>
                        <option v-for="p in purchases" :key="p.id" :value="p.id">{{ p.purchase_no }}</option>
                    </select>
                    <InputError :message="form.errors.purchase_id" />
                </div>
                <div>
                    <InputLabel value="Warehouse" />
                    <select v-model="form.warehouse_id" class="theme-form-input w-full" required>
                        <option value="" disabled>Select Warehouse</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Total Quantity" />
                    <TextInput type="number" step="0.01" v-model="form.total_quantity" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Total Amount" />
                    <TextInput type="number" step="0.01" v-model="form.total_amount" class="w-full" />
                </div>
            </div>
            <div>
                <InputLabel value="Remarks" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24"></textarea>
            </div>
            <div class="flex justify-center"><PrimaryButton :disabled="form.processing">Save Return</PrimaryButton></div>
        </form>
    </AuthenticatedLayout>
</template>

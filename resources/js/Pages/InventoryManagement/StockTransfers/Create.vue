<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ warehouses: Array });

const form = useForm({
    reference_no: 'TRF-' + Date.now(),
    transfer_date: new Date().toISOString().split('T')[0],
    from_warehouse_id: '',
    to_warehouse_id: '',
    total_quantity: 0,
    total_amount: 0,
    stock_status: 'draft',
    remarks: '',
    status: true,
});

const submit = () => form.post(route('stock-transfers.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Stock Transfer" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-800">New Stock Transfer</h2>
            <Link :href="route('stock-transfers.index')" class="theme-form-back-link">Back to List</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Reference #" />
                    <TextInput v-model="form.reference_no" class="w-full" required />
                    <InputError :message="form.errors.reference_no" />
                </div>
                <div>
                    <InputLabel value="Transfer Date" />
                    <TextInput type="date" v-model="form.transfer_date" class="w-full" required />
                    <InputError :message="form.errors.transfer_date" />
                </div>
                <div>
                    <InputLabel value="Stock Status" />
                    <select v-model="form.stock_status" class="theme-form-input w-full" required>
                        <option value="draft">Draft</option>
                        <option value="in_transit">In Transit</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <InputError :message="form.errors.stock_status" />
                </div>
                <div>
                    <InputLabel value="From Warehouse" />
                    <select v-model="form.from_warehouse_id" class="theme-form-input w-full" required>
                        <option value="" disabled>Select Source Warehouse</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id" :disabled="w.id == form.to_warehouse_id">{{ w.name }}</option>
                    </select>
                    <InputError :message="form.errors.from_warehouse_id" />
                </div>
                <div>
                    <InputLabel value="To Warehouse" />
                    <select v-model="form.to_warehouse_id" class="theme-form-input w-full" required>
                        <option value="" disabled>Select Destination Warehouse</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id" :disabled="w.id == form.from_warehouse_id">{{ w.name }}</option>
                    </select>
                    <InputError :message="form.errors.to_warehouse_id" />
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
            <div class="flex justify-center"><PrimaryButton :disabled="form.processing">Save Transfer</PrimaryButton></div>
        </form>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ warehouses: Array });
const form = useForm({
    reference_no: 'OS-' + Date.now(),
    opening_date: new Date().toISOString().split('T')[0],
    warehouse_id: '',
    total_quantity: 0,
    total_amount: 0,
    remarks: '',
    status: true,
});

const submit = () => form.post(route('opening-stocks.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Opening Stock" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">Create Opening Stock</h2>
            <Link :href="route('opening-stocks.index')" class="theme-form-back-link text-sm font-bold">Back to List</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Reference #" />
                    <TextInput v-model="form.reference_no" class="w-full" required />
                    <InputError :message="form.errors.reference_no" />
                </div>
                <div>
                    <InputLabel value="Opening Date" />
                    <TextInput type="date" v-model="form.opening_date" class="w-full" required />
                    <InputError :message="form.errors.opening_date" />
                </div>
                <div>
                    <InputLabel value="Warehouse" />
                    <select v-model="form.warehouse_id" class="theme-form-input w-full" required>
                        <option value="" disabled>Select Warehouse</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                    <InputError :message="form.errors.warehouse_id" />
                </div>
                <div>
                    <InputLabel value="Total Quantity" />
                    <TextInput type="number" step="0.01" v-model="form.total_quantity" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Total Amount" />
                    <TextInput type="number" step="0.01" v-model="form.total_amount" class="w-full" />
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
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24" placeholder="Optional notes..."></textarea>
            </div>
            <div class="flex justify-center"><PrimaryButton :disabled="form.processing">Save Opening Stock</PrimaryButton></div>
        </form>
    </AuthenticatedLayout>
</template>
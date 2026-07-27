<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ adjustment: Object, warehouses: Array });
const form = useForm({
    reference_no: props.adjustment.reference_no,
    adjustment_date: props.adjustment.adjustment_date ? props.adjustment.adjustment_date.substring(0, 10) : '',
    warehouse_id: props.adjustment.warehouse_id,
    adjustment_type: props.adjustment.adjustment_type,
    total_quantity: props.adjustment.total_quantity,
    total_amount: props.adjustment.total_amount,
    remarks: props.adjustment.remarks,
    status: Boolean(props.adjustment.status),
});

const submit = () => form.put(route('stock-adjustments.update', props.adjustment.id));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Adjustment" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-800">Edit Adjustment</h2>
            <Link :href="route('stock-adjustments.index')" class="theme-form-back-link font-bold">Back to List</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- ... existing fields (Ref, Date, Warehouse, Type, Qty, Amount) ... -->
                <div>
                    <InputLabel value="Reference #" />
                    <TextInput v-model="form.reference_no" class="w-full" required />
                    <InputError :message="form.errors.reference_no" />
                </div>
                <div>
                    <InputLabel value="Adjustment Date" />
                    <TextInput type="date" v-model="form.adjustment_date" class="w-full" required />
                </div>
                <div>
                    <InputLabel value="Warehouse" />
                    <select v-model="form.warehouse_id" class="theme-form-input w-full" required>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Type" />
                    <select v-model="form.adjustment_type" class="theme-form-input w-full">
                        <option value="increase">Increase (+)</option>
                        <option value="decrease">Decrease (-)</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Qty" />
                    <TextInput type="number" step="0.01" v-model="form.total_quantity" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Amount" />
                    <TextInput type="number" step="0.01" v-model="form.total_amount" class="w-full" />
                </div>
            </div>

            <!-- Added Remarks Field -->
            <div class="mt-6">
                <InputLabel for="remarks" value="Remarks" class="theme-form-label ml-1" />
                <textarea id="remarks" v-model="form.remarks" class="theme-form-input w-full h-24 pt-3 resize-none" placeholder="Enter adjustment details..."></textarea>
                <InputError :message="form.errors.remarks" class="mt-2 ml-1" />
            </div>

            <div class="flex justify-center pt-4">
                <PrimaryButton :disabled="form.processing">Update Adjustment</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
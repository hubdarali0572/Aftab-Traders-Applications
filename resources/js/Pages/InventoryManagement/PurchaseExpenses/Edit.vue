<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ expense: Object, purchases: Array });

const form = useForm({
    purchase_id: props.expense.purchase_id,
    expense_type: props.expense.expense_type,
    amount: props.expense.amount,
    remarks: props.expense.remarks ?? '',
    status: Boolean(props.expense.status),
});

const submit = () => form.put(route('purchase-expenses.update', props.expense.id));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Purchase Expense" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">Edit Purchase Expense</h2>
            <Link :href="route('purchase-expenses.index')" class="theme-form-back-link">Back to List</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Purchase Order" />
                    <select v-model="form.purchase_id" class="theme-form-input w-full" required>
                        <option v-for="p in purchases" :key="p.id" :value="p.id">{{ p.purchase_no }}</option>
                    </select>
                    <InputError :message="form.errors.purchase_id" />
                </div>
                <div>
                    <InputLabel value="Expense Type" />
                    <TextInput v-model="form.expense_type" class="w-full" required />
                    <InputError :message="form.errors.expense_type" />
                </div>
                <div>
                    <InputLabel value="Amount" />
                    <TextInput type="number" step="0.01" v-model="form.amount" class="w-full" required />
                    <InputError :message="form.errors.amount" />
                </div>
            </div>
            <div>
                <InputLabel value="Remarks" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24"></textarea>
            </div>
            <div class="flex justify-center pt-4">
                <PrimaryButton :disabled="form.processing">Update Expense</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

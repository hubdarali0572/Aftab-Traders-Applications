<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ purchases: Array });

const form = useForm({
    purchase_id: '',
    expense_type: '',
    amount: 0,
    remarks: '',
    status: true,
});

const submit = () => form.post(route('purchase-expenses.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Add Purchase Expense')" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-900">{{ $t('Add Purchase Expense') }}</h2>
            <Link :href="route('purchase-expenses.index')" class="theme-form-back-link">{{ $t('Back') }}</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel :value="$t('Purchase Order')" />
                    <select v-model="form.purchase_id" class="theme-form-input w-full" required>
                        <option value="" disabled>{{ $t('Select Purchase') }}</option>
                        <option v-for="p in purchases" :key="p.id" :value="p.id">{{ p.purchase_no }}</option>
                    </select>
                    <InputError :message="form.errors.purchase_id" />
                </div>
                <div>
                    <InputLabel :value="$t('Expense Type')" />
                    <TextInput v-model="form.expense_type" class="w-full" required placeholder="e.g. Freight, Customs" />
                    <InputError :message="form.errors.expense_type" />
                </div>
                <div>
                    <InputLabel :value="$t('Amount')" />
                    <TextInput type="number" step="0.01" v-model="form.amount" class="w-full" required />
                    <InputError :message="form.errors.amount" />
                </div>
            </div>
            <div>
                <InputLabel :value="$t('Remarks')" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24"></textarea>
            </div>
            <div class="flex justify-center"><PrimaryButton :disabled="form.processing">{{ $t('Save Expense') }}</PrimaryButton></div>
        </form>
    </AuthenticatedLayout>
</template>

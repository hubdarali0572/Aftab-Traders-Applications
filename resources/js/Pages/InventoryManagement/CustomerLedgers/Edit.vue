<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ ledger: Object, customers: Array, transactionTypes: Array });

const form = useForm({
    customer_id: props.ledger.customer_id,
    transaction_date: props.ledger.transaction_date ? props.ledger.transaction_date.substring(0, 10) : '',
    transaction_type: props.ledger.transaction_type,
    reference_no: props.ledger.reference_no ?? '',
    debit: props.ledger.debit,
    credit: props.ledger.credit,
    remarks: props.ledger.remarks ?? '',
    status: Boolean(props.ledger.status),
});

const submit = () => form.put(route('customer-ledgers.update', props.ledger.id));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Ledger Entry" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black text-slate-900">Edit Ledger Entry</h2>
                <p class="text-sm text-slate-500 font-medium">Record ID: #{{ ledger.id }}</p>
            </div>
            <Link :href="route('customer-ledgers.index')" class="theme-form-back-link">Back to List</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Customer" />
                    <select v-model="form.customer_id" class="theme-form-input w-full" required>
                        <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.customer_name }} ({{ c.customer_code }})</option>
                    </select>
                    <InputError :message="form.errors.customer_id" />
                </div>
                <div>
                    <InputLabel value="Transaction Date" />
                    <TextInput type="date" v-model="form.transaction_date" class="w-full" required />
                </div>
                <div>
                    <InputLabel value="Transaction Type" />
                    <select v-model="form.transaction_type" class="theme-form-input w-full" required>
                        <option v-for="t in transactionTypes" :key="t" :value="t">{{ t.replace(/_/g, ' ') }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Reference No" />
                    <TextInput v-model="form.reference_no" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Debit Amount" />
                    <TextInput type="number" step="0.01" v-model="form.debit" class="w-full" />
                    <InputError :message="form.errors.debit" />
                </div>
                <div>
                    <InputLabel value="Credit Amount" />
                    <TextInput type="number" step="0.01" v-model="form.credit" class="w-full" />
                    <InputError :message="form.errors.credit" />
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
            <div class="flex justify-center pt-4">
                <PrimaryButton :disabled="form.processing">Update Entry</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

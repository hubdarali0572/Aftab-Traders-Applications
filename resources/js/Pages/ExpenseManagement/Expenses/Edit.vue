<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    expense: Object,
    expenseHeads: Array,
    warehouses: Array,
});

const form = useForm({
    expense_no: props.expense.expense_no,
    expense_date: (props.expense.expense_date || '').toString().slice(0, 10),
    expense_head_id: props.expense.expense_head_id,
    warehouse_id: props.expense.warehouse_id ?? '',
    employee_name: props.expense.employee_name ?? '',
    payee_name: props.expense.payee_name ?? '',
    amount: props.expense.amount,
    payment_method: props.expense.payment_method,
    reference_no: props.expense.reference_no ?? '',
    invoice_no: props.expense.invoice_no ?? '',
    description: props.expense.description ?? '',
    remarks: props.expense.remarks ?? '',
    status: props.expense.status,
});

const submit = () => form.put(route('expenses.update', props.expense.id));
</script>

<template>
    <Head :title="$t('Edit Expense')" />

    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">{{ $t('Edit Expense') }}</h2>
                <p class="text-sm text-slate-800 mt-1 font-medium dark:text-slate-400">Update expense {{ expense.expense_no }}</p>
            </div>
            <Link :href="route('expenses.index')" class="theme-form-back-link">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="text-slate-900">{{ $t('Back to List') }}</span>
            </Link>
        </div>

        <div class="max-w-8xl mx-auto pb-24">
            <form @submit.prevent="submit" class="space-y-6">
                <div class="theme-form-card">
                    <div class="p-8 md:p-10">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-10 gap-y-8">
                            <div class="flex flex-col">
                                <InputLabel for="expense_no" :value="$t('Expense No')" class="theme-form-label ml-1" />
                                <TextInput id="expense_no" type="text" class="theme-form-input" v-model="form.expense_no" required />
                                <InputError :message="form.errors.expense_no" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="expense_date" value="Expense Date" class="theme-form-label ml-1" />
                                <TextInput id="expense_date" type="date" class="theme-form-input" v-model="form.expense_date" required />
                                <InputError :message="form.errors.expense_date" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="expense_head_id" :value="$t('Expense Head')" class="theme-form-label ml-1" />
                                <select id="expense_head_id" v-model="form.expense_head_id" class="theme-form-input w-full" required>
                                    <option v-for="h in expenseHeads" :key="h.id" :value="h.id">{{ h.head_code }} — {{ h.name }}</option>
                                </select>
                                <InputError :message="form.errors.expense_head_id" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="warehouse_id" :value="$t('Warehouse')" class="theme-form-label ml-1" />
                                <select id="warehouse_id" v-model="form.warehouse_id" class="theme-form-input w-full" required>
                                    <option value="" disabled>{{ $t('Select warehouse') }}</option>
                                    <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                                </select>
                                <InputError :message="form.errors.warehouse_id" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="employee_name" value="Employee Name" class="theme-form-label ml-1" />
                                <TextInput id="employee_name" type="text" class="theme-form-input" v-model="form.employee_name" />
                                <InputError :message="form.errors.employee_name" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="payee_name" value="Payee Name" class="theme-form-label ml-1" />
                                <TextInput id="payee_name" type="text" class="theme-form-input" v-model="form.payee_name" />
                                <InputError :message="form.errors.payee_name" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="amount" :value="$t('Amount')" class="theme-form-label ml-1" />
                                <TextInput id="amount" type="number" step="0.01" min="0.01" class="theme-form-input" v-model="form.amount" required />
                                <InputError :message="form.errors.amount" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="payment_method" :value="$t('Payment Method')" class="theme-form-label ml-1" />
                                <select id="payment_method" v-model="form.payment_method" class="theme-form-input w-full" required>
                                    <option value="cash">{{ $t('Cash') }}</option>
                                    <option value="bank">{{ $t('Bank') }}</option>
                                    <option value="cheque">{{ $t('Cheque') }}</option>
                                    <option value="online">{{ $t('Online') }}</option>
                                </select>
                                <InputError :message="form.errors.payment_method" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="status" :value="$t('Status')" class="theme-form-label ml-1" />
                                <select id="status" v-model="form.status" class="theme-form-input w-full" required>
                                    <option value="draft">{{ $t('Draft') }}</option>
                                    <option value="approved">{{ $t('Approved') }}</option>
                                    <option value="paid">{{ $t('Paid') }}</option>
                                    <option value="cancelled">{{ $t('Cancelled') }}</option>
                                </select>
                                <InputError :message="form.errors.status" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="reference_no" :value="$t('Reference No')" class="theme-form-label ml-1" />
                                <TextInput id="reference_no" type="text" class="theme-form-input" v-model="form.reference_no" />
                                <InputError :message="form.errors.reference_no" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="invoice_no" :value="$t('Invoice No')" class="theme-form-label ml-1" />
                                <TextInput id="invoice_no" type="text" class="theme-form-input" v-model="form.invoice_no" />
                                <InputError :message="form.errors.invoice_no" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col md:col-span-3">
                                <InputLabel for="description" :value="$t('Description')" class="theme-form-label ml-1" />
                                <textarea id="description" class="theme-form-input min-h-[100px] resize-y" v-model="form.description"></textarea>
                                <InputError :message="form.errors.description" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col md:col-span-3">
                                <InputLabel for="remarks" :value="$t('Remarks')" class="theme-form-label ml-1" />
                                <textarea id="remarks" class="theme-form-input min-h-[80px] resize-y" v-model="form.remarks"></textarea>
                                <InputError :message="form.errors.remarks" class="mt-2 ml-1" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-center pt-4">
                    <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95" :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing">
                        {{ $t('Update Expense') }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

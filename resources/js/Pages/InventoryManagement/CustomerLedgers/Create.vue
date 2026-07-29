<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    customers: Array,
    transactionTypes: Array,
    defaultCustomerId: [String, Number],
    defaultMode: { type: String, default: 'entry' },
});

const isPaymentMode = computed(() => props.defaultMode === 'payment');

const form = useForm({
    customer_id: props.defaultCustomerId ? Number(props.defaultCustomerId) : '',
    redirect_customer_id: props.defaultCustomerId ? Number(props.defaultCustomerId) : '',
    transaction_date: new Date().toISOString().split('T')[0],
    transaction_type: isPaymentMode.value ? 'payment_received' : 'payment_received',
    reference_no: '',
    debit: 0,
    credit: '',
    remarks: '',
    status: true,
});

watch(() => form.transaction_type, (type) => {
    if (type === 'payment_received' || type === 'credit_note') {
        form.debit = 0;
    } else if (type === 'debit_note') {
        form.credit = 0;
    }
});

const formatType = (type) => type?.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) ?? '—';

const showDebit = computed(() => ['debit_note', 'adjustment'].includes(form.transaction_type));
const showCredit = computed(() => ['payment_received', 'credit_note', 'adjustment'].includes(form.transaction_type));

const submit = () => form.post(route('customer-ledgers.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="isPaymentMode ? $t('Record Payment') : $t('Ledger Entry')" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100">
                    {{ isPaymentMode ? $t('Record Payment') : $t('Ledger Entry') }}
                </h2>
                <p class="text-sm text-slate-500 font-medium mt-1 dark:text-slate-400">
                    {{ isPaymentMode ? $t('Record a payment received from the customer.') : $t('Post a manual debit or credit adjustment.') }}
                </p>
            </div>
            <Link :href="route('customer-ledgers.index')" class="theme-form-back-link">{{ $t('Back') }}</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel :value="$t('Customer')" />
                    <select v-model="form.customer_id" class="theme-form-input w-full" required>
                        <option value="" disabled>{{ $t('Select Customer') }}</option>
                        <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.customer_code }} — {{ c.customer_name }}</option>
                    </select>
                    <InputError :message="form.errors.customer_id" />
                </div>
                <div>
                    <InputLabel :value="$t('Transaction Date')" />
                    <TextInput type="date" v-model="form.transaction_date" class="w-full" required />
                    <InputError :message="form.errors.transaction_date" />
                </div>
                <div v-if="!isPaymentMode">
                    <InputLabel :value="$t('Transaction Type')" />
                    <select v-model="form.transaction_type" class="theme-form-input w-full" required>
                        <option v-for="t in transactionTypes" :key="t" :value="t">{{ formatType(t) }}</option>
                    </select>
                    <InputError :message="form.errors.transaction_type" />
                </div>
                <div v-else>
                    <InputLabel :value="$t('Transaction Type')" />
                    <div class="theme-form-input w-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 font-bold">{{ $t('Payment Received') }}</div>
                </div>
                <div>
                    <InputLabel :value="$t('Reference No.')" />
                    <TextInput v-model="form.reference_no" class="w-full" :placeholder="$t('Optional')" />
                </div>
                <div v-if="showDebit">
                    <InputLabel :value="$t('Debit Amount')" />
                    <TextInput type="number" step="0.01" min="0" v-model="form.debit" class="w-full" />
                    <InputError :message="form.errors.debit" />
                </div>
                <div v-if="showCredit">
                    <InputLabel :value="isPaymentMode ? $t('Payment Amount') : $t('Credit Amount')" />
                    <TextInput type="number" step="0.01" min="0" v-model="form.credit" class="w-full" required />
                    <InputError :message="form.errors.credit" />
                </div>
            </div>
            <div>
                <InputLabel :value="$t('Remarks')" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24" :placeholder="$t('Optional')"></textarea>
            </div>
            <div class="flex justify-center gap-4">
                <PrimaryButton :disabled="form.processing">{{ $t('Save Entry') }}</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

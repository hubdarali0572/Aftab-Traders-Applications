<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ customerTypes: Array, defaultType: { type: String, default: 'retail' } });

const form = useForm({
    customer_code: 'CUS-' + Date.now(),
    customer_type: props.defaultType || 'retail',
    company_name: '',
    customer_name: '',
    phone: '',
    alternate_phone: '',
    email: '',
    address: '',
    city: '',
    state: '',
    country: '',
    opening_balance: 0,
    opening_balance_type: 'debit',
    credit_limit: 0,
    tax_number: '',
    remarks: '',
    status: true,
});

const submit = () => form.post(route('customers.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Customer" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ $t('New Customer') }}</h2>
            <Link :href="route('customers.index')" class="theme-form-back-link">{{ $t('Back to List') }}</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Customer Code" />
                    <TextInput v-model="form.customer_code" class="w-full" required />
                    <InputError :message="form.errors.customer_code" />
                </div>
                <div>
                    <InputLabel value="Customer Type" />
                    <select v-model="form.customer_type" class="theme-form-input w-full" required>
                        <option v-for="t in customerTypes" :key="t" :value="t">{{ t.replace(/_/g, ' ') }}</option>
                    </select>
                    <InputError :message="form.errors.customer_type" />
                </div>
                <div>
                    <InputLabel value="Customer Name" />
                    <TextInput v-model="form.customer_name" class="w-full" required />
                    <InputError :message="form.errors.customer_name" />
                </div>
                <div>
                    <InputLabel value="Company Name" />
                    <TextInput v-model="form.company_name" class="w-full" />
                    <InputError :message="form.errors.company_name" />
                </div>
                <div>
                    <InputLabel :value="$t('Phone')" />
                    <TextInput v-model="form.phone" class="w-full" required />
                    <InputError :message="form.errors.phone" />
                </div>
                <div>
                    <InputLabel :value="$t('Alternate Phone')" />
                    <TextInput v-model="form.alternate_phone" class="w-full" />
                </div>
                <div>
                    <InputLabel :value="$t('Email')" />
                    <TextInput type="email" v-model="form.email" class="w-full" />
                    <InputError :message="form.errors.email" />
                </div>
                <div>
                    <InputLabel :value="$t('Tax Number')" />
                    <TextInput v-model="form.tax_number" class="w-full" />
                </div>
                <div>
                    <InputLabel :value="$t('Credit Limit')" />
                    <TextInput type="number" step="0.01" v-model="form.credit_limit" class="w-full" />
                </div>
                <div>
                    <InputLabel :value="$t('Opening Balance')" />
                    <TextInput type="number" step="0.01" v-model="form.opening_balance" class="w-full" />
                    <InputError :message="form.errors.opening_balance" />
                </div>
                <div>
                    <InputLabel value="Opening Balance Type" />
                    <select v-model="form.opening_balance_type" class="theme-form-input w-full">
                        <option value="debit">{{ $t('Debit (Receivable)') }}</option>
                        <option value="credit">{{ $t('Credit (Payable)') }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel :value="$t('Status')" />
                    <button type="button" @click="form.status = !form.status" class="mt-2 relative inline-flex h-6 w-11 items-center rounded-full" :class="form.status ? 'bg-indigo-600' : 'bg-slate-300'">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition" :class="form.status ? 'translate-x-6' : 'translate-x-1'" />
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <InputLabel :value="$t('Address')" />
                    <textarea v-model="form.address" class="theme-form-input w-full h-24"></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <InputLabel :value="$t('City')" />
                        <TextInput v-model="form.city" class="w-full" />
                    </div>
                    <div>
                        <InputLabel value="State" />
                        <TextInput v-model="form.state" class="w-full" />
                    </div>
                    <div>
                        <InputLabel value="Country" />
                        <TextInput v-model="form.country" class="w-full" />
                    </div>
                </div>
            </div>

            <div>
                <InputLabel :value="$t('Remarks')" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24"></textarea>
            </div>

            <div class="flex justify-center"><PrimaryButton :disabled="form.processing">{{ $t('Save Customer') }}</PrimaryButton></div>
        </form>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ customer: Object, customerTypes: Array, outstanding: Number });

const form = useForm({
    customer_type: props.customer.customer_type,
    company_name: props.customer.company_name ?? '',
    customer_name: props.customer.customer_name,
    phone: props.customer.phone,
    email: props.customer.email ?? '',
    address: props.customer.address ?? '',
    city: props.customer.city ?? '',
    opening_balance: props.customer.opening_balance,
    remarks: props.customer.remarks ?? '',
    status: Boolean(props.customer.status),
});

const submit = () => form.put(route('customers.update', props.customer.id));
const money = (v) => `$${Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Edit Customer')" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ $t('Edit Customer') }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ customer.customer_code }} · {{ customer.customer_name }}</p>
            </div>
            <Link :href="route('customers.show', customer.id)" class="theme-form-back-link font-bold">{{ $t('Back to Profile') }}</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-8">
            <div>
                <h3 class="text-xs font-black uppercase text-slate-400 mb-4 tracking-widest">{{ $t('Basic Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <InputLabel :value="$t('Customer Code')" />
                        <TextInput :model-value="customer.customer_code" class="w-full bg-slate-50 dark:bg-slate-800" disabled />
                        <p class="text-[10px] text-slate-400 mt-1">{{ $t('Auto-generated — cannot be changed') }}</p>
                    </div>
                    <div>
                        <InputLabel :value="$t('Customer Name')" />
                        <TextInput v-model="form.customer_name" class="w-full" required />
                        <InputError :message="form.errors.customer_name" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Business Name')" />
                        <TextInput v-model="form.company_name" class="w-full" :placeholder="$t('Optional')" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Customer Type')" />
                        <select v-model="form.customer_type" class="theme-form-input w-full" required>
                            <option v-for="t in customerTypes" :key="t" :value="t">{{ t.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase()) }}</option>
                        </select>
                        <InputError :message="form.errors.customer_type" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Mobile Number')" />
                        <TextInput v-model="form.phone" class="w-full" required />
                        <InputError :message="form.errors.phone" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Email')" />
                        <TextInput type="email" v-model="form.email" class="w-full" :placeholder="$t('Optional')" />
                        <InputError :message="form.errors.email" />
                    </div>
                    <div>
                        <InputLabel :value="$t('City')" />
                        <TextInput v-model="form.city" class="w-full" />
                    </div>
                    <div class="md:col-span-2">
                        <InputLabel :value="$t('Address')" />
                        <textarea v-model="form.address" class="theme-form-input w-full h-20"></textarea>
                    </div>
                    <div>
                        <InputLabel :value="$t('Status')" />
                        <button type="button" @click="form.status = !form.status" class="mt-2 relative inline-flex h-6 w-11 items-center rounded-full" :class="form.status ? 'bg-indigo-600' : 'bg-slate-300'">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition" :class="form.status ? 'translate-x-6' : 'translate-x-1'" />
                        </button>
                        <span class="ml-3 text-sm font-bold text-slate-600">{{ form.status ? $t('Active') : $t('Inactive') }}</span>
                        <p v-if="!form.status" class="text-[10px] text-amber-600 mt-1">{{ $t('Inactive customers cannot receive new transactions.') }}</p>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="text-xs font-black uppercase text-slate-400 mb-4 tracking-widest">{{ $t('Account Balance') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl">
                    <div>
                        <InputLabel :value="$t('Opening Balance')" />
                        <TextInput type="number" step="0.01" min="0" v-model="form.opening_balance" class="w-full" />
                        <p class="text-[10px] text-slate-400 mt-1">{{ $t('Previous due amount from this customer') }}</p>
                        <InputError :message="form.errors.opening_balance" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Outstanding Balance')" />
                        <div class="theme-form-input w-full bg-slate-50 dark:bg-slate-800/80 font-bold" :class="Number(outstanding) > 0 ? 'text-rose-600' : 'text-emerald-600'">
                            {{ money(outstanding) }}
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">{{ $t('Updated automatically from sales and payments') }}</p>
                    </div>
                </div>
            </div>

            <div>
                <InputLabel :value="$t('Remarks')" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-20"></textarea>
            </div>

            <div class="flex justify-center"><PrimaryButton :disabled="form.processing">{{ $t('Update Customer') }}</PrimaryButton></div>
        </form>
    </AuthenticatedLayout>
</template>

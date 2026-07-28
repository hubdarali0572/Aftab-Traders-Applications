<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    warehouses: Array,
    products: Array,
    transaction_types: Array,
});

const form = useForm({
    warehouse_id: '',
    product_id: '',
    transaction_type: 'opening_stock',
    reference_type: 'Manual Entry',
    reference_id: 1,
    reference_no: '',
    transaction_date: new Date().toISOString().split('T')[0],
    quantity_in: 0,
    quantity_out: 0,
    unit_cost: 0,
    remarks: '',
    status: true,
});

const submit = () => {
    form.post(route('stock-ledgers.store'));
};
</script>

<template>
    <Head title="Create Ledger Entry" />
    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">{{ $t('Create Stock Ledger Entry') }}</h2>
            </div>
            <Link :href="route('stock-ledgers.index')" class="theme-form-back-link">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                {{ $t('Back to Ledger') }}
            </Link>
        </div>

        <div class="max-w-8xl mx-auto pb-5">
            <form @submit.prevent="submit" class="space-y-4">
                <div class="theme-form-card">
                    <div class="p-8 md:p-10">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-10 gap-y-3">
                            
                            <!-- Relationships -->
                            <div class="flex flex-col">
                                <InputLabel for="warehouse_id" :value="$t('Warehouse')" class="theme-form-label ml-1" />
                                <select id="warehouse_id" class="theme-form-input" v-model="form.warehouse_id" required>
                                    <option value="" disabled>{{ $t('Select warehouse') }}</option>
                                    <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                                </select>
                                <InputError :message="form.errors.warehouse_id" class="mt-2" />
                            </div>

                            <div class="flex flex-col">
                                <InputLabel for="product_id" :value="$t('Product')" class="theme-form-label ml-1" />
                                <select id="product_id" class="theme-form-input" v-model="form.product_id" required>
                                    <option value="" disabled>{{ $t('Select product') }}</option>
                                    <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                                <InputError :message="form.errors.product_id" class="mt-2" />
                            </div>

                            <div class="flex flex-col">
                                <InputLabel for="transaction_type" value="Transaction Type" class="theme-form-label ml-1" />
                                <select id="transaction_type" class="theme-form-input" v-model="form.transaction_type" required>
                                    <option v-for="type in transaction_types" :key="type" :value="type">
                                        {{ type.replace('_', ' ').toUpperCase() }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.transaction_type" class="mt-2" />
                            </div>

                            <!-- References -->
                            <div class="flex flex-col">
                                <InputLabel for="reference_no" value="Reference Number" class="theme-form-label ml-1" />
                                <TextInput id="reference_no" type="text" class="theme-form-input" v-model="form.reference_no" placeholder="PO-001, INV-002 etc." />
                                <InputError :message="form.errors.reference_no" class="mt-2" />
                            </div>

                            <div class="flex flex-col">
                                <InputLabel for="transaction_date" :value="$t('Transaction Date')" class="theme-form-label ml-1" />
                                <TextInput id="transaction_date" type="date" class="theme-form-input" v-model="form.transaction_date" required />
                                <InputError :message="form.errors.transaction_date" class="mt-2" />
                            </div>

                            <div class="flex flex-col">
                                <InputLabel for="unit_cost" :value="$t('Unit Cost')" class="theme-form-label ml-1" />
                                <TextInput id="unit_cost" type="number" step="0.01" class="theme-form-input" v-model="form.unit_cost" required />
                                <InputError :message="form.errors.unit_cost" class="mt-2" />
                            </div>

                            <!-- Movement -->
                            <div class="flex flex-col">
                                <InputLabel for="quantity_in" value="Quantity In (+)" class="theme-form-label ml-1 text-emerald-600" />
                                <TextInput id="quantity_in" type="number" step="0.01" class="theme-form-input border-emerald-200" v-model="form.quantity_in" />
                                <InputError :message="form.errors.quantity_in" class="mt-2" />
                            </div>

                            <div class="flex flex-col">
                                <InputLabel for="quantity_out" value="Quantity Out (-)" class="theme-form-label ml-1 text-rose-600" />
                                <TextInput id="quantity_out" type="number" step="0.01" class="theme-form-input border-rose-200" v-model="form.quantity_out" />
                                <InputError :message="form.errors.quantity_out" class="mt-2" />
                            </div>

                            <div class="flex flex-col">
                                <InputLabel :value="$t('Status')" class="theme-form-label ml-1" />
                                <label class="inline-flex items-center gap-3 mt-2 cursor-pointer">
                                    <button type="button" @click="form.status = !form.status" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors" :class="form.status ? 'bg-indigo-600' : 'bg-slate-300'">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform" :class="form.status ? 'translate-x-6' : 'translate-x-1'" />
                                    </button>
                                    <span class="text-sm font-bold text-slate-700">{{ form.status ? 'Active' : 'Inactive' }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="mt-6 flex flex-col">
                            <InputLabel for="remarks" value="Remarks / Notes" class="theme-form-label ml-1" />
                            <textarea id="remarks" v-model="form.remarks" class="theme-form-input h-24 pt-3" placeholder="Enter transaction details..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-center pt-4">
                    <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95" :disabled="form.processing">
                        {{ $t('Save Ledger Entry') }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    warehouses: Array,
    products: Array,
    stocks: Array,
});

const form = useForm({
    reference_no: 'TRF-' + Date.now(),
    transfer_date: new Date().toISOString().split('T')[0],
    product_id: '',
    from_warehouse_id: '',
    to_warehouse_id: '',
    quantity: '',
    remarks: '',
    status: true,
});

const availableQuantity = computed(() => {
    if (!form.from_warehouse_id || !form.product_id) {
        return null;
    }

    const row = props.stocks.find(
        (stock) =>
            Number(stock.warehouse_id) === Number(form.from_warehouse_id) &&
            Number(stock.product_id) === Number(form.product_id)
    );

    return row ? Number(row.quantity) : 0;
});

const quantityWarning = computed(() => {
    if (availableQuantity.value === null || !form.quantity) {
        return null;
    }

    const requested = parseFloat(form.quantity);
    if (requested > availableQuantity.value) {
        return `Transfer quantity exceeds available stock. Available: ${availableQuantity.value}, requested: ${requested}.`;
    }

    return null;
});

const submit = () => form.post(route('stock-transfers.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('New Stock Transfer')" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ $t('New Stock Transfer') }}</h2>
            <Link :href="route('stock-transfers.index')" class="theme-form-back-link">{{ $t('Back to List') }}</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel :value="$t('Reference Number')" />
                    <TextInput v-model="form.reference_no" class="w-full theme-form-input" readonly />
                    <InputError :message="form.errors.reference_no" />
                </div>
                <div>
                    <InputLabel :value="$t('Transfer Date')" />
                    <TextInput type="date" v-model="form.transfer_date" class="w-full theme-form-input" required />
                    <InputError :message="form.errors.transfer_date" />
                </div>
                <div>
                    <InputLabel :value="$t('Status')" />
                    <button type="button" @click="form.status = !form.status" class="mt-2 relative inline-flex h-6 w-11 items-center rounded-full" :class="form.status ? 'bg-indigo-600' : 'bg-slate-300'">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition" :class="form.status ? 'translate-x-6' : 'translate-x-1'" />
                    </button>
                    <InputError :message="form.errors.status" />
                </div>
                <div>
                    <InputLabel :value="$t('Product')" />
                    <select v-model="form.product_id" class="theme-form-input w-full" required>
                        <option value="" disabled>{{ $t('Select Product') }}</option>
                        <option v-for="product in products" :key="product.id" :value="product.id">{{ product.name }}</option>
                    </select>
                    <InputError :message="form.errors.product_id" />
                </div>
                <div>
                    <InputLabel :value="$t('From Warehouse')" />
                    <select v-model="form.from_warehouse_id" class="theme-form-input w-full" required>
                        <option value="" disabled>{{ $t('Select Source Warehouse') }}</option>
                        <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id" :disabled="warehouse.id == form.to_warehouse_id">
                            {{ warehouse.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.from_warehouse_id" />
                </div>
                <div>
                    <InputLabel :value="$t('To Warehouse')" />
                    <select v-model="form.to_warehouse_id" class="theme-form-input w-full" required>
                        <option value="" disabled>{{ $t('Select Destination Warehouse') }}</option>
                        <option v-for="warehouse in warehouses" :key="warehouse.id" :value="warehouse.id" :disabled="warehouse.id == form.from_warehouse_id">
                            {{ warehouse.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.to_warehouse_id" />
                </div>
                <div>
                    <InputLabel :value="$t('Available Quantity')" />
                    <TextInput
                        :model-value="availableQuantity === null ? '—' : availableQuantity"
                        class="w-full theme-form-input bg-slate-50 dark:bg-slate-800"
                        readonly
                    />
                </div>
                <div>
                    <InputLabel :value="$t('Transfer Quantity')" />
                    <TextInput type="number" step="0.01" min="0.01" v-model="form.quantity" class="w-full theme-form-input" required />
                    <InputError :message="form.errors.quantity" />
                    <p v-if="quantityWarning" class="mt-2 text-sm font-semibold text-rose-600">{{ quantityWarning }}</p>
                </div>
            </div>

            <div>
                <InputLabel :value="$t('Remarks')" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24" :placeholder="$t('Optional notes...')"></textarea>
                <InputError :message="form.errors.remarks" />
            </div>

            <div class="flex justify-center">
                <PrimaryButton :disabled="form.processing || !!quantityWarning">{{ $t('Save Transfer') }}</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    ledger: Object,
    warehouses: Array,
    products: Array,
    transaction_types: Array,
});

const form = useForm({
    warehouse_id: props.ledger.warehouse_id,
    product_id: props.ledger.product_id,
    transaction_type: props.ledger.transaction_type,
    reference_no: props.ledger.reference_no,
    // FIX: Slicing ensures the date is YYYY-MM-DD for the HTML input
    transaction_date: props.ledger.transaction_date ? props.ledger.transaction_date.substring(0, 10) : '',
    quantity_in: props.ledger.quantity_in,
    quantity_out: props.ledger.quantity_out,
    unit_cost: props.ledger.unit_cost,
    remarks: props.ledger.remarks,
    status: props.ledger.status,
});

const submit = () => {
    form.put(route('stock-ledgers.update', props.ledger.id));
};
</script>

<template>
    <Head :title="$t('Edit Ledger Entry')" />
    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-700">{{ $t('Edit Stock Ledger') }}</h2>
            <Link :href="route('stock-ledgers.index')" class="theme-form-back-link">{{ $t('Back to List') }}</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel :value="$t('Warehouse')" />
                    <select v-model="form.warehouse_id" class="theme-form-input w-full">
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                    <InputError :message="form.errors.warehouse_id" />
                </div>
                <div>
                    <InputLabel :value="$t('Product')" />
                    <select v-model="form.product_id" class="theme-form-input w-full">
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <InputError :message="form.errors.product_id" />
                </div>
                <div>
                    <InputLabel :value="$t('Type')" />
                    <select v-model="form.transaction_type" class="theme-form-input w-full">
                        <option v-for="t in transaction_types" :key="t" :value="t">{{ t.toUpperCase() }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Reference #" />
                    <TextInput v-model="form.reference_no" class="w-full" />
                </div>
                <div>
                    <InputLabel :value="$t('Date')" />
                    <TextInput type="date" v-model="form.transaction_date" class="w-full" />
                    <InputError :message="form.errors.transaction_date" />
                </div>
                <div>
                    <InputLabel :value="$t('Unit Cost')" />
                    <TextInput type="number" step="0.01" v-model="form.unit_cost" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Qty In (+)" class="text-emerald-600" />
                    <TextInput type="number" v-model="form.quantity_in" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Qty Out (-)" class="text-rose-600" />
                    <TextInput type="number" v-model="form.quantity_out" class="w-full" />
                </div>
                <div>
                    <InputLabel :value="$t('Status')" />
                    <button type="button" @click="form.status = !form.status" class="mt-2 relative inline-flex h-6 w-11 items-center rounded-full" :class="form.status ? 'bg-indigo-600' : 'bg-slate-300'">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition" :class="form.status ? 'translate-x-6' : 'translate-x-1'" />
                    </button>
                </div>
            </div>
            <div>
                <InputLabel :value="$t('Remarks')" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24"></textarea>
            </div>
            <div class="flex justify-center">
                <PrimaryButton :disabled="form.processing">{{ $t('Update Ledger Entry') }}</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    stock: Object,
    warehouses: Array,
    products: Array,
});

const form = useForm({
    warehouse_id: props.stock.warehouse_id,
    product_id: props.stock.product_id,
    quantity: props.stock.quantity,
    reserved_quantity: props.stock.reserved_quantity,
    average_cost: props.stock.average_cost,
    minimum_stock: props.stock.minimum_stock,
    maximum_stock: props.stock.maximum_stock,
    reorder_level: props.stock.reorder_level,
    last_received_at: props.stock.last_received_at
        ? props.stock.last_received_at.slice(0, 10)
        : '',
    last_issued_at: props.stock.last_issued_at
        ? props.stock.last_issued_at.slice(0, 10)
        : '',
    status: !!props.stock.status,
});

const submit = () => {
    form.put(route('warehouse-stocks.update', props.stock.id));
};
</script>

<template>
    <Head :title="$t('Edit Warehouse Stock')" />
    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">{{ $t('Edit Warehouse Stock') }}</h2>
            </div>
            <Link :href="route('warehouse-stocks.index')" class="theme-form-back-link">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="text-slate-900">{{ $t('Back to Stock List') }}</span>
            </Link>
        </div>

        <div class="max-w-8xl mx-auto pb-5">
            <form @submit.prevent="submit" class="space-y-4">
                <div class="theme-form-card">
                    <div class="p-8 md:p-10">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-3">
                            <div class="flex flex-col">
                                <InputLabel for="warehouse_id" :value="$t('Warehouse')" class="theme-form-label ml-1" />
                                <select id="warehouse_id" class="theme-form-input" v-model="form.warehouse_id" required>
                                    <option value="" disabled>{{ $t('Select warehouse') }}</option>
                                    <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                                </select>
                                <InputError :message="form.errors.warehouse_id" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="product_id" :value="$t('Product')" class="theme-form-label ml-1" />
                                <select id="product_id" class="theme-form-input" v-model="form.product_id" required>
                                    <option value="" disabled>{{ $t('Select product') }}</option>
                                    <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                                <InputError :message="form.errors.product_id" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="quantity" :value="$t('Quantity')" class="theme-form-label ml-1" />
                                <TextInput id="quantity" type="number" step="0.01" class="theme-form-input" v-model="form.quantity" required placeholder="0.00" />
                                <InputError :message="form.errors.quantity" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="reserved_quantity" :value="$t('Reserved Quantity')" class="theme-form-label ml-1" />
                                <TextInput id="reserved_quantity" type="number" step="0.01" class="theme-form-input" v-model="form.reserved_quantity" placeholder="0.00" />
                                <InputError :message="form.errors.reserved_quantity" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="average_cost" :value="$t('Average Cost')" class="theme-form-label ml-1" />
                                <TextInput id="average_cost" type="number" step="0.01" class="theme-form-input" v-model="form.average_cost" placeholder="0.00" />
                                <InputError :message="form.errors.average_cost" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="minimum_stock" :value="$t('Minimum Stock')" class="theme-form-label ml-1" />
                                <TextInput id="minimum_stock" type="number" step="0.01" class="theme-form-input" v-model="form.minimum_stock" placeholder="0.00" />
                                <InputError :message="form.errors.minimum_stock" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="maximum_stock" :value="$t('Maximum Stock')" class="theme-form-label ml-1" />
                                <TextInput id="maximum_stock" type="number" step="0.01" class="theme-form-input" v-model="form.maximum_stock" :placeholder="$t('Optional')" />
                                <InputError :message="form.errors.maximum_stock" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="reorder_level" :value="$t('Reorder Level')" class="theme-form-label ml-1" />
                                <TextInput id="reorder_level" type="number" step="0.01" class="theme-form-input" v-model="form.reorder_level" placeholder="0.00" />
                                <InputError :message="form.errors.reorder_level" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="last_received_at" :value="$t('Last Received At')" class="theme-form-label ml-1" />
                                <TextInput id="last_received_at" type="date" class="theme-form-input" v-model="form.last_received_at" />
                                <InputError :message="form.errors.last_received_at" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="last_issued_at" :value="$t('Last Issued At')" class="theme-form-label ml-1" />
                                <TextInput id="last_issued_at" type="date" class="theme-form-input" v-model="form.last_issued_at" />
                                <InputError :message="form.errors.last_issued_at" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="status" :value="$t('Status')" class="theme-form-label ml-1" />
                                <label class="inline-flex items-center gap-3 mt-2 cursor-pointer select-none">
                                    <button type="button" role="switch" :aria-checked="form.status" @click="form.status = !form.status" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none" :class="form.status ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-600'">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200" :class="form.status ? 'translate-x-6' : 'translate-x-1'" />
                                    </button>
                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ form.status ? 'Active' : 'Inactive' }}</span>
                                </label>
                                <InputError :message="form.errors.status" class="mt-2 ml-1" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-center pt-4">
                    <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95" :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing">{{ $t('Update Stock Entry') }}</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    detail: Object,
    opening_stocks: Array,
    products: Array
});

const form = useForm({
    opening_stock_id: props.detail.opening_stock_id,
    product_id: props.detail.product_id,
    quantity: props.detail.quantity,
    unit_cost: props.detail.unit_cost,
    batch_no: props.detail.batch_no,
    serial_no: props.detail.serial_no,
    // Fix: Slice date for HTML5 input compatibility
    expiry_date: props.detail.expiry_date ? props.detail.expiry_date.substring(0, 10) : '',
    remarks: props.detail.remarks,
    status: Boolean(props.detail.status),
});

// Automatically update total cost display as user edits quantity or unit cost
const total_cost = computed(() => {
    const total = parseFloat(form.quantity || 0) * parseFloat(form.unit_cost || 0);
    return total.toLocaleString(undefined, { minimumFractionDigits: 2 });
});

const submit = () => {
    form.put(route('opening-stock-details.update', props.detail.id));
};
</script>

<template>
    <Head title="Edit Line Item" />
    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">Edit Detail Item</h2>
                <p class="text-sm font-medium text-slate-500">Record ID: #{{ detail.id }}</p>
            </div>
            <Link :href="route('opening-stock-details.index')" class="theme-form-back-link">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Back to List
            </Link>
        </div>

        <div class="max-w-8xl mx-auto pb-10">
            <form @submit.prevent="submit" class="space-y-4">
                <div class="theme-form-card">
                    <div class="p-8 md:p-10">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-6">
                            
                            <!-- Parent Selection -->
                            <div class="flex flex-col">
                                <InputLabel for="opening_stock_id" value="Opening Stock Reference" class="theme-form-label ml-1" />
                                <select id="opening_stock_id" class="theme-form-input" v-model="form.opening_stock_id" required>
                                    <option v-for="os in opening_stocks" :key="os.id" :value="os.id">{{ os.reference_no }}</option>
                                </select>
                                <InputError :message="form.errors.opening_stock_id" class="mt-2" />
                            </div>

                            <!-- Product Selection -->
                            <div class="flex flex-col">
                                <InputLabel for="product_id" value="Product" class="theme-form-label ml-1" />
                                <select id="product_id" class="theme-form-input" v-model="form.product_id" required>
                                    <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                                </select>
                                <InputError :message="form.errors.product_id" class="mt-2" />
                            </div>

                            <!-- Batch Info -->
                            <div class="flex flex-col">
                                <InputLabel for="batch_no" value="Batch Number" class="theme-form-label ml-1" />
                                <TextInput id="batch_no" type="text" class="theme-form-input" v-model="form.batch_no" />
                                <InputError :message="form.errors.batch_no" class="mt-2" />
                            </div>

                            <!-- Quantity & Cost -->
                            <div class="flex flex-col">
                                <InputLabel for="quantity" value="Quantity" class="theme-form-label ml-1" />
                                <TextInput id="quantity" type="number" step="0.01" class="theme-form-input" v-model="form.quantity" required />
                                <InputError :message="form.errors.quantity" class="mt-2" />
                            </div>

                            <div class="flex flex-col">
                                <InputLabel for="unit_cost" value="Unit Cost" class="theme-form-label ml-1" />
                                <TextInput id="unit_cost" type="number" step="0.01" class="theme-form-input" v-model="form.unit_cost" required />
                                <InputError :message="form.errors.unit_cost" class="mt-2" />
                            </div>

                            <div class="flex flex-col">
                                <InputLabel value="Calculated Total Cost" class="theme-form-label ml-1 text-emerald-600" />
                                <div class="theme-form-input bg-emerald-50 dark:bg-emerald-900/10 font-black text-emerald-700 border-emerald-200 cursor-not-allowed">
                                    ${{ total_cost }}
                                </div>
                            </div>

                            <!-- Tracking -->
                            <div class="flex flex-col">
                                <InputLabel for="expiry_date" value="Expiry Date" class="theme-form-label ml-1" />
                                <TextInput id="expiry_date" type="date" class="theme-form-input" v-model="form.expiry_date" />
                                <InputError :message="form.errors.expiry_date" class="mt-2" />
                            </div>

                            <div class="flex flex-col">
                                <InputLabel for="serial_no" value="Serial Number" class="theme-form-label ml-1" />
                                <TextInput id="serial_no" type="text" class="theme-form-input" v-model="form.serial_no" />
                                <InputError :message="form.errors.serial_no" class="mt-2" />
                            </div>

                            <!-- Status -->
                            <div class="flex flex-col">
                                <InputLabel value="Line Item Status" class="theme-form-label ml-1" />
                                <label class="inline-flex items-center gap-3 mt-2 cursor-pointer select-none">
                                    <button type="button" @click="form.status = !form.status" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200" :class="form.status ? 'bg-indigo-600' : 'bg-slate-300'">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200" :class="form.status ? 'translate-x-6' : 'translate-x-1'" />
                                    </button>
                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ form.status ? 'Active' : 'Inactive' }}</span>
                                </label>
                            </div>
                        </div>

                        <div class="mt-8 flex flex-col">
                            <InputLabel for="remarks" value="Line Item Remarks" class="theme-form-label ml-1" />
                            <textarea id="remarks" v-model="form.remarks" class="theme-form-input h-24 pt-3 resize-none"></textarea>
                            <InputError :message="form.errors.remarks" class="mt-2 ml-1" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-center pt-4">
                    <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95" :disabled="form.processing">
                        Update Detail Item
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
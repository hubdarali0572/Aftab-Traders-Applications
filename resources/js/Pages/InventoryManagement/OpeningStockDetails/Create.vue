<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ opening_stocks: Array, products: Array });
const form = useForm({
    opening_stock_id: '',
    product_id: '',
    quantity: 0,
    unit_cost: 0,
    batch_no: '',
    serial_no: '',
    expiry_date: '',
    remarks: '',
    status: true,
});

const total_cost = computed(() => (form.quantity * form.unit_cost).toFixed(2));
const submit = () => form.post(route('opening-stock-details.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Add Line Item')" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $t('Add Opening Stock Detail') }}</h2>
            <Link :href="route('opening-stock-details.index')" class="theme-form-back-link">{{ $t('Back to List') }}</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Opening Stock Parent" />
                    <select v-model="form.opening_stock_id" class="theme-form-input w-full" required>
                        <option value="" disabled>{{ $t('Select Parent Reference') }}</option>
                        <option v-for="os in opening_stocks" :key="os.id" :value="os.id">{{ os.reference_no }}</option>
                    </select>
                    <InputError :message="form.errors.opening_stock_id" />
                </div>
                <div>
                    <InputLabel :value="$t('Product')" />
                    <select v-model="form.product_id" class="theme-form-input w-full" required>
                        <option value="" disabled>{{ $t('Select Product') }}</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel :value="$t('Batch Number')" />
                    <TextInput v-model="form.batch_no" class="w-full" />
                </div>
                <div>
                    <InputLabel :value="$t('Quantity')" />
                    <TextInput type="number" step="0.01" v-model="form.quantity" class="w-full" />
                </div>
                <div>
                    <InputLabel :value="$t('Unit Cost')" />
                    <TextInput type="number" step="0.01" v-model="form.unit_cost" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Total Cost (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold text-emerald-600 cursor-not-allowed">{{ total_cost }}</div>
                </div>
                <div>
                    <InputLabel :value="$t('Expiry Date')" />
                    <TextInput type="date" v-model="form.expiry_date" class="w-full" />
                </div>
                <div>
                    <InputLabel :value="$t('Serial Number')" />
                    <TextInput v-model="form.serial_no" class="w-full" />
                </div>
                <div>
                    <InputLabel :value="$t('Status')" />
                    <button type="button" @click="form.status = !form.status" class="mt-2 relative inline-flex h-6 w-11 items-center rounded-full" :class="form.status ? 'bg-indigo-600' : 'bg-slate-300'">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition" :class="form.status ? 'translate-x-6' : 'translate-x-1'" />
                    </button>
                </div>
            </div>
            <div>
                <InputLabel :value="$t('Item Remarks')" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24"></textarea>
            </div>
            <div class="flex justify-center"><PrimaryButton :disabled="form.processing">{{ $t('Add Detail Item') }}</PrimaryButton></div>
        </form>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ detail: Object, transfers: Array, products: Array });

const form = useForm({
    stock_transfer_id: props.detail.stock_transfer_id,
    product_id: props.detail.product_id,
    quantity: props.detail.quantity,
    unit_cost: props.detail.unit_cost,
    batch_no: props.detail.batch_no ?? '',
    serial_no: props.detail.serial_no ?? '',
    expiry_date: props.detail.expiry_date ? props.detail.expiry_date.substring(0, 10) : '',
    remarks: props.detail.remarks ?? '',
    status: Boolean(props.detail.status),
});

const totalCost = computed(() => (parseFloat(form.quantity || 0) * parseFloat(form.unit_cost || 0)).toFixed(2));

const submit = () => form.put(route('stock-transfer-details.update', props.detail.id));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Edit Transfer Item')" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black text-slate-900">{{ $t('Edit Transfer Item') }}</h2>
                <p class="text-sm text-slate-500 font-medium">Record ID: #{{ detail.id }}</p>
            </div>
            <Link :href="route('stock-transfer-details.index')" class="theme-form-back-link">{{ $t('Back to List') }}</Link>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div class="theme-form-card p-8 md:p-10">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-10 gap-y-6">
                    <div>
                        <InputLabel value="Transfer Reference" />
                        <select v-model="form.stock_transfer_id" class="theme-form-input w-full" required>
                            <option v-for="t in transfers" :key="t.id" :value="t.id">{{ t.reference_no }}</option>
                        </select>
                        <InputError :message="form.errors.stock_transfer_id" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Product')" />
                        <select v-model="form.product_id" class="theme-form-input w-full" required>
                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                        <InputError :message="form.errors.product_id" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Quantity')" />
                        <TextInput type="number" step="0.01" v-model="form.quantity" class="w-full" />
                        <InputError :message="form.errors.quantity" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Unit Cost')" />
                        <TextInput type="number" step="0.01" v-model="form.unit_cost" class="w-full" />
                        <InputError :message="form.errors.unit_cost" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel value="Total Cost (Auto)" />
                        <div class="theme-form-input bg-slate-50 dark:bg-slate-800/50 font-black text-indigo-600">${{ totalCost }}</div>
                    </div>
                    <div>
                        <InputLabel :value="$t('Batch No')" />
                        <TextInput v-model="form.batch_no" class="w-full" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Serial No')" />
                        <TextInput v-model="form.serial_no" class="w-full" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Expiry Date')" />
                        <TextInput type="date" v-model="form.expiry_date" class="w-full" />
                    </div>
                    <div>
                        <InputLabel value="Item Status" />
                        <label class="inline-flex items-center gap-3 mt-2 cursor-pointer select-none">
                            <button type="button" @click="form.status = !form.status" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200" :class="form.status ? 'bg-indigo-600' : 'bg-slate-300'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200" :class="form.status ? 'translate-x-6' : 'translate-x-1'" />
                            </button>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ form.status ? 'Active' : 'Inactive' }}</span>
                        </label>
                    </div>
                </div>
                <div class="mt-8">
                    <InputLabel for="remarks" :value="$t('Remarks')" class="theme-form-label ml-1" />
                    <textarea id="remarks" v-model="form.remarks" class="theme-form-input w-full h-24 pt-3 resize-none" placeholder="Enter additional details..."></textarea>
                    <InputError :message="form.errors.remarks" class="mt-2 ml-1" />
                </div>
            </div>
            <div class="flex justify-center pt-4">
                <PrimaryButton :disabled="form.processing" class="theme-btn-primary px-12 py-4">{{ $t('Update Transfer Item') }}</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

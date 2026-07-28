<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ saleReturn: Object, sales: Array });

const form = useForm({
    reference_no: props.saleReturn.reference_no,
    sale_id: props.saleReturn.sale_id,
    return_date: props.saleReturn.return_date ? props.saleReturn.return_date.substring(0, 10) : '',
    remarks: props.saleReturn.remarks || '',
    status: Boolean(props.saleReturn.status),
});

const selectedSale = computed(() => props.sales.find((sale) => sale.id == form.sale_id));

const submit = () => form.put(route('sale-returns.update', props.saleReturn.id));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Edit Sales Return" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-800">Edit Sales Return</h2>
            <Link :href="route('sale-returns.index')" class="theme-form-back-link">Back to List</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Reference No" />
                    <TextInput v-model="form.reference_no" class="w-full" required />
                    <InputError :message="form.errors.reference_no" />
                </div>
                <div>
                    <InputLabel value="Return Date" />
                    <TextInput type="date" v-model="form.return_date" class="w-full" required />
                    <InputError :message="form.errors.return_date" />
                </div>
                <div>
                    <InputLabel value="Sales Invoice" />
                    <select v-model="form.sale_id" class="theme-form-input w-full" required>
                        <option value="" disabled>Select Invoice</option>
                        <option v-for="sale in sales" :key="sale.id" :value="sale.id">
                            {{ sale.invoice_no }} - {{ sale.customer?.customer_name || 'Walk-in' }}
                        </option>
                    </select>
                    <InputError :message="form.errors.sale_id" />
                </div>

                <div>
                    <InputLabel value="Customer" />
                    <div class="theme-form-input bg-slate-50 dark:bg-slate-700/50">{{ selectedSale?.customer?.customer_name || 'Walk-in' }}</div>
                </div>
                <div>
                    <InputLabel value="Warehouse" />
                    <div class="theme-form-input bg-slate-50 dark:bg-slate-700/50">{{ selectedSale?.warehouse?.name || '—' }}</div>
                </div>
                <div>
                    <InputLabel value="Invoice Reference" />
                    <div class="theme-form-input bg-slate-50 dark:bg-slate-700/50">{{ selectedSale?.invoice_no || '—' }}</div>
                </div>
            </div>

            <div>
                <InputLabel value="Remarks" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24"></textarea>
                <InputError :message="form.errors.remarks" />
            </div>

            <div class="flex justify-center">
                <PrimaryButton :disabled="form.processing">Update Return</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

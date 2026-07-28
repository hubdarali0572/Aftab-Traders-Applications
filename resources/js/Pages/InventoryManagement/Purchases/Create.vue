<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ warehouses: Array });

const form = useForm({
    purchase_no: 'PO-' + Date.now(),
    supplier_invoice_no: '',
    supplier_name: '',
    purchase_date: new Date().toISOString().split('T')[0],
    warehouse_id: '',
    discount: 0,
    tax: 0,
    shipping_cost: 0,
    other_charges: 0,
    paid_amount: 0,
    purchase_status: 'draft',
    remarks: '',
    status: true,
});

const grandTotal = computed(() => {
    const g = 0 - (parseFloat(form.discount) || 0) + (parseFloat(form.tax) || 0)
        + (parseFloat(form.shipping_cost) || 0) + (parseFloat(form.other_charges) || 0);
    return g.toFixed(2);
});

const dueAmount = computed(() => Math.max(0, parseFloat(grandTotal.value) - (parseFloat(form.paid_amount) || 0)).toFixed(2));

const submit = () => form.post(route('purchases.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Purchase" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-800">{{ $t('New Purchase Order') }}</h2>
            <Link :href="route('purchases.index')" class="theme-form-back-link">{{ $t('Back to List') }}</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Purchase No (PO #)" />
                    <TextInput v-model="form.purchase_no" class="w-full" required />
                    <InputError :message="form.errors.purchase_no" />
                </div>
                <div>
                    <InputLabel :value="$t('Purchase Date')" />
                    <TextInput type="date" v-model="form.purchase_date" class="w-full" required />
                </div>
                <div>
                    <InputLabel :value="$t('Warehouse')" />
                    <select v-model="form.warehouse_id" class="theme-form-input w-full" required>
                        <option value="" disabled>{{ $t('Select Warehouse') }}</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                    <InputError :message="form.errors.warehouse_id" />
                </div>
                <div>
                    <InputLabel value="Supplier Name" />
                    <TextInput v-model="form.supplier_name" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Supplier Invoice / Reference" />
                    <TextInput v-model="form.supplier_invoice_no" class="w-full" />
                </div>
                <div>
                    <InputLabel :value="$t('Purchase Status')" />
                    <select v-model="form.purchase_status" class="theme-form-input w-full">
                        <option value="draft">{{ $t('Draft') }}</option>
                        <option value="received">{{ $t('Received') }}</option>
                        <option value="cancelled">{{ $t('Cancelled') }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel :value="$t('Discount')" />
                    <TextInput type="number" step="0.01" v-model="form.discount" class="w-full" />
                </div>
                <div>
                    <InputLabel :value="$t('Tax')" />
                    <TextInput type="number" step="0.01" v-model="form.tax" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Shipping Cost" />
                    <TextInput type="number" step="0.01" v-model="form.shipping_cost" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Other Charges" />
                    <TextInput type="number" step="0.01" v-model="form.other_charges" class="w-full" />
                </div>
                <div>
                    <InputLabel :value="$t('Paid Amount')" />
                    <TextInput type="number" step="0.01" v-model="form.paid_amount" class="w-full" />
                </div>
                <div>
                    <InputLabel value="Grand Total (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold text-indigo-600">${{ grandTotal }}</div>
                </div>
                <div>
                    <InputLabel value="Due Amount (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold text-rose-600">${{ dueAmount }}</div>
                </div>
            </div>
            <div>
                <InputLabel :value="$t('Remarks')" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24"></textarea>
            </div>
            <div class="flex justify-center"><PrimaryButton :disabled="form.processing">{{ $t('Save Purchase') }}</PrimaryButton></div>
        </form>
    </AuthenticatedLayout>
</template>

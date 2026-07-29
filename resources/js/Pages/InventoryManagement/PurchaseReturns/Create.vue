<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PurchaseReturnItemsEditor from '@/Components/Inventory/PurchaseReturnItemsEditor.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ purchases: Array, warehouses: Array, products: Array, selectedPurchase: Object });

const form = useForm({
    reference_no: 'PR-' + Date.now(),
    purchase_id: props.selectedPurchase?.id ?? '',
    warehouse_id: props.selectedPurchase?.warehouse_id ?? '',
    return_date: new Date().toISOString().split('T')[0],
    remarks: '',
    status: true,
    items: [{ product_id: '', quantity: '', unit_price: 0, reason: '', remarks: '' }],
});

const onPurchaseChange = () => {
    const purchase = props.purchases.find((p) => String(p.id) === String(form.purchase_id));
    if (purchase?.warehouse_id) {
        form.warehouse_id = purchase.warehouse_id;
    }
};

const submit = () => form.post(route('purchase-returns.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('New Purchase Return')" />
        <div class="max-w-8xl mx-auto mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $t('New Purchase Return') }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $t('Return purchased items to supplier with line items in one form.') }}</p>
            </div>
            <Link :href="route('purchase-returns.index')" class="theme-form-back-link text-sm font-bold shrink-0">{{ $t('Back to List') }}</Link>
        </div>

        <form @submit.prevent="submit" class="max-w-8xl mx-auto theme-form-card p-8 md:p-10 space-y-8">
            <div>
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Return Details') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-x-8 gap-y-6">
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Reference No')" class="theme-form-label ml-1" />
                        <TextInput v-model="form.reference_no" class="w-full theme-form-input bg-slate-50 dark:bg-slate-800" readonly />
                        <InputError :message="form.errors.reference_no" class="mt-2 ml-1" />
                    </div>
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Return Date')" class="theme-form-label ml-1" />
                        <TextInput type="date" v-model="form.return_date" class="w-full theme-form-input" required />
                        <InputError :message="form.errors.return_date" class="mt-2 ml-1" />
                    </div>
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Purchase Order')" class="theme-form-label ml-1" />
                        <select v-model="form.purchase_id" class="theme-form-input w-full" required @change="onPurchaseChange">
                            <option value="" disabled>{{ $t('Select Purchase') }}</option>
                            <option v-for="p in purchases" :key="p.id" :value="p.id">{{ p.purchase_no }}</option>
                        </select>
                        <InputError :message="form.errors.purchase_id" class="mt-2 ml-1" />
                    </div>
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Warehouse')" class="theme-form-label ml-1" />
                        <select v-model="form.warehouse_id" class="theme-form-input w-full" required>
                            <option value="" disabled>{{ $t('Select Warehouse') }}</option>
                            <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                        </select>
                        <InputError :message="form.errors.warehouse_id" class="mt-2 ml-1" />
                    </div>
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Status')" class="theme-form-label ml-1" />
                        <div class="mt-2 flex items-center gap-3">
                            <button type="button" @click="form.status = !form.status" class="relative inline-flex h-6 w-11 items-center rounded-full" :class="form.status ? 'bg-indigo-600' : 'bg-slate-300'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition" :class="form.status ? 'translate-x-6' : 'translate-x-1'" />
                            </button>
                            <span class="text-sm font-semibold text-slate-600 dark:text-slate-300">{{ form.status ? $t('Active') : $t('Inactive') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-700 pt-8">
                <PurchaseReturnItemsEditor v-model="form.items" :products="products" :errors="form.errors" />
            </div>

            <div class="flex flex-col">
                <InputLabel :value="$t('Remarks')" class="theme-form-label ml-1" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24 mt-1"></textarea>
            </div>

            <div class="flex justify-center pt-2">
                <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest" :disabled="form.processing">{{ $t('Save Return') }}</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

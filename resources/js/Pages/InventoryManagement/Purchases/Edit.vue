<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PurchaseItemsEditor from '@/Components/Inventory/PurchaseItemsEditor.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ purchase: Object, warehouses: Array, products: Array });

const mapItems = () =>
    props.purchase.details?.length
        ? props.purchase.details.map((i) => ({
            product_id: i.product_id,
            quantity: i.quantity,
            free_quantity: i.free_quantity ?? 0,
            unit_price: i.unit_price,
            discount: i.discount ?? 0,
            tax: i.tax ?? 0,
            remarks: i.remarks ?? '',
        }))
        : [{ product_id: '', quantity: '', free_quantity: 0, unit_price: 0, discount: 0, tax: 0, remarks: '' }];

const form = useForm({
    purchase_no: props.purchase.purchase_no,
    supplier_invoice_no: props.purchase.supplier_invoice_no ?? '',
    supplier_name: props.purchase.supplier_name ?? '',
    purchase_date: props.purchase.purchase_date ? props.purchase.purchase_date.substring(0, 10) : '',
    warehouse_id: props.purchase.warehouse_id,
    discount: props.purchase.discount,
    tax: props.purchase.tax,
    shipping_cost: props.purchase.shipping_cost,
    other_charges: props.purchase.other_charges,
    paid_amount: props.purchase.paid_amount,
    purchase_status: props.purchase.purchase_status,
    remarks: props.purchase.remarks ?? '',
    status: Boolean(props.purchase.status),
    items: mapItems(),
});

const subtotal = computed(() =>
    form.items.reduce((sum, row) => {
        const qty = parseFloat(row.quantity) || 0;
        const price = parseFloat(row.unit_price) || 0;
        const discount = parseFloat(row.discount) || 0;
        const tax = parseFloat(row.tax) || 0;
        return sum + (qty * price - discount + tax);
    }, 0)
);

const grandTotal = computed(() => {
    const g = subtotal.value
        - (parseFloat(form.discount) || 0)
        + (parseFloat(form.tax) || 0)
        + (parseFloat(form.shipping_cost) || 0)
        + (parseFloat(form.other_charges) || 0);
    return g.toFixed(2);
});

const dueAmount = computed(() => Math.max(0, parseFloat(grandTotal.value) - (parseFloat(form.paid_amount) || 0)).toFixed(2));

const submit = () => form.put(route('purchases.update', props.purchase.id));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Edit Purchase')" />
        <div class="max-w-8xl mx-auto mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $t('Edit Purchase') }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ purchase.purchase_no }}</p>
            </div>
            <Link :href="route('purchases.show', purchase.id)" class="theme-form-back-link text-sm font-bold shrink-0">{{ $t('Back to Purchase') }}</Link>
        </div>

        <form @submit.prevent="submit" class="max-w-8xl mx-auto theme-form-card p-8 md:p-10 space-y-8">
            <div>
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Purchase Details') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-x-8 gap-y-6">
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Purchase No (PO #)')" class="theme-form-label ml-1" />
                        <TextInput v-model="form.purchase_no" class="w-full theme-form-input" required />
                        <InputError :message="form.errors.purchase_no" class="mt-2 ml-1" />
                    </div>
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Purchase Date')" class="theme-form-label ml-1" />
                        <TextInput type="date" v-model="form.purchase_date" class="w-full theme-form-input" required />
                    </div>
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Warehouse')" class="theme-form-label ml-1" />
                        <select v-model="form.warehouse_id" class="theme-form-input w-full" required>
                            <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Purchase Status')" class="theme-form-label ml-1" />
                        <select v-model="form.purchase_status" class="theme-form-input w-full">
                            <option value="draft">{{ $t('Draft') }}</option>
                            <option value="received">{{ $t('Received') }}</option>
                            <option value="cancelled">{{ $t('Cancelled') }}</option>
                        </select>
                    </div>
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Supplier Name')" class="theme-form-label ml-1" />
                        <TextInput v-model="form.supplier_name" class="w-full theme-form-input" />
                    </div>
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Supplier Invoice / Reference')" class="theme-form-label ml-1" />
                        <TextInput v-model="form.supplier_invoice_no" class="w-full theme-form-input" />
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
                <PurchaseItemsEditor v-model="form.items" :products="products" :errors="form.errors" />
            </div>

            <div>
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Payment & Charges') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-x-8 gap-y-6">
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Subtotal')" class="theme-form-label ml-1" />
                        <div class="theme-form-input bg-slate-50 dark:bg-slate-800 font-bold text-indigo-600">{{ subtotal.toFixed(2) }}</div>
                    </div>
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Discount')" class="theme-form-label ml-1" />
                        <TextInput type="number" step="0.01" v-model="form.discount" class="w-full theme-form-input" />
                    </div>
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Tax')" class="theme-form-label ml-1" />
                        <TextInput type="number" step="0.01" v-model="form.tax" class="w-full theme-form-input" />
                    </div>
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Shipping Cost')" class="theme-form-label ml-1" />
                        <TextInput type="number" step="0.01" v-model="form.shipping_cost" class="w-full theme-form-input" />
                    </div>
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Other Charges')" class="theme-form-label ml-1" />
                        <TextInput type="number" step="0.01" v-model="form.other_charges" class="w-full theme-form-input" />
                    </div>
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Paid Amount')" class="theme-form-label ml-1" />
                        <TextInput type="number" step="0.01" v-model="form.paid_amount" class="w-full theme-form-input" />
                    </div>
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Grand Total')" class="theme-form-label ml-1" />
                        <div class="theme-form-input bg-slate-50 dark:bg-slate-800 font-bold text-indigo-600">{{ grandTotal }}</div>
                    </div>
                    <div class="flex flex-col">
                        <InputLabel :value="$t('Due Amount')" class="theme-form-label ml-1" />
                        <div class="theme-form-input bg-slate-50 dark:bg-slate-800 font-bold text-rose-600">{{ dueAmount }}</div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col">
                <InputLabel :value="$t('Remarks')" class="theme-form-label ml-1" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24 mt-1"></textarea>
            </div>

            <div class="flex justify-center pt-2">
                <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest" :disabled="form.processing">{{ $t('Update Purchase') }}</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

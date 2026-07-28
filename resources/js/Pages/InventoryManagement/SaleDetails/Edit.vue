<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    detail: Object,
    sales: Array,
    products: Array,
    sellingUnits: Array,
    warehouseStocks: Array,
});

const page = usePage();

const form = useForm({
    sale_id: props.detail.sale_id,
    product_id: props.detail.product_id,
    selling_unit: props.detail.selling_unit,
    quantity: props.detail.quantity,
    unit_price: props.detail.unit_price,
    discount: props.detail.discount,
    tax: props.detail.tax,
    remarks: props.detail.remarks ?? '',
    status: Boolean(props.detail.status),
});

const selectedSale = computed(() => props.sales.find((s) => s.id == form.sale_id));

const availableStock = computed(() => {
    if (!selectedSale.value || !form.product_id) return null;
    const row = props.warehouseStocks.find(
        (s) => s.warehouse_id == selectedSale.value.warehouse_id && s.product_id == form.product_id
    );
    return row ? Number(row.available_quantity) : 0;
});

const lineTotal = computed(() => {
    const sub = parseFloat(form.quantity || 0) * parseFloat(form.unit_price || 0);
    return (sub - parseFloat(form.discount || 0) + parseFloat(form.tax || 0)).toFixed(2);
});

const submit = () => form.put(route('sale-details.update', props.detail.id));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Edit Sale Item')" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-black text-slate-900">{{ $t('Edit Sale Item') }}</h2>
                <p class="text-sm text-slate-500 font-medium">Record ID: #{{ detail.id }}</p>
            </div>
            <Link :href="route('sale-details.index')" class="theme-form-back-link">{{ $t('Back to List') }}</Link>
        </div>

        <div
            v-if="page.props.flash?.error"
            class="mb-6 flex items-center p-4 border-l-4 border-red-500 bg-red-50 text-red-800 rounded-r-xl shadow-sm dark:bg-red-500/10 dark:text-red-200"
        >
            <p class="text-sm font-bold">{{ page.props.flash.error }}</p>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div
                v-if="selectedSale?.sale_status === 'completed'"
                class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200"
            >
                {{ $t('This invoice is') }} <strong>{{ $t('completed') }}</strong>. Saving will re-sync stock for this line. Available: {{ availableStock ?? 0 }}.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel :value="$t('Sale Invoice')" />
                    <select v-model="form.sale_id" class="theme-form-input w-full" required>
                        <option v-for="s in sales" :key="s.id" :value="s.id">
                            {{ s.invoice_no }} ({{ s.sale_status }})
                        </option>
                    </select>
                    <InputError :message="form.errors.sale_id" />
                </div>
                <div>
                    <InputLabel :value="$t('Product')" />
                    <select v-model="form.product_id" class="theme-form-input w-full" required>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <InputError :message="form.errors.product_id" />
                </div>
                <div>
                    <InputLabel value="Selling Unit" />
                    <select v-model="form.selling_unit" class="theme-form-input w-full">
                        <option v-for="u in sellingUnits" :key="u" :value="u">{{ u }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel :value="$t('Quantity')" />
                    <TextInput type="number" step="0.01" v-model="form.quantity" class="w-full" required />
                    <InputError :message="form.errors.quantity" />
                </div>
                <div>
                    <InputLabel :value="$t('Unit Price')" />
                    <TextInput type="number" step="0.01" v-model="form.unit_price" class="w-full" required />
                    <InputError :message="form.errors.unit_price" />
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
                    <InputLabel value="Line Total (Auto)" />
                    <div class="theme-form-input bg-slate-50 font-bold text-indigo-600">${{ lineTotal }}</div>
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
            <div class="flex justify-center pt-4">
                <PrimaryButton type="submit" :disabled="form.processing">{{ $t('Update Item') }}</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

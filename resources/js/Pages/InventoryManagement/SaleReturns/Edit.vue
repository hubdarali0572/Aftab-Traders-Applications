<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SaleReturnItemsEditor from '@/Components/Inventory/SaleReturnItemsEditor.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    saleReturn: Object,
    sales: Array,
});

const page = usePage();

const mapItems = () => (props.saleReturn.details?.length
    ? props.saleReturn.details.map((d) => ({
        product_id: d.product_id,
        quantity: d.quantity,
        reason: d.reason ?? '',
        remarks: d.remarks ?? '',
    }))
    : [{ product_id: '', quantity: '', reason: '', remarks: '' }]);

const form = useForm({
    reference_no: props.saleReturn.reference_no,
    sale_id: props.saleReturn.sale_id,
    return_date: props.saleReturn.return_date ? props.saleReturn.return_date.substring(0, 10) : '',
    remarks: props.saleReturn.remarks || '',
    status: Boolean(props.saleReturn.status),
    items: mapItems(),
});

const selectedSale = computed(() => props.sales.find((s) => String(s.id) === String(form.sale_id)));

const saleDetails = computed(() => selectedSale.value?.details ?? []);

watch(() => form.sale_id, () => {
    if (String(form.sale_id) !== String(props.saleReturn.sale_id)) {
        form.items = [{ product_id: '', quantity: '', reason: '', remarks: '' }];
    }
});

const submit = () => form.put(route('sale-returns.update', props.saleReturn.id));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Edit Sales Return')" />
        <div class="max-w-8xl mx-auto mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $t('Edit Sales Return') }} — {{ saleReturn.reference_no }}</h2>
            </div>
            <Link :href="route('sale-returns.index')" class="theme-form-back-link text-sm font-bold shrink-0">{{ $t('Back to List') }}</Link>
        </div>

        <div v-if="page.props.flash?.error" class="mb-6 p-4 border-l-4 border-rose-500 bg-rose-50 text-rose-800 rounded-r-xl text-sm font-bold">{{ page.props.flash.error }}</div>

        <form @submit.prevent="submit" class="max-w-8xl mx-auto theme-form-card p-8 md:p-10 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-x-8 gap-y-6">
                <div>
                    <InputLabel :value="$t('Return Reference No.')" class="theme-form-label ml-1" />
                    <TextInput v-model="form.reference_no" class="w-full theme-form-input" required />
                    <InputError :message="form.errors.reference_no" class="mt-2 ml-1" />
                </div>
                <div>
                    <InputLabel :value="$t('Return Date')" class="theme-form-label ml-1" />
                    <TextInput type="date" v-model="form.return_date" class="w-full theme-form-input" required />
                </div>
                <div>
                    <InputLabel :value="$t('Original Invoice')" class="theme-form-label ml-1" />
                    <select v-model="form.sale_id" class="theme-form-input w-full" required>
                        <option v-for="sale in sales" :key="sale.id" :value="sale.id">
                            {{ sale.invoice_no }} — {{ sale.customer?.customer_name || $t('Walk-in') }}
                        </option>
                    </select>
                </div>
                <div>
                    <InputLabel :value="$t('Warehouse')" class="theme-form-label ml-1" />
                    <div class="theme-form-input bg-slate-50 dark:bg-slate-800">{{ selectedSale?.warehouse?.name || '—' }}</div>
                </div>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-700 pt-8">
                <SaleReturnItemsEditor v-model="form.items" :sale-details="saleDetails" :errors="form.errors" />
            </div>

            <div>
                <InputLabel :value="$t('Remarks')" class="theme-form-label ml-1" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24 mt-1"></textarea>
            </div>

            <div class="flex justify-center pt-2">
                <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest" :disabled="form.processing">{{ $t('Update Return') }}</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

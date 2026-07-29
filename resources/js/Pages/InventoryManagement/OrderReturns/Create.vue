<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import OrderReturnItemsEditor from '@/Components/Inventory/OrderReturnItemsEditor.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    orders: Array,
    selectedOrder: Object,
    generatedReferenceNo: String,
    returnStatuses: Array,
});

const page = usePage();

const form = useForm({
    reference_no: props.generatedReferenceNo || ('OR-' + Date.now()),
    order_id: props.selectedOrder?.id ?? '',
    return_date: new Date().toISOString().split('T')[0],
    return_reason: '',
    return_status: 'pending',
    remarks: '',
    status: true,
    items: [{ product_id: '', quantity: '', reason: '', remarks: '' }],
});

const selectedOrder = computed(() => props.orders.find((o) => String(o.id) === String(form.order_id)) || props.selectedOrder);
const orderDetails = computed(() => selectedOrder.value?.details ?? []);

watch(() => form.order_id, () => {
    form.items = [{ product_id: '', quantity: '', reason: '', remarks: '' }];
});

const submit = () => form.post(route('order-returns.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('New Order Return')" />
        <div class="max-w-8xl mx-auto mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $t('New Order Return') }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $t('Process a return with line items, stock restoration, and customer ledger credit in one form.') }}</p>
            </div>
            <Link :href="route('order-returns.index')" class="theme-form-back-link text-sm font-bold shrink-0">{{ $t('Back to List') }}</Link>
        </div>

        <div v-if="page.props.flash?.error" class="mb-6 p-4 border-l-4 border-rose-500 bg-rose-50 text-rose-800 rounded-r-xl text-sm font-bold">{{ page.props.flash.error }}</div>

        <form @submit.prevent="submit" class="max-w-8xl mx-auto theme-form-card p-8 md:p-10 space-y-8">
            <div>
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Order Return Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-x-8 gap-y-6">
                    <div>
                        <InputLabel :value="$t('Return Reference No.')" class="theme-form-label ml-1" />
                        <TextInput v-model="form.reference_no" class="w-full theme-form-input bg-slate-50 dark:bg-slate-800" readonly />
                        <InputError :message="form.errors.reference_no" class="mt-2 ml-1" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Return Date')" class="theme-form-label ml-1" />
                        <TextInput type="date" v-model="form.return_date" class="w-full theme-form-input" required />
                    </div>
                    <div>
                        <InputLabel :value="$t('Original Order')" class="theme-form-label ml-1" />
                        <select v-model="form.order_id" class="theme-form-input w-full" required>
                            <option value="" disabled>{{ $t('Select Order') }}</option>
                            <option v-for="order in orders" :key="order.id" :value="order.id">
                                {{ order.order_no }} — {{ order.customer?.customer_name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.order_id" class="mt-2 ml-1" />
                    </div>
                    <div>
                        <InputLabel :value="$t('Customer')" class="theme-form-label ml-1" />
                        <div class="theme-form-input bg-slate-50 dark:bg-slate-800">{{ selectedOrder?.customer?.customer_name || '—' }}</div>
                    </div>
                    <div>
                        <InputLabel :value="$t('Warehouse')" class="theme-form-label ml-1" />
                        <div class="theme-form-input bg-slate-50 dark:bg-slate-800">{{ selectedOrder?.warehouse?.name || '—' }}</div>
                    </div>
                    <div>
                        <InputLabel :value="$t('Return Status')" class="theme-form-label ml-1" />
                        <select v-model="form.return_status" class="theme-form-input w-full">
                            <option v-for="s in returnStatuses" :key="s" :value="s">{{ s.charAt(0).toUpperCase() + s.slice(1) }}</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <InputLabel :value="$t('Return Reason')" class="theme-form-label ml-1" />
                        <TextInput v-model="form.return_reason" class="w-full theme-form-input" />
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 dark:border-slate-700 pt-8">
                <OrderReturnItemsEditor v-model="form.items" :order-details="orderDetails" :errors="form.errors" />
            </div>

            <div>
                <InputLabel :value="$t('Remarks')" class="theme-form-label ml-1" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24 mt-1"></textarea>
            </div>

            <div class="flex justify-center pt-2">
                <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest" :disabled="form.processing">{{ $t('Save Return') }}</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

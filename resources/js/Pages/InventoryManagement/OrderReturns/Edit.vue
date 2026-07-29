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
    orderReturn: Object,
    orders: Array,
    returnStatuses: Array,
});

const page = usePage();

const mapItems = () => (props.orderReturn.details?.length
    ? props.orderReturn.details.map((d) => ({
        product_id: d.product_id,
        quantity: d.quantity,
        reason: d.reason ?? '',
        remarks: d.remarks ?? '',
    }))
    : [{ product_id: '', quantity: '', reason: '', remarks: '' }]);

const form = useForm({
    reference_no: props.orderReturn.reference_no,
    order_id: props.orderReturn.order_id,
    return_date: props.orderReturn.return_date ? props.orderReturn.return_date.substring(0, 10) : '',
    return_reason: props.orderReturn.return_reason ?? '',
    return_status: props.orderReturn.return_status,
    remarks: props.orderReturn.remarks ?? '',
    status: Boolean(props.orderReturn.status),
    items: mapItems(),
});

const selectedOrder = computed(() => props.orders.find((o) => String(o.id) === String(form.order_id)));
const orderDetails = computed(() => selectedOrder.value?.details ?? []);

watch(() => form.order_id, () => {
    if (!props.orderReturn.details?.length) {
        form.items = [{ product_id: '', quantity: '', reason: '', remarks: '' }];
    }
});

const submit = () => form.put(route('order-returns.update', props.orderReturn.id));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="`${orderReturn.reference_no} — ${$t('Edit Order Return')}`" />
        <div class="max-w-8xl mx-auto mb-6 flex justify-between items-center">
            <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $t('Edit Order Return') }}</h2>
            <Link :href="route('order-returns.index')" class="theme-form-back-link text-sm font-bold">{{ $t('Back') }}</Link>
        </div>

        <div v-if="page.props.flash?.error" class="mb-6 p-4 border-l-4 border-rose-500 bg-rose-50 text-rose-800 rounded-r-xl text-sm font-bold">{{ page.props.flash.error }}</div>

        <form @submit.prevent="submit" class="max-w-8xl mx-auto theme-form-card p-8 md:p-10 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-x-8 gap-y-6">
                <div>
                    <InputLabel :value="$t('Return Reference No.')" class="theme-form-label ml-1" />
                    <TextInput v-model="form.reference_no" class="w-full theme-form-input bg-slate-50" readonly />
                </div>
                <div>
                    <InputLabel :value="$t('Return Date')" class="theme-form-label ml-1" />
                    <TextInput type="date" v-model="form.return_date" class="w-full theme-form-input" required />
                </div>
                <div>
                    <InputLabel :value="$t('Original Order')" class="theme-form-label ml-1" />
                    <select v-model="form.order_id" class="theme-form-input w-full" required>
                        <option v-for="order in orders" :key="order.id" :value="order.id">{{ order.order_no }}</option>
                    </select>
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

            <div class="border-t border-slate-100 dark:border-slate-700 pt-8">
                <OrderReturnItemsEditor v-model="form.items" :order-details="orderDetails" :errors="form.errors" />
            </div>

            <div>
                <InputLabel :value="$t('Remarks')" class="theme-form-label ml-1" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24 mt-1"></textarea>
            </div>

            <div class="flex justify-center pt-2">
                <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full" :disabled="form.processing">{{ $t('Update Return') }}</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

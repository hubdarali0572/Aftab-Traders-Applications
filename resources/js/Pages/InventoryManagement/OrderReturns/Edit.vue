<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    orderReturn: Object,
    orders: Array,
    returnStatuses: Array,
});

const form = useForm({
    reference_no: props.orderReturn.reference_no,
    order_id: props.orderReturn.order_id,
    return_date: props.orderReturn.return_date ? props.orderReturn.return_date.substring(0, 10) : '',
    return_status: props.orderReturn.return_status,
    remarks: props.orderReturn.remarks || '',
    status: Boolean(props.orderReturn.status),
});

const selectedOrder = computed(() => props.orders.find((order) => order.id == form.order_id));

const submit = () => form.put(route('order-returns.update', props.orderReturn.id));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Edit Order Return')" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-black text-slate-800">{{ $t('Edit Order Return') }}</h2>
            <Link :href="route('order-returns.index')" class="theme-form-back-link">{{ $t('Back to List') }}</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel :value="$t('Reference No')" />
                    <TextInput v-model="form.reference_no" class="w-full" required />
                    <InputError :message="form.errors.reference_no" />
                </div>
                <div>
                    <InputLabel :value="$t('Return Date')" />
                    <TextInput type="date" v-model="form.return_date" class="w-full" required />
                    <InputError :message="form.errors.return_date" />
                </div>
                <div>
                    <InputLabel value="Order" />
                    <select v-model="form.order_id" class="theme-form-input w-full" required>
                        <option value="" disabled>{{ $t('Select Order') }}</option>
                        <option v-for="order in orders" :key="order.id" :value="order.id">
                            {{ order.order_no }} - {{ order.customer?.customer_name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.order_id" />
                </div>

                <div>
                    <InputLabel :value="$t('Customer')" />
                    <div class="theme-form-input bg-slate-50 dark:bg-slate-700/50">{{ selectedOrder?.customer?.customer_name || '—' }}</div>
                </div>
                <div>
                    <InputLabel :value="$t('Warehouse')" />
                    <div class="theme-form-input bg-slate-50 dark:bg-slate-700/50">{{ selectedOrder?.warehouse?.name || '—' }}</div>
                </div>
                <div>
                    <InputLabel value="Order Reference" />
                    <div class="theme-form-input bg-slate-50 dark:bg-slate-700/50">{{ selectedOrder?.order_no || '—' }}</div>
                </div>
                <div>
                    <InputLabel :value="$t('Return Status')" />
                    <select v-model="form.return_status" class="theme-form-input w-full">
                        <option v-for="s in returnStatuses" :key="s" :value="s">{{ s.charAt(0).toUpperCase() + s.slice(1) }}</option>
                    </select>
                    <InputError :message="form.errors.return_status" />
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
                <InputError :message="form.errors.remarks" />
            </div>

            <div class="flex justify-center">
                <PrimaryButton :disabled="form.processing">{{ $t('Update Return') }}</PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

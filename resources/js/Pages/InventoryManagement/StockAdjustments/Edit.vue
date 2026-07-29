<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InventoryItemsEditor from '@/Components/Inventory/InventoryItemsEditor.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ adjustment: Object, warehouses: Array, products: Array });

const mapItems = () =>
    props.adjustment.items?.length
        ? props.adjustment.items.map((i) => ({
            product_id: i.product_id,
            adjustment_quantity: i.adjustment_quantity,
            unit_cost: i.unit_cost,
            reason: i.reason ?? '',
        }))
        : [{ product_id: '', adjustment_quantity: '', unit_cost: 0, reason: '' }];

const form = useForm({
    reference_no: props.adjustment.reference_no,
    adjustment_date: props.adjustment.adjustment_date ? props.adjustment.adjustment_date.substring(0, 10) : '',
    warehouse_id: props.adjustment.warehouse_id,
    remarks: props.adjustment.remarks ?? '',
    status: Boolean(props.adjustment.status),
    items: mapItems(),
});

const submit = () => form.put(route('stock-adjustments.update', props.adjustment.id));
</script>

<template>
    <AuthenticatedLayout>
        <Head :title="$t('Edit Inventory Correction')" />
        <div class="max-w-8xl mx-auto mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $t('Edit Inventory Correction') }}</h2>
                <p class="text-sm text-slate-500 mt-1 dark:text-slate-400">{{ adjustment.reference_no }} · {{ $t('Not for customer sales') }}</p>
            </div>
            <Link :href="route('stock-adjustments.index')" class="theme-form-back-link text-sm font-bold shrink-0">{{ $t('Back to List') }}</Link>
        </div>

        <form @submit.prevent="submit" class="max-w-8xl mx-auto space-y-6">
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">
                <strong>{{ $t('Not a sale:') }}</strong>
                {{ $t('Use positive quantity to add stock and negative quantity to remove stock from the warehouse. Customer sales must be recorded in the Sales module.') }}
            </div>

            <div class="theme-form-card p-8 md:p-10 space-y-8">
                <div>
                    <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Correction Details') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-x-8 gap-y-6">
                        <div class="flex flex-col">
                            <InputLabel :value="$t('Reference Number')" class="theme-form-label ml-1" />
                            <TextInput v-model="form.reference_no" class="w-full theme-form-input" required />
                            <InputError :message="form.errors.reference_no" class="mt-2 ml-1" />
                        </div>
                        <div class="flex flex-col">
                            <InputLabel :value="$t('Correction Date')" class="theme-form-label ml-1" />
                            <TextInput type="date" v-model="form.adjustment_date" class="w-full theme-form-input" required />
                            <InputError :message="form.errors.adjustment_date" class="mt-2 ml-1" />
                        </div>
                        <div class="flex flex-col">
                            <InputLabel :value="$t('Warehouse')" class="theme-form-label ml-1" />
                            <select v-model="form.warehouse_id" class="theme-form-input w-full" required>
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
                            <InputError :message="form.errors.status" class="mt-2 ml-1" />
                        </div>
                    </div>
                </div>

                <div class="flex flex-col">
                    <InputLabel :value="$t('Remarks')" class="theme-form-label ml-1" />
                    <textarea id="remarks" v-model="form.remarks" class="theme-form-input w-full h-24 mt-1" :placeholder="$t('Optional notes about this correction...')"></textarea>
                    <InputError :message="form.errors.remarks" class="mt-2 ml-1" />
                </div>

                <div class="border-t border-slate-100 dark:border-slate-700 pt-8">
                    <InventoryItemsEditor
                        v-model="form.items"
                        :products="products"
                        quantity-field="adjustment_quantity"
                        :quantity-label="$t('Qty (+ add / − remove)')"
                        :allow-negative="true"
                        extra-field="reason"
                        :extra-label="$t('Reason')"
                        :errors="form.errors"
                        :hint="$t('Positive = add stock. Negative = remove stock. This is not a customer sale.')"
                    />
                </div>

                <div class="flex justify-center pt-2">
                    <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest" :disabled="form.processing">
                        {{ $t('Update Correction') }}
                    </PrimaryButton>
                </div>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

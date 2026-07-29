<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InventoryItemsEditor from '@/Components/Inventory/InventoryItemsEditor.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({ warehouses: Array, products: Array });

const form = useForm({
    reference_no: 'DMG-' + Date.now(),
    damage_date: new Date().toISOString().split('T')[0],
    warehouse_id: '',
    remarks: '',
    status: true,
    items: [{ product_id: '', quantity: '', unit_cost: 0, damage_reason: '' }],
});

const submit = () => form.post(route('damaged-stocks.store'));
</script>

<template>
    <AuthenticatedLayout>
        <Head title="Create Damaged Stock" />
        <div class="max-w-8xl mx-auto mb-5 flex justify-between items-center">
            <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white">{{ $t('Create Damaged Stock Record') }}</h2>
            <Link :href="route('damaged-stocks.index')" class="theme-form-back-link text-sm font-bold">{{ $t('Back to List') }}</Link>
        </div>

        <form @submit.prevent="submit" class="theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <InputLabel value="Reference #" />
                    <TextInput v-model="form.reference_no" class="w-full theme-form-input" required />
                    <InputError :message="form.errors.reference_no" />
                </div>
                <div>
                    <InputLabel :value="$t('Damage Date')" />
                    <TextInput type="date" v-model="form.damage_date" class="w-full theme-form-input" required />
                </div>
                <div>
                    <InputLabel :value="$t('Warehouse')" />
                    <select v-model="form.warehouse_id" class="theme-form-input w-full" required>
                        <option value="" disabled>{{ $t('Select Warehouse') }}</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                </div>
            </div>
            <div>
                <InputLabel :value="$t('Remarks')" />
                <textarea v-model="form.remarks" class="theme-form-input w-full h-24"></textarea>
            </div>

            <InventoryItemsEditor
                v-model="form.items"
                :products="products"
                extra-field="damage_reason"
                extra-label="Damage Reason"
                :errors="form.errors"
            />

            <div class="flex justify-center"><PrimaryButton :disabled="form.processing">{{ $t('Save Damage Record') }}</PrimaryButton></div>
        </form>
    </AuthenticatedLayout>
</template>

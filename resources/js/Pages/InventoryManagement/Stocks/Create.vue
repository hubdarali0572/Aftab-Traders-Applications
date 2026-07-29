<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    warehouses: Array,
    products: Array,
});

const form = useForm({
    warehouse_id: '',
    product_id: '',
    minimum_stock: 0,
    reorder_level: 0,
});

const submit = () => form.post(route('stocks.store'));
</script>

<template>
    <Head :title="$t('Add Stock')" />
    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">{{ $t('Add Stock') }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $t('Register a product in a warehouse. Quantities update automatically from Opening Stock, Purchases, Sales, and other transactions.') }}</p>
            </div>
            <Link :href="route('stocks.index')" class="theme-form-back-link">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="text-slate-900 dark:text-slate-100">{{ $t('Back to Total Stock') }}</span>
            </Link>
        </div>

        <form @submit.prevent="submit" class="max-w-8xl mx-auto theme-form-card p-8 md:p-10 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-6">
                <div class="flex flex-col">
                    <InputLabel for="warehouse_id" :value="$t('Warehouse')" class="theme-form-label ml-1" />
                    <select id="warehouse_id" v-model="form.warehouse_id" class="theme-form-input w-full" required>
                        <option value="" disabled>{{ $t('Select Warehouse') }}</option>
                        <option v-for="w in warehouses" :key="w.id" :value="w.id">{{ w.name }}</option>
                    </select>
                    <InputError :message="form.errors.warehouse_id" class="mt-2 ml-1" />
                </div>
                <div class="flex flex-col">
                    <InputLabel for="product_id" :value="$t('Product')" class="theme-form-label ml-1" />
                    <select id="product_id" v-model="form.product_id" class="theme-form-input w-full" required>
                        <option value="" disabled>{{ $t('Select Product') }}</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                    <InputError :message="form.errors.product_id" class="mt-2 ml-1" />
                </div>
                <div class="flex flex-col">
                    <InputLabel for="minimum_stock" :value="$t('Minimum Stock')" class="theme-form-label ml-1" />
                    <TextInput id="minimum_stock" type="number" step="0.01" class="theme-form-input" v-model="form.minimum_stock" placeholder="0.00" />
                    <InputError :message="form.errors.minimum_stock" class="mt-2 ml-1" />
                </div>
                <div class="flex flex-col">
                    <InputLabel for="reorder_level" :value="$t('Reorder Level')" class="theme-form-label ml-1" />
                    <TextInput id="reorder_level" type="number" step="0.01" class="theme-form-input" v-model="form.reorder_level" placeholder="0.00" />
                    <InputError :message="form.errors.reorder_level" class="mt-2 ml-1" />
                </div>
            </div>

            <div class="flex items-center justify-center pt-4">
                <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95" :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing">
                    {{ $t('Add Stock') }}
                </PrimaryButton>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

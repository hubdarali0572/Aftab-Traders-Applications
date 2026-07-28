<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    products: { type: Array, default: () => [] },
});

const form = useForm({
    product_id: '',

    purchase_price: 0,
    landing_cost: 0,
    cost_price: 0,

    retail_price: '',
    wholesale_price: '',
    dealer_price: '',
    distributor_price: '',
    online_price: '',

    minimum_selling_price: '',
    maximum_discount: '',
    profit_margin: '',

    effective_from: '',
    effective_to: '',

    is_default: true,
    status: true,
});

const submit = () => {
    form.post(route('product-selling-prices.store'));
};
</script>

<template>
    <Head :title="$t('Create Selling Price')" />
    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">{{ $t('Create Selling Price') }}</h2>
            </div>
            <Link :href="route('product-selling-prices.index')" class="theme-form-back-link">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="text-slate-900">{{ $t('Back to Price List') }}</span>
            </Link>
        </div>

        <div class="max-w-8xl mx-auto pb-5">
            <form @submit.prevent="submit" class="space-y-4">
                <div class="theme-form-card">
                    <div class="p-8 md:p-10">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Basic Information') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                            <div class="flex flex-col md:col-span-2">
                                <InputLabel for="product_id" :value="$t('Product')" class="theme-form-label ml-1" />
                                <select id="product_id" class="theme-form-input" v-model="form.product_id" required>
                                    <option value="" disabled>{{ $t('Select product') }}</option>
                                    <option v-for="product in props.products" :key="product.id" :value="product.id">{{ product.name }}<template v-if="product.sku"> ({{ product.sku }})</template></option>
                                </select>
                                <InputError :message="form.errors.product_id" class="mt-2 ml-1" />
                            </div>
                        </div>

                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-10 mb-6">Costing</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-10 gap-y-8">
                            <div class="flex flex-col">
                                <InputLabel for="purchase_price" value="Purchase Price" class="theme-form-label ml-1" />
                                <TextInput id="purchase_price" type="number" step="0.01" min="0" class="theme-form-input" v-model="form.purchase_price" placeholder="e.g. 100.00" />
                                <InputError :message="form.errors.purchase_price" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="landing_cost" value="Landing Cost" class="theme-form-label ml-1" />
                                <TextInput id="landing_cost" type="number" step="0.01" min="0" class="theme-form-input" v-model="form.landing_cost" placeholder="e.g. 105.00" />
                                <InputError :message="form.errors.landing_cost" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="cost_price" value="Cost Price" class="theme-form-label ml-1" />
                                <TextInput id="cost_price" type="number" step="0.01" min="0" class="theme-form-input" v-model="form.cost_price" placeholder="e.g. 110.00" />
                                <InputError :message="form.errors.cost_price" class="mt-2 ml-1" />
                            </div>
                        </div>

                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-10 mb-6">Selling Prices</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-10 gap-y-8">
                            <div class="flex flex-col">
                                <InputLabel for="retail_price" value="Retail Price" class="theme-form-label ml-1" />
                                <TextInput id="retail_price" type="number" step="0.01" min="0" class="theme-form-input" v-model="form.retail_price" required placeholder="e.g. 149.99" />
                                <InputError :message="form.errors.retail_price" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="wholesale_price" value="Wholesale Price" class="theme-form-label ml-1" />
                                <TextInput id="wholesale_price" type="number" step="0.01" min="0" class="theme-form-input" v-model="form.wholesale_price" placeholder="Optional" />
                                <InputError :message="form.errors.wholesale_price" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="dealer_price" value="Dealer Price" class="theme-form-label ml-1" />
                                <TextInput id="dealer_price" type="number" step="0.01" min="0" class="theme-form-input" v-model="form.dealer_price" placeholder="Optional" />
                                <InputError :message="form.errors.dealer_price" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="distributor_price" value="Distributor Price" class="theme-form-label ml-1" />
                                <TextInput id="distributor_price" type="number" step="0.01" min="0" class="theme-form-input" v-model="form.distributor_price" placeholder="Optional" />
                                <InputError :message="form.errors.distributor_price" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="online_price" value="Online Price" class="theme-form-label ml-1" />
                                <TextInput id="online_price" type="number" step="0.01" min="0" class="theme-form-input" v-model="form.online_price" placeholder="Optional" />
                                <InputError :message="form.errors.online_price" class="mt-2 ml-1" />
                            </div>
                        </div>

                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-10 mb-6">Discount / Margin</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-x-10 gap-y-8">
                            <div class="flex flex-col">
                                <InputLabel for="minimum_selling_price" value="Minimum Selling Price" class="theme-form-label ml-1" />
                                <TextInput id="minimum_selling_price" type="number" step="0.01" min="0" class="theme-form-input" v-model="form.minimum_selling_price" placeholder="Optional" />
                                <InputError :message="form.errors.minimum_selling_price" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="maximum_discount" value="Maximum Discount (%)" class="theme-form-label ml-1" />
                                <TextInput id="maximum_discount" type="number" step="0.01" min="0" max="100" class="theme-form-input" v-model="form.maximum_discount" placeholder="e.g. 10.00" />
                                <InputError :message="form.errors.maximum_discount" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="profit_margin" value="Profit Margin (%)" class="theme-form-label ml-1" />
                                <TextInput id="profit_margin" type="number" step="0.01" min="0" max="100" class="theme-form-input" v-model="form.profit_margin" placeholder="e.g. 25.00" />
                                <InputError :message="form.errors.profit_margin" class="mt-2 ml-1" />
                            </div>
                        </div>

                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-10 mb-6">Validity & Status</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                            <div class="flex flex-col">
                                <InputLabel for="effective_from" value="Effective From" class="theme-form-label ml-1" />
                                <TextInput id="effective_from" type="date" class="theme-form-input" v-model="form.effective_from" required />
                                <InputError :message="form.errors.effective_from" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="effective_to" value="Effective To" class="theme-form-label ml-1" />
                                <TextInput id="effective_to" type="date" class="theme-form-input" v-model="form.effective_to" />
                                <InputError :message="form.errors.effective_to" class="mt-2 ml-1" />
                            </div>

                            <div class="flex flex-col">
                                <InputLabel value="Default Price" class="theme-form-label ml-1" />
                                <label class="inline-flex items-center gap-3 mt-2 cursor-pointer select-none">
                                    <button type="button" role="switch" :aria-checked="form.is_default" @click="form.is_default = !form.is_default" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none" :class="form.is_default ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-600'">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200" :class="form.is_default ? 'translate-x-6' : 'translate-x-1'" />
                                    </button>
                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ form.is_default ? 'Yes' : 'No' }}</span>
                                </label>
                                <InputError :message="form.errors.is_default" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel value="Status" class="theme-form-label ml-1" />
                                <label class="inline-flex items-center gap-3 mt-2 cursor-pointer select-none">
                                    <button type="button" role="switch" :aria-checked="form.status" @click="form.status = !form.status" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none" :class="form.status ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-600'">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200" :class="form.status ? 'translate-x-6' : 'translate-x-1'" />
                                    </button>
                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ form.status ? 'Active' : 'Inactive' }}</span>
                                </label>
                                <InputError :message="form.errors.status" class="mt-2 ml-1" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center justify-center pt-4">
                    <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95" :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing">Create Selling Price</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { ref, watch, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { usePricePerPieceCalculation } from '@/composables/usePricePerPieceCalculation';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    categories: { type: Array, default: () => [] },
    brands: { type: Array, default: () => [] },
    units: { type: Array, default: () => [] },
});

const slugTouched = ref(false);

const form = useForm({
    product_category_id: '',
    brand_id: '',
    unit_id: '',
    name: '',
    slug: '',
    sku: '',
    purchase_price: '',
    selling_price: '',
    carton_qty: '',
    price_per_carton: '',
    pieces_per_carton: '',
    price_per_piece: 0,
    weight: '',
    color: '',
    origin_country: '',
    description: '',
    minimum_stock: 0,
    status: true,
});

const { formattedPricePerPiece, prepareProductPricing } = usePricePerPieceCalculation(form);

const slugify = (value) =>
    value.toString().trim().toLowerCase().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');

watch(() => form.name, (newName) => {
    if (!slugTouched.value) form.slug = slugify(newName);
});

const onSlugInput = () => { slugTouched.value = true; };

onMounted(() => {
    setDefaultUnit();
});

watch(() => props.units, () => {
    setDefaultUnit();
}, { immediate: true });

const setDefaultUnit = () => {
    if (!form.unit_id && props.units.length > 0) {
        form.unit_id = props.units[0].id;
    }
};

const submit = () => {
    prepareProductPricing();
    form.post(route('products.store'));
};
</script>

<template>
    <Head :title="$t('Create Product')" />
    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">{{ $t('Create New Product') }}</h2>
            </div>
            <Link :href="route('products.index')" class="theme-form-back-link">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="text-slate-900">{{ $t('Back to Product List') }}</span>
            </Link>
        </div>

        <div class="max-w-8xl mx-auto pb-4">
            <form @submit.prevent="submit" class="space-y-3">
                <div class="theme-form-card">
                    <div class="p-8 md:p-10">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Basic Information') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-3">
                            <div class="flex flex-col">
                                <InputLabel for="name" :value="$t('Name')" class="theme-form-label ml-1" />
                                <TextInput id="name" type="text" class="theme-form-input" v-model="form.name" required placeholder="e.g. Wireless Mouse" />
                                <InputError :message="form.errors.name" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="slug" :value="$t('Slug')" class="theme-form-label ml-1" />
                                <TextInput id="slug" type="text" class="theme-form-input" v-model="form.slug" @input="onSlugInput" required placeholder="e.g. wireless-mouse" />
                                <InputError :message="form.errors.slug" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="sku" :value="$t('SKU')" class="theme-form-label ml-1" />
                                <TextInput id="sku" type="text" class="theme-form-input" v-model="form.sku" required placeholder="e.g. WM-1001" />
                                <InputError :message="form.errors.sku" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="purchase_price" :value="$t('Purchase Price')" class="theme-form-label ml-1" />
                                <TextInput id="purchase_price" type="number" min="0" step="0.01" class="theme-form-input" v-model="form.purchase_price" :placeholder="$t('Purchase Price')" />
                                <InputError :message="form.errors.purchase_price" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="selling_price" :value="$t('Selling Price')" class="theme-form-label ml-1" />
                                <TextInput id="selling_price" type="number" min="0" step="0.01" class="theme-form-input" v-model="form.selling_price" :placeholder="$t('Selling Price')" />
                                <InputError :message="form.errors.selling_price" class="mt-2 ml-1" />
                            </div>

                            <div class="flex flex-col">
                                <InputLabel for="product_category_id" :value="$t('Category')" class="theme-form-label ml-1" />
                                <select id="product_category_id" class="theme-form-input" v-model="form.product_category_id" required>
                                    <option value="" disabled>{{ $t('Select category') }}</option>
                                    <option v-for="category in props.categories" :key="category.id" :value="category.id">{{ category.name }}</option>
                                </select>
                                <InputError :message="form.errors.product_category_id" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="brand_id" :value="$t('Brand')" class="theme-form-label ml-1" />
                                <select id="brand_id" class="theme-form-input" v-model="form.brand_id" required>
                                    <option value="" disabled>{{ $t('Select brand') }}</option>
                                    <option v-for="brand in props.brands" :key="brand.id" :value="brand.id">{{ brand.name }}</option>
                                </select>
                                <InputError :message="form.errors.brand_id" class="mt-2 ml-1" />
                            </div>
                        </div>

                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-10 mb-6">{{ $t('Product Details') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                            <div class="flex flex-col">
                                <InputLabel for="carton_qty" :value="$t('Carton Qty')" class="theme-form-label ml-1" />
                                <TextInput id="carton_qty" type="number" min="0" step="1" class="theme-form-input" v-model="form.carton_qty" :placeholder="$t('Carton Qty')" />
                                <InputError :message="form.errors.carton_qty" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="price_per_carton" :value="$t('Price per Carton')" class="theme-form-label ml-1" />
                                <TextInput id="price_per_carton" type="number" min="0" step="0.01" class="theme-form-input" v-model="form.price_per_carton" :placeholder="$t('Price per Carton')" />
                                <InputError :message="form.errors.price_per_carton" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="pieces_per_carton" :value="$t('Pieces per Carton')" class="theme-form-label ml-1" />
                                <TextInput id="pieces_per_carton" type="number" min="0" step="1" class="theme-form-input" v-model="form.pieces_per_carton" :placeholder="$t('Pieces per Carton')" />
                                <InputError :message="form.errors.pieces_per_carton" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="price_per_piece" :value="$t('Price per Piece')" class="theme-form-label ml-1" />
                                <TextInput
                                    id="price_per_piece"
                                    type="text"
                                    class="theme-form-input bg-slate-50 dark:bg-slate-800/60 cursor-not-allowed"
                                    :model-value="formattedPricePerPiece"
                                    readonly
                                    tabindex="-1"
                                    :placeholder="$t('Price per Piece')"
                                />
                                <InputError :message="form.errors.price_per_piece" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="weight" :value="$t('Weight')" class="theme-form-label ml-1" />
                                <TextInput id="weight" type="number" step="0.001" min="0" class="theme-form-input" v-model="form.weight" :placeholder="$t('Weight')" />
                                <InputError :message="form.errors.weight" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="color" :value="$t('Color')" class="theme-form-label ml-1" />
                                <TextInput id="color" type="text" class="theme-form-input" v-model="form.color" placeholder="e.g. Black" />
                                <InputError :message="form.errors.color" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col md:col-span-2">
                                <InputLabel for="origin_country" :value="$t('Origin Country')" class="theme-form-label ml-1" />
                                <TextInput id="origin_country" type="text" class="theme-form-input" v-model="form.origin_country" placeholder="e.g. Pakistan" />
                                <InputError :message="form.errors.origin_country" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col md:col-span-2">
                                <InputLabel for="description" :value="$t('Description')" class="theme-form-label ml-1" />
                                <textarea id="description" class="theme-form-input min-h-[120px] resize-y" v-model="form.description" placeholder="Short description..."></textarea>
                                <InputError :message="form.errors.description" class="mt-2 ml-1" />
                            </div>
                        </div>

                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-10 mb-6">{{ $t('Stock Settings') }}</h3>
                        <div class="space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-1 gap-x-10 gap-y-8">
                                <div class="flex flex-col">
                                    <InputLabel for="minimum_stock" :value="$t('Minimum Stock')" class="theme-form-label ml-1" />
                                    <TextInput id="minimum_stock" type="number" min="0" class="theme-form-input" v-model="form.minimum_stock" :placeholder="$t('Minimum Stock')" />
                                    <InputError :message="form.errors.minimum_stock" class="mt-2 ml-1" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-x-10 gap-y-8">
                                <div class="flex flex-col">
                                    <InputLabel :value="$t('Status')" class="theme-form-label ml-1" />
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
                </div>
                <div class="flex items-center justify-center pt-4">
                    <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95" :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing">{{ $t('Create Product') }}</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    product: { type: Object, required: true },
    categories: { type: Array, default: () => [] },
    brands: { type: Array, default: () => [] },
    units: { type: Array, default: () => [] },
});

const slugTouched = ref(false);

const form = useForm({
    product_category_id: props.product.product_category_id ?? '',
    brand_id: props.product.brand_id ?? '',
    unit_id: props.product.unit_id ?? '',
    tax: props.product.tax ?? 0,
    name: props.product.name ?? '',
    slug: props.product.slug ?? '',
    sku: props.product.sku ?? '',
    barcode: props.product.barcode ?? '',
    model_number: props.product.model_number ?? '',
    manufacturer: props.product.manufacturer ?? '',
    color: props.product.color ?? '',
    size: props.product.size ?? '',
    weight: props.product.weight ?? '',
    hsn_code: props.product.hsn_code ?? '',
    origin_country: props.product.origin_country ?? '',
    description: props.product.description ?? '',
    minimum_stock: props.product.minimum_stock ?? 0,
    maximum_stock: props.product.maximum_stock ?? '',
    track_stock: !!props.product.track_stock,
    has_expiry: !!props.product.has_expiry,
    is_serialized: !!props.product.is_serialized,
    status: !!props.product.status,
});

const slugify = (value) =>
    value.toString().trim().toLowerCase().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');

watch(() => form.name, (newName) => {
    if (!slugTouched.value) form.slug = slugify(newName);
});

const onSlugInput = () => { slugTouched.value = true; };
const regenerateSlug = () => { form.slug = slugify(form.name); slugTouched.value = false; };

const submit = () => {
    form.put(route('products.update', props.product.id));
};
</script>

<template>
    <Head :title="$t('Edit Product')" />
    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">{{ $t('Edit Product') }}</h2>
            </div>
            <Link :href="route('products.index')" class="theme-form-back-link">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="text-slate-900">{{ $t('Back to Product List') }}</span>
            </Link>
        </div>

        <div class="max-w-8xl mx-auto pb-5">
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
                                <div class="flex items-center justify-between ml-1">
                                    <InputLabel for="slug" :value="$t('Slug')" class="theme-form-label" />
                                    <button type="button" @click="regenerateSlug" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">{{ $t('Regenerate') }}</button>
                                </div>
                                <TextInput id="slug" type="text" class="theme-form-input" v-model="form.slug" @input="onSlugInput" required placeholder="e.g. wireless-mouse" />
                                <InputError :message="form.errors.slug" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="sku" :value="$t('SKU')" class="theme-form-label ml-1" />
                                <TextInput id="sku" type="text" class="theme-form-input" v-model="form.sku" required placeholder="e.g. WM-1001" />
                                <InputError :message="form.errors.sku" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="barcode" :value="$t('Barcode')" class="theme-form-label ml-1" />
                                <TextInput id="barcode" type="text" class="theme-form-input" v-model="form.barcode" placeholder="Optional barcode" />
                                <InputError :message="form.errors.barcode" class="mt-2 ml-1" />
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
                            <div class="flex flex-col">
                                <InputLabel for="unit_id" :value="$t('Unit')" class="theme-form-label ml-1" />
                                <select id="unit_id" class="theme-form-input" v-model="form.unit_id" required>
                                    <option value="" disabled>{{ $t('Select unit') }}</option>
                                    <option v-for="unit in props.units" :key="unit.id" :value="unit.id">{{ unit.name }} ({{ unit.base_value }})</option>
                                </select>
                                <InputError :message="form.errors.unit_id" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="tax" value="Tax (%)" class="theme-form-label ml-1" />
                                <TextInput id="tax" type="number" step="0.01" min="0" class="theme-form-input" v-model="form.tax" placeholder="e.g. 5.00" />
                                <InputError :message="form.errors.tax" class="mt-2 ml-1" />
                            </div>
                        </div>

                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-10 mb-6">{{ $t('Product Details') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                            <div class="flex flex-col">
                                <InputLabel for="model_number" :value="$t('Model Number')" class="theme-form-label ml-1" />
                                <TextInput id="model_number" type="text" class="theme-form-input" v-model="form.model_number" placeholder="Optional model number" />
                                <InputError :message="form.errors.model_number" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="manufacturer" :value="$t('Manufacturer')" class="theme-form-label ml-1" />
                                <TextInput id="manufacturer" type="text" class="theme-form-input" v-model="form.manufacturer" placeholder="Optional manufacturer" />
                                <InputError :message="form.errors.manufacturer" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="color" :value="$t('Color')" class="theme-form-label ml-1" />
                                <TextInput id="color" type="text" class="theme-form-input" v-model="form.color" placeholder="e.g. Black" />
                                <InputError :message="form.errors.color" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="size" :value="$t('Size')" class="theme-form-label ml-1" />
                                <TextInput id="size" type="text" class="theme-form-input" v-model="form.size" placeholder="e.g. Medium" />
                                <InputError :message="form.errors.size" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="weight" value="Weight (kg)" class="theme-form-label ml-1" />
                                <TextInput id="weight" type="number" step="0.001" min="0" class="theme-form-input" v-model="form.weight" placeholder="e.g. 0.250" />
                                <InputError :message="form.errors.weight" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="hsn_code" :value="$t('HSN Code')" class="theme-form-label ml-1" />
                                <TextInput id="hsn_code" type="text" class="theme-form-input" v-model="form.hsn_code" placeholder="Optional HSN code" />
                                <InputError :message="form.errors.hsn_code" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col  md:col-span-2">
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
                            <!-- Stock Numbers -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                                <div class="flex flex-col">
                                    <InputLabel for="minimum_stock" :value="$t('Minimum Stock')" class="theme-form-label ml-1" />
                                    <TextInput id="minimum_stock" type="number" min="0" class="theme-form-input" v-model="form.minimum_stock" placeholder="e.g. 10" />
                                    <InputError :message="form.errors.minimum_stock" class="mt-2 ml-1" />
                                </div>
                                <div class="flex flex-col">
                                    <InputLabel for="maximum_stock" :value="$t('Maximum Stock')" class="theme-form-label ml-1" />
                                    <TextInput id="maximum_stock" type="number" min="0" class="theme-form-input" v-model="form.maximum_stock" placeholder="Optional maximum stock" />
                                    <InputError :message="form.errors.maximum_stock" class="mt-2 ml-1" />
                                </div>
                            </div>

                            <!-- Toggles -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-x-10 gap-y-8">
                                <div class="flex flex-col">
                                    <InputLabel :value="$t('Track Stock')" class="theme-form-label ml-1" />
                                    <label class="inline-flex items-center gap-3 mt-2 cursor-pointer select-none">
                                        <button type="button" role="switch" :aria-checked="form.track_stock" @click="form.track_stock = !form.track_stock" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none" :class="form.track_stock ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-600'">
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200" :class="form.track_stock ? 'translate-x-6' : 'translate-x-1'" />
                                        </button>
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ form.track_stock ? 'Enabled' : 'Disabled' }}</span>
                                    </label>
                                    <InputError :message="form.errors.track_stock" class="mt-2 ml-1" />
                                </div>
                                <div class="flex flex-col">
                                    <InputLabel :value="$t('Has Expiry')" class="theme-form-label ml-1" />
                                    <label class="inline-flex items-center gap-3 mt-2 cursor-pointer select-none">
                                        <button type="button" role="switch" :aria-checked="form.has_expiry" @click="form.has_expiry = !form.has_expiry" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none" :class="form.has_expiry ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-600'">
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200" :class="form.has_expiry ? 'translate-x-6' : 'translate-x-1'" />
                                        </button>
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ form.has_expiry ? 'Yes' : 'No' }}</span>
                                    </label>
                                    <InputError :message="form.errors.has_expiry" class="mt-2 ml-1" />
                                </div>
                                <div class="flex flex-col">
                                    <InputLabel :value="$t('Is Serialized')" class="theme-form-label ml-1" />
                                    <label class="inline-flex items-center gap-3 mt-2 cursor-pointer select-none">
                                        <button type="button" role="switch" :aria-checked="form.is_serialized" @click="form.is_serialized = !form.is_serialized" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none" :class="form.is_serialized ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-600'">
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200" :class="form.is_serialized ? 'translate-x-6' : 'translate-x-1'" />
                                        </button>
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ form.is_serialized ? 'Yes' : 'No' }}</span>
                                    </label>
                                    <InputError :message="form.errors.is_serialized" class="mt-2 ml-1" />
                                </div>
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
                    <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95" :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing">{{ $t('Update Product') }}</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ category: Object });

const slugTouched = ref(false);
const codeTouched = ref(false);

const form = useForm({
    code: props.category?.code ?? '',
    name: props.category?.name ?? '',
    slug: props.category?.slug ?? '',
    category_type: props.category?.category_type ?? '',
    image: props.category?.image ?? '',
    description: props.category?.description ?? '',
    status: props.category?.status ?? true,
});

const slugify = (value) =>
    value.toString().trim().toLowerCase().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');

watch(() => form.name, (newName) => {
    if (!slugTouched.value) form.slug = slugify(newName);
});

const onSlugInput = () => { slugTouched.value = true; };
const onCodeInput = () => { codeTouched.value = true; };

const submit = () => {
    form.put(route('product-categories.update', props.category.id));
};
</script>

<template>
    <Head :title="$t('Edit Product Category')" />
    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">{{ $t('Edit Product Category') }}</h2>
                <p class="text-sm text-slate-800 mt-1 font-medium dark:text-slate-400">{{ $t('Update existing category details.') }}</p>
            </div>
            <Link :href="route('product-categories.index')" class="theme-form-back-link">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="text-slate-900">{{ $t('Back to Category List') }}</span>
            </Link>
        </div>

        <div class="max-w-8xl mx-auto pb-4">
            <form @submit.prevent="submit" class="space-y-3">
                <div class="theme-form-card">
                    <div class="p-8 md:p-10">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-4">
                            <div class="flex flex-col">
                                <InputLabel for="code" :value="$t('Code')" class="theme-form-label ml-1" />
                                <TextInput id="code" type="text" class="theme-form-input" v-model="form.code" @input="onCodeInput" required />
                                <InputError :message="form.errors.code" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="name" :value="$t('Name')" class="theme-form-label ml-1" />
                                <TextInput id="name" type="text" class="theme-form-input" v-model="form.name" required autofocus />
                                <InputError :message="form.errors.name" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="slug" :value="$t('Slug')" class="theme-form-label ml-1" />
                                <TextInput id="slug" type="text" class="theme-form-input" v-model="form.slug" @input="onSlugInput" required />
                                <InputError :message="form.errors.slug" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="category_type" value="Category Type" class="theme-form-label ml-1" />
                                <TextInput id="category_type" type="text" class="theme-form-input" v-model="form.category_type" required />
                                <InputError :message="form.errors.category_type" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col md:col-span-2">
                                <InputLabel for="description" :value="$t('Description')" class="theme-form-label ml-1" />
                                <textarea id="description" class="theme-form-input min-h-[120px] resize-y" v-model="form.description"></textarea>
                                <InputError :message="form.errors.description" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="status" :value="$t('Status')" class="theme-form-label ml-1" />
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
                    <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95" :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing">{{ $t('Update Category') }}</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>

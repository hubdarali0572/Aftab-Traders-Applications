<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    code: '',
    name: '',

    contact_person: '',
    phone: '',
    email: '',

    city: '',
    address: '',

    is_default: false,
    status: true,
});

const submit = () => {
    form.post(route('warehouses.store'));
};
</script>

<template>
    <Head :title="$t('Create Warehouse')" />
    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">{{ $t('Create New Warehouse') }}</h2>
            </div>
            <Link :href="route('warehouses.index')" class="theme-form-back-link">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="text-slate-900">{{ $t('Back to Warehouse List') }}</span>
            </Link>
        </div>

        <div class="max-w-8xl mx-auto pb-5">
            <form @submit.prevent="submit" class="space-y-3">
                <div class="theme-form-card">
                    <div class="p-8 md:p-10">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Basic Information') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                            <div class="flex flex-col">
                                <InputLabel for="code" :value="$t('Code')" class="theme-form-label ml-1" />
                                <TextInput id="code" type="text" class="theme-form-input" v-model="form.code" required placeholder="e.g. WH-001" />
                                <InputError :message="form.errors.code" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="name" :value="$t('Name')" class="theme-form-label ml-1" />
                                <TextInput id="name" type="text" class="theme-form-input" v-model="form.name" required placeholder="e.g. Main Warehouse" />
                                <InputError :message="form.errors.name" class="mt-2 ml-1" />
                            </div>
                        </div>

                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-10 mb-6">{{ $t('Contact Information') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                            <div class="flex flex-col">
                                <InputLabel for="contact_person" :value="$t('Contact Person')" class="theme-form-label ml-1" />
                                <TextInput id="contact_person" type="text" class="theme-form-input" v-model="form.contact_person" placeholder="Optional contact person" />
                                <InputError :message="form.errors.contact_person" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="phone" :value="$t('Phone')" class="theme-form-label ml-1" />
                                <TextInput id="phone" type="text" class="theme-form-input" v-model="form.phone" placeholder="Optional phone number" />
                                <InputError :message="form.errors.phone" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col md:col-span-2">
                                <InputLabel for="email" :value="$t('Email')" class="theme-form-label ml-1" />
                                <TextInput id="email" type="email" class="theme-form-input" v-model="form.email" placeholder="Optional email address" />
                                <InputError :message="form.errors.email" class="mt-2 ml-1" />
                            </div>
                        </div>

                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-10 mb-6">{{ $t('Location') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                            <div class="flex flex-col">
                                <InputLabel for="city" :value="$t('City')" class="theme-form-label ml-1" />
                                <TextInput id="city" type="text" class="theme-form-input" v-model="form.city" placeholder="e.g. Lahore" />
                                <InputError :message="form.errors.city" class="mt-2 ml-1" />
                            </div>
                            <div class="flex flex-col">
                                <InputLabel for="address" :value="$t('Address')" class="theme-form-label ml-1" />
                                <TextInput id="address" type="text" class="theme-form-input" v-model="form.address" placeholder="Optional address" />
                                <InputError :message="form.errors.address" class="mt-2 ml-1" />
                            </div>
                        </div>

                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mt-10 mb-6">{{ $t('Settings') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-8">
                            <div class="flex flex-col">
                                <InputLabel :value="$t('Default Warehouse')" class="theme-form-label ml-1" />
                                <label class="inline-flex items-center gap-3 mt-2 cursor-pointer select-none">
                                    <button type="button" role="switch" :aria-checked="form.is_default" @click="form.is_default = !form.is_default" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none" :class="form.is_default ? 'bg-indigo-600' : 'bg-slate-300 dark:bg-slate-600'">
                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200" :class="form.is_default ? 'translate-x-6' : 'translate-x-1'" />
                                    </button>
                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ form.is_default ? 'Yes' : 'No' }}</span>
                                </label>
                                <InputError :message="form.errors.is_default" class="mt-2 ml-1" />
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
                <div class="flex items-center justify-center pt-4">
                    <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95" :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing">{{ $t('Create Warehouse') }}</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
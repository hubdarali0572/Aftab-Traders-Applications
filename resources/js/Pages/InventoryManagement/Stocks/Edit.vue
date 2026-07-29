<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    stock: Object,
});

const form = useForm({
    minimum_stock: props.stock.minimum_stock,
    reorder_level: props.stock.reorder_level,
});

const submit = () => {
    form.put(route('stocks.update', props.stock.id));
};
</script>

<template>
    <Head :title="$t('Edit Stock Thresholds')" />
    <AuthenticatedLayout>
        <div class="max-w-8xl mx-auto mb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight dark:text-slate-100">{{ $t('Edit Stock Thresholds') }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ stock.product?.name }} · {{ stock.warehouse?.name }}</p>
            </div>
            <Link :href="route('stocks.index')" class="theme-form-back-link">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="text-slate-900">{{ $t('Back to Stock List') }}</span>
            </Link>
        </div>

        <div class="max-w-8xl mx-auto pb-5 space-y-4">
            <div class="theme-form-card p-8 md:p-10">
                <h3 class="text-sm font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-6">{{ $t('Current Stock') }} <span class="text-xs font-normal normal-case">({{ $t('read-only') }})</span></h3>
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Quantity') }}</dt>
                        <dd class="mt-1 text-lg font-bold text-slate-800 dark:text-slate-100">{{ stock.quantity }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $t('Average Cost') }}</dt>
                        <dd class="mt-1 text-lg font-bold text-slate-800 dark:text-slate-100">{{ stock.average_cost }}</dd>
                    </div>
                </dl>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-10 gap-y-3">
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
                        <PrimaryButton class="theme-btn-primary px-12 py-4 rounded-full text-white font-black text-xs uppercase tracking-widest active:scale-95" :class="{ 'opacity-50 cursor-not-allowed': form.processing }" :disabled="form.processing">{{ $t('Update Thresholds') }}</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <div class="mb-6 text-center sm:mb-8">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-500/15">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-indigo-600 dark:text-indigo-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                </svg>
            </div>

            <h2 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl dark:text-white">
                Forgot password?
            </h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                No worries, we'll send you reset instructions.
            </p>
        </div>

        <div
            v-if="status"
            class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-xl text-sm font-medium text-indigo-700 flex items-start dark:bg-indigo-500/10 dark:border-indigo-500/30 dark:text-indigo-200"
        >
            <svg class="h-5 w-5 text-indigo-500 mr-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-6">
            <div>
                <InputLabel for="email" value="Email Address" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5"
                    v-model="form.email"
                    required
                    autofocus
                    placeholder="Enter your registered email"
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <PrimaryButton class="w-full py-3.5" :disabled="form.processing">
                <span v-if="form.processing" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Sending Link...
                </span>
                <span v-else>Email Password Reset Link</span>
            </PrimaryButton>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100 text-center dark:border-slate-800">
            <Link
                :href="route('login')"
                class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-500 transition-colors dark:text-indigo-400"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
                Back to log in
            </Link>
        </div>
    </GuestLayout>
</template>

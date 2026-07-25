<script setup>
import { computed } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    status: {
        type: String,
    },
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(
    () => props.status === 'verification-link-sent',
);
</script>

<template>
    <GuestLayout>
        <Head title="Email Verification" />

        <div class="mb-6 text-center sm:mb-8">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-50 mb-4 dark:bg-indigo-500/15">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-indigo-600 dark:text-indigo-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
            </div>

            <h2 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl dark:text-white">
                Verify your email
            </h2>
            <p class="mt-3 text-sm text-slate-500 leading-relaxed dark:text-slate-400">
                Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?
            </p>
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-xl text-sm font-medium text-indigo-700 flex items-start dark:bg-indigo-500/10 dark:border-indigo-500/30 dark:text-indigo-200"
        >
            <svg class="h-5 w-5 text-indigo-500 mr-3 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span>A new verification link has been sent to the email address you provided during registration.</span>
        </div>

        <form @submit.prevent="submit">
            <div class="flex flex-col space-y-4 items-center">
                <PrimaryButton class="w-full py-3.5" :disabled="form.processing">
                    <span v-if="form.processing" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Resending...
                    </span>
                    <span v-else>Resend Verification Email</span>
                </PrimaryButton>

                <div class="flex flex-wrap items-center justify-center gap-1 text-sm text-slate-500 dark:text-slate-400">
                    <span>Didn't receive it? Check your spam folder or</span>
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="font-semibold text-indigo-600 hover:text-indigo-500 underline underline-offset-4 dark:text-indigo-400"
                    >
                        Log Out
                    </Link>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>

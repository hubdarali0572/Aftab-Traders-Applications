<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DeleteUserForm from "./Partials/DeleteUserForm.vue";
import UpdatePasswordForm from "./Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "./Partials/UpdateProfileInformationForm.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head :title="$t('Profile')" />

    <AuthenticatedLayout>
        <div class="mx-auto w-full max-w-[1600px] space-y-6 pb-8">
            <div
                class="rounded-2xl border border-slate-200 bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-900 p-6 lg:p-8 text-white shadow-lg relative overflow-hidden dark:border-slate-700"
            >
                <div class="relative z-10">
                    <p
                        class="text-[10px] font-bold uppercase tracking-[0.2em] text-indigo-300"
                    >
                        {{ $t('Account Settings') }}
                    </p>
                    <h2 class="mt-2 text-2xl lg:text-3xl font-extrabold tracking-tight">
                        {{ $t('Profile') }}
                    </h2>
                    <p class="mt-2 text-sm text-white/75 max-w-2xl leading-relaxed">
                        {{ $t('Manage your personal information, password, and account security from one place.') }}
                    </p>
                </div>
                <div
                    class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"
                ></div>
                <div
                    class="absolute right-24 bottom-0 h-20 w-20 rounded-full bg-indigo-400/20 blur-xl"
                ></div>
            </div>

            <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
                <div class="xl:col-span-2">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="h-full"
                    />
                </div>

                <div class="flex flex-col gap-6">
                    <UpdatePasswordForm class="flex-1" />
                    <DeleteUserForm />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

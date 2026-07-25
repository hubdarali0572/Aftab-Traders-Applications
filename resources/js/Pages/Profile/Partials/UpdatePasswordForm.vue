<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";

const passwordInput = ref(null);
const currentPasswordInput = ref(null);

const form = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const updatePassword = () => {
    form.put(route("password.update"), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: () => {
            if (form.errors.password) {
                form.reset("password", "password_confirmation");
                passwordInput.value.focus();
            }
            if (form.errors.current_password) {
                form.reset("current_password");
                currentPasswordInput.value.focus();
            }
        },
    });
};
</script>

<template>
    <section class="theme-form-card flex h-full flex-col overflow-hidden">
        <div class="theme-form-section-header">
            <h2 class="theme-form-section-title">Update Password</h2>
            <p class="mt-1 text-xs text-slate-400">
                Use a strong, unique password to keep your account secure.
            </p>
        </div>

        <div class="flex flex-1 flex-col p-6 md:p-8">
            <form
                @submit.prevent="updatePassword"
                class="flex flex-1 flex-col space-y-5"
            >
                <div>
                    <InputLabel for="current_password" value="Current Password" />
                    <TextInput
                        id="current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        type="password"
                        class="mt-1 block w-full"
                        autocomplete="current-password"
                        placeholder="Enter current password"
                    />
                    <InputError
                        :message="form.errors.current_password"
                        class="mt-2"
                    />
                </div>

                <div>
                    <InputLabel for="password" value="New Password" />
                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        autocomplete="new-password"
                        placeholder="Enter new password"
                    />
                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div>
                    <InputLabel
                        for="password_confirmation"
                        value="Confirm Password"
                    />
                    <TextInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="mt-1 block w-full"
                        autocomplete="new-password"
                        placeholder="Confirm new password"
                    />
                    <InputError
                        :message="form.errors.password_confirmation"
                        class="mt-2"
                    />
                </div>

                <div
                    class="mt-auto flex flex-col gap-4 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700"
                >
                    <Transition
                        enter-active-class="transition ease-in-out duration-300"
                        enter-from-class="opacity-0"
                        leave-active-class="transition ease-in-out duration-300"
                        leave-to-class="opacity-0"
                    >
                        <p
                            v-if="form.recentlySuccessful"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-600 dark:text-indigo-300"
                        >
                            <svg
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                />
                            </svg>
                            Password updated
                        </p>
                    </Transition>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="theme-btn-primary w-full sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </section>
</template>

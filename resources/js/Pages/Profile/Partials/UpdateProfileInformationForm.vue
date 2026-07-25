<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { computed } from "vue";

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});

const initials = computed(() =>
    user.name
        .split(" ")
        .map((part) => part[0])
        .join("")
        .slice(0, 2)
        .toUpperCase(),
);

const isVerified = computed(() => !!user.email_verified_at);
</script>

<template>
    <section class="theme-form-card flex h-full flex-col overflow-hidden">
        <div
            class="theme-form-section-header flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="theme-form-section-title">Profile Information</h2>
                <p class="mt-1 text-xs text-slate-400">
                    Update your account details and email address.
                </p>
            </div>
        </div>

        <div class="p-6 md:p-8 lg:p-10">
            <div
                class="mb-8 flex flex-col gap-5 rounded-2xl border border-slate-200 bg-slate-50 p-5 sm:flex-row sm:items-center dark:border-slate-700 dark:bg-slate-900/60"
            >
                <div
                    class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-indigo-600 text-lg font-black text-white shadow-lg shadow-indigo-600/30"
                >
                    {{ initials }}
                </div>

                <div class="min-w-0 flex-1">
                    <p
                        class="truncate text-lg font-bold text-slate-900 dark:text-slate-100"
                    >
                        {{ user.name }}
                    </p>
                    <p
                        class="mt-0.5 truncate text-sm font-medium text-slate-500 dark:text-slate-400"
                    >
                        {{ user.email }}
                    </p>
                </div>

                <div
                    v-if="isVerified"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-3 py-2 text-xs font-bold uppercase tracking-wider text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200"
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
                    Email Verified
                </div>
            </div>

            <form
                @submit.prevent="form.patch(route('profile.update'))"
                class="space-y-8"
            >
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 md:gap-8">
                    <div>
                        <InputLabel for="name" value="Full Name" />
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Enter your full name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email Address" />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            placeholder="you@example.com"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>
                </div>

                <div
                    v-if="mustVerifyEmail && user.email_verified_at === null"
                    class="rounded-xl border border-indigo-200 bg-indigo-50 p-4 dark:border-indigo-500/30 dark:bg-indigo-500/10"
                >
                    <div class="flex items-start gap-3">
                        <span
                            class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300"
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
                                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                                />
                            </svg>
                        </span>

                        <div>
                            <p
                                class="text-sm font-semibold text-indigo-900 dark:text-indigo-200"
                            >
                                Your email address is not verified.
                            </p>
                            <p
                                class="mt-1 text-sm text-indigo-800/80 dark:text-indigo-200/80"
                            >
                                Click below to receive a new verification link.
                            </p>
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="mt-3 inline-flex items-center text-sm font-bold text-indigo-600 hover:text-indigo-500 transition-colors dark:text-indigo-400 dark:hover:text-indigo-300"
                            >
                                Resend verification email
                            </Link>
                        </div>
                    </div>

                    <div
                        v-show="status === 'verification-link-sent'"
                        class="mt-4 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 dark:border-indigo-500/30 dark:bg-indigo-500/10 dark:text-indigo-200"
                    >
                        A new verification link has been sent to your email
                        address.
                    </div>
                </div>

                <div
                    class="flex flex-col gap-4 border-t border-slate-200 pt-6 sm:flex-row sm:items-center sm:justify-between dark:border-slate-700"
                >
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Changes are saved to your account immediately.
                    </p>

                    <div class="flex items-center gap-4">
                        <Transition
                            enter-active-class="transition ease-in-out duration-300"
                            enter-from-class="opacity-0 translate-y-1"
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
                                Profile saved
                            </p>
                        </Transition>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="theme-btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</template>

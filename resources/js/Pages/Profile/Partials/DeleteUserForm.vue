<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import { nextTick, ref } from "vue";

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: "",
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value?.focus());
};

const deleteUser = () => {
    form.delete(route("profile.destroy"), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value?.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section
        class="theme-form-card overflow-hidden border-slate-200 dark:border-slate-700"
    >
        <div
            class="flex flex-col gap-2 border-b border-indigo-100 bg-gradient-to-r from-indigo-50 to-slate-50 px-6 py-4 dark:border-indigo-400/20 dark:from-indigo-500/10 dark:to-slate-700/40"
        >
            <h2
                class="text-xs font-semibold uppercase tracking-wider text-indigo-700 dark:text-indigo-200"
            >
                Danger Zone
            </h2>
            <p class="text-xs text-slate-600 dark:text-slate-300">
                Permanently remove your account and all associated data.
            </p>
        </div>

        <div class="p-6 md:p-8">
            <div
                class="flex flex-col gap-5 rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-600 dark:bg-slate-700/40 sm:flex-row sm:items-center"
            >
                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-300"
                >
                    <svg
                        class="h-6 w-6"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"
                        />
                    </svg>
                </div>

                <div class="min-w-0 flex-1">
                    <h3
                        class="text-sm font-bold text-slate-900 dark:text-slate-100"
                    >
                        Delete Account
                    </h3>
                    <p
                        class="mt-1 text-xs leading-relaxed text-slate-600 dark:text-slate-400"
                    >
                        Once deleted, all resources and data will be permanently
                        removed. This action cannot be undone.
                    </p>
                </div>

                <button
                    type="button"
                    @click="confirmUserDeletion"
                    class="inline-flex shrink-0 items-center justify-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-indigo-600/20 transition-colors hover:bg-indigo-700 active:scale-[0.98] dark:focus:ring-offset-slate-900"
                >
                    Delete Account
                </button>
            </div>
        </div>

        <Modal :show="confirmingUserDeletion" max-width="md" @close="closeModal">
            <div class="theme-modal-header">
                <h3 class="theme-modal-title">Delete Account</h3>
                <button
                    type="button"
                    class="theme-modal-close"
                    aria-label="Close"
                    @click="closeModal"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-6 py-6">
                <div class="theme-modal-icon theme-modal-icon-danger">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>

                <p class="text-center text-sm font-medium leading-relaxed text-slate-600 dark:text-slate-300">
                    Please enter your password to permanently delete your account and all associated data.
                </p>

                <p class="theme-modal-warning text-center">This action is irreversible</p>

                <div class="mt-6">
                    <InputLabel for="password" value="Password" class="theme-form-label" />

                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="theme-form-input mt-2 block w-full"
                        placeholder="Enter your password"
                        @keyup.enter="deleteUser"
                    />

                    <InputError :message="form.errors.password" class="mt-2" />
                </div>
            </div>

            <div class="theme-modal-footer">
                <button type="button" class="theme-modal-btn-cancel" @click="closeModal">
                    Cancel
                </button>

                <button
                    type="button"
                    :disabled="form.processing"
                    class="theme-modal-btn-danger disabled:cursor-not-allowed disabled:opacity-50"
                    @click="deleteUser"
                >
                    {{ form.processing ? 'Deleting...' : 'Delete Account' }}
                </button>
            </div>
        </Modal>
    </section>
</template>
